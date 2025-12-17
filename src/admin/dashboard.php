<?php
require_once __DIR__ . '/../config.php';
requireLogin();

// Check if user is admin
$stmt = $pdo->prepare('SELECT role FROM '. TBL_USERS .' WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$userRole = $stmt->fetchColumn();

if ($userRole !== 'admin') {
    header('Location: ' . url_path('src/dashboard.php'));
    exit;
}

// System Statistics
$totalUsers = $pdo->query('SELECT COUNT(*) FROM '. TBL_USERS .'')->fetchColumn();
$totalClothes = $pdo->query('SELECT COUNT(*) FROM '. TBL_CLOTHES .'')->fetchColumn();
$totalOutfits = $pdo->query('SELECT COUNT(*) FROM '. TBL_OUTFITS .'')->fetchColumn();
$totalPlanned = $pdo->query('SELECT COUNT(*) FROM '. TBL_OUTFITS .'_planned')->fetchColumn();

// Recent users
$recentUsers = $pdo->query('SELECT id, name, email, role, created_at FROM '. TBL_USERS .' ORDER BY created_at DESC LIMIT 5')->fetchAll();

// Most active users
$activeUsers = $pdo->query('
    SELECT u.id, u.name, u.email, 
           COUNT(DISTINCT c.id) as clothes_count,
           COUNT(DISTINCT o.id) as outfits_count,
           COUNT(DISTINCT p.id) as planned_count
    FROM '. TBL_USERS .' u
    LEFT JOIN '. TBL_CLOTHES .' c ON c.user_id = u.id
    LEFT JOIN '. TBL_OUTFITS .' o ON o.user_id = u.id
    LEFT JOIN '. TBL_OUTFITS .'_planned p ON p.user_id = u.id
    GROUP BY u.id, u.name, u.email
    ORDER BY (COUNT(DISTINCT c.id) + COUNT(DISTINCT o.id)) DESC
    LIMIT 5
')->fetchAll();

// Recent activity
$recentActivity = $pdo->query('
    SELECT "clothes" as type, user_id, created_at, name as title FROM '. TBL_CLOTHES .'
    UNION ALL
    SELECT "outfit" as type, user_id, created_at, title FROM '. TBL_OUTFITS .'
    ORDER BY created_at DESC
    LIMIT 10
')->fetchAll();

// Category distribution
$categoryStats = $pdo->query('SELECT category, COUNT(*) as count FROM '. TBL_CLOTHES .' GROUP BY category ORDER BY count DESC')->fetchAll();

// Popular vw_outfits (most planned)
$popularOutfits = $pdo->query('
    SELECT o.id, o.title, o.user_id, u.name as user_name, COUNT(p.id) as plan_count
    FROM '. TBL_OUTFITS .' o
    LEFT JOIN '. TBL_OUTFITS .'_planned p ON p.outfit_id = o.id
    INNER JOIN '. TBL_USERS .' u ON o.user_id = u.id
    GROUP BY o.id, o.title, o.user_id, u.name
    HAVING plan_count > 0
    ORDER BY plan_count DESC
    LIMIT 5
')->fetchAll();

include __DIR__ . '/../templates/header.php';
?>

<style>
.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.admin-header {
    background: linear-gradient(135deg, rgba(91, 75, 255, 0.15), rgba(32, 216, 210, 0.15));
    border: 1px solid rgba(91, 75, 255, 0.3);
    border-radius: 20px;
    padding: 2.5rem;
    margin-bottom: 2rem;
    text-align: center;
}

.admin-header h1 {
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #5b4bff, #20d8d2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 0.5rem;
}

.admin-header p {
    color: rgba(226, 232, 240, 0.8);
    font-size: 1.1rem;
}

.admin-nav {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.admin-nav a {
    padding: 0.75rem 1.5rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 12px;
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    font-weight: 600;
}

.admin-nav a:hover {
    background: rgba(91, 75, 255, 0.2);
    border-color: rgba(91, 75, 255, 0.4);
    transform: translateY(-2px);
}

.admin-nav a.active {
    background: linear-gradient(135deg, #5b4bff, #3c88ff);
    border-color: rgba(91, 75, 255, 0.6);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.04));
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.stat-card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-icon.blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.stat-icon.purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.stat-icon.green { background: linear-gradient(135deg, #10b981, #059669); }
.stat-icon.orange { background: linear-gradient(135deg, #f59e0b, #d97706); }

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #5b4bff, #20d8d2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-label {
    color: rgba(226, 232, 240, 0.7);
    font-size: 0.9rem;
    margin-top: 0.25rem;
}

.section-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.section-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.section-card h3 {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.user-list-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    margin-bottom: 0.75rem;
    transition: all 0.2s;
}

.user-list-item:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(91, 75, 255, 0.3);
}

.user-avatar-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
}

.user-details {
    flex: 1;
}

.user-details strong {
    display: block;
    font-size: 0.95rem;
}

.user-details small {
    color: rgba(226, 232, 240, 0.6);
    font-size: 0.8rem;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.03);
    border-left: 3px solid;
    border-radius: 8px;
    margin-bottom: 0.75rem;
}

.activity-item.clothes { border-color: #3b82f6; }
.activity-item.outfit { border-color: #8b5cf6; }

.activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.activity-icon.clothes { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
.activity-icon.outfit { background: rgba(139, 92, 246, 0.2); color: #a78bfa; }

.chart-container {
    margin-top: 1rem;
}

.chart-bar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.chart-label {
    min-width: 100px;
    font-size: 0.85rem;
    color: rgba(226, 232, 240, 0.8);
}

.chart-bar-bg {
    flex: 1;
    height: 32px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    overflow: hidden;
    position: relative;
}

.chart-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #5b4bff, #3c88ff);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0 0.75rem;
    font-size: 0.85rem;
    font-weight: 700;
    color: white;
    transition: width 0.5s ease;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-admin {
    background: linear-gradient(135deg, #5b4bff, #3c88ff);
    color: white;
}

.badge-user {
    background: rgba(255, 255, 255, 0.1);
    color: rgba(226, 232, 240, 0.9);
}
</style>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-shield-halved"></i> Admin Dashboard</h1>
        <p>Complete system overview and management controls</p>
    </div>

    <div class="admin-nav">
        <a href="<?= url_path('src/admin/dashboard.php') ?>" class="active">
            <i class="fas fa-chart-line"></i> Overview
        </a>
        <a href="<?= url_path('src/admin/users.php') ?>">
            <i class="fas fa-users"></i> User Management
        </a>
        <a href="<?= url_path('src/admin/content.php') ?>">
            <i class="fas fa-images"></i> Content Browser
        </a>
        <a href="<?= url_path('src/admin/analytics.php') ?>">
            <i class="fas fa-chart-pie"></i> Analytics
        </a>
        <a href="<?= url_path('src/dashboard.php') ?>">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="stat-number"><?= $totalUsers ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon purple">
                    <i class="fas fa-shirt"></i>
                </div>
                <div>
                    <div class="stat-number"><?= $totalClothes ?></div>
                    <div class="stat-label">Clothing Items</div>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon green">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <div class="stat-number"><?= $totalOutfits ?></div>
                    <div class="stat-label">Total Outfits</div>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon orange">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="stat-number"><?= $totalPlanned ?></div>
                    <div class="stat-label">Planned Outfits</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Grid -->
    <div class="section-grid">
        <!-- Recent Users -->
        <div class="section-card">
            <h3><i class="fas fa-user-plus"></i> Recent Users</h3>
            <?php foreach ($recentUsers as $user): ?>
                <div class="user-list-item">
                    <div class="user-avatar-small"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                    <div class="user-details">
                        <strong><?= h($user['name']) ?></strong>
                        <small><?= h($user['email']) ?> • <?= date('M j, Y', strtotime($user['created_at'])) ?></small>
                    </div>
                    <span class="badge badge-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Most Active Users -->
        <div class="section-card">
            <h3><i class="fas fa-fire"></i> Most Active Users</h3>
            <?php foreach ($activeUsers as $user): ?>
                <div class="user-list-item">
                    <div class="user-avatar-small"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                    <div class="user-details">
                        <strong><?= h($user['name']) ?></strong>
                        <small><?= $user['clothes_count'] ?> items • <?= $user['outfits_count'] ?> vw_outfits • <?= $user['planned_count'] ?> planned</small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- More sections -->
    <div class="section-grid">
        <!-- Category Distribution -->
        <div class="section-card">
            <h3><i class="fas fa-chart-bar"></i> Category Distribution</h3>
            <div class="chart-container">
                <?php 
                if (!empty($categoryStats)) {
                    $maxCount = max(array_column($categoryStats, 'count'));
                    foreach ($categoryStats as $cat): 
                        $percentage = ($cat['count'] / $maxCount) * 100;
                    ?>
                        <div class="chart-bar">
                            <div class="chart-label"><?= ucfirst(h($cat['category'])) ?></div>
                        <div class="chart-bar-bg">
                            <div class="chart-bar-fill" style="width: <?= $percentage ?>%">
                                <?= $cat['count'] ?>
                            </div>
                        </div>
                    </div>
                <?php 
                    endforeach;
                } else {
                    echo '<p style="color: rgba(226, 232, 240, 0.6); text-align: center; padding: 2rem;">No clothing items yet. Users need to upload items to see category distribution.</p>';
                }
                ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="section-card">
            <h3><i class="fas fa-clock-rotate-left"></i> Recent Activity</h3>
            <?php foreach ($recentActivity as $activity): ?>
                <div class="activity-item <?= $activity['type'] ?>">
                    <div class="activity-icon <?= $activity['type'] ?>">
                        <i class="fas fa-<?= $activity['type'] === 'clothes' ? 'shirt' : 'layer-group' ?>"></i>
                    </div>
                    <div class="user-details">
                        <strong><?= h($activity['title']) ?></strong>
                        <small><?= ucfirst($activity['type']) ?> • <?= date('M j, g:i A', strtotime($activity['created_at'])) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Popular Outfits -->
    <?php if (count($popularOutfits) > 0): ?>
    <div class="section-card" style="margin-bottom: 2rem;">
        <h3><i class="fas fa-star"></i> Popular Outfits</h3>
        <?php foreach ($popularOutfits as $outfit): ?>
            <div class="user-list-item">
                <div class="activity-icon outfit">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="user-details">
                    <strong><?= h($outfit['title']) ?></strong>
                    <small>by <?= h($outfit['user_name']) ?> • Planned <?= $outfit['plan_count'] ?> time<?= $outfit['plan_count'] > 1 ? 's' : '' ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
