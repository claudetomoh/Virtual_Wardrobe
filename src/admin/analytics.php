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

// User growth over time (last 30 days)
$userGrowth = $pdo->query('
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM '. TBL_USERS .'
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
')->fetchAll();

// Content creation over time (last 30 days)
$contentGrowth = $pdo->query('
    SELECT DATE(created_at) as date, 
           SUM(CASE WHEN source_type = "clothes" THEN 1 ELSE 0 END) as clothes,
           SUM(CASE WHEN source_type = "outfit" THEN 1 ELSE 0 END) as outfits
    FROM (
        SELECT created_at, "clothes" as source_type FROM '. TBL_CLOTHES .' WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        UNION ALL
        SELECT created_at, "outfit" as source_type FROM '. TBL_OUTFITS .' WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ) combined
    GROUP BY DATE(created_at)
    ORDER BY date ASC
')->fetchAll();

// Top categories
$topCategories = $pdo->query('
    SELECT category, COUNT(*) as count
    FROM '. TBL_CLOTHES .'
    GROUP BY category
    ORDER BY count DESC
    LIMIT 10
')->fetchAll();

// User engagement stats
$engagementStats = $pdo->query('
    SELECT 
        u.id,
        u.name,
        u.email,
        COUNT(DISTINCT c.id) as clothes_count,
        COUNT(DISTINCT o.id) as outfits_count,
        COUNT(DISTINCT p.id) as planned_count,
        (COUNT(DISTINCT c.id) + COUNT(DISTINCT o.id) + COUNT(DISTINCT p.id)) as total_activity
    FROM '. TBL_USERS .' u
    LEFT JOIN '. TBL_CLOTHES .' c ON c.user_id = u.id
    LEFT JOIN '. TBL_OUTFITS .' o ON o.user_id = u.id
    LEFT JOIN '. TBL_OUTFITS .'_planned p ON p.user_id = u.id
    GROUP BY u.id, u.name, u.email
    ORDER BY total_activity DESC
    LIMIT 10
')->fetchAll();

// Most worn items
$mostWorn = $pdo->query('
    SELECT c.name, c.category, c.wear_count, u.name as user_name
    FROM '. TBL_CLOTHES .' c
    INNER JOIN '. TBL_USERS .' u ON c.user_id = u.id
    WHERE c.wear_count > 0
    ORDER BY c.wear_count DESC
    LIMIT 10
')->fetchAll();

include __DIR__ . '/../templates/header.php';
?>

<style>
.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.analytics-header {
    background: linear-gradient(135deg, rgba(91, 75, 255, 0.15), rgba(32, 216, 210, 0.15));
    border: 1px solid rgba(91, 75, 255, 0.3);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.analytics-header h1 {
    font-size: 2rem;
    font-weight: 800;
    background: linear-gradient(135deg, #5b4bff, #20d8d2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 0.5rem;
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
}

.admin-nav a.active {
    background: linear-gradient(135deg, #5b4bff, #3c88ff);
}

.chart-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.chart-card h3 {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.chart-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
}

.bar-chart {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.bar-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.bar-label {
    min-width: 120px;
    font-size: 0.9rem;
    color: rgba(226, 232, 240, 0.8);
}

.bar-container {
    flex: 1;
    height: 36px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    overflow: hidden;
    position: relative;
}

.bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #5b4bff, #3c88ff);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0 1rem;
    font-weight: 700;
    color: white;
    transition: width 0.5s ease;
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    text-align: left;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.05);
    border-bottom: 2px solid rgba(91, 75, 255, 0.3);
    font-weight: 700;
    color: rgba(226, 232, 240, 0.9);
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.data-table tr:hover {
    background: rgba(255, 255, 255, 0.03);
}

.timeline-chart {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
    height: 200px;
    padding: 1rem 0;
}

.timeline-bar {
    flex: 1;
    background: linear-gradient(180deg, #5b4bff, #3c88ff);
    border-radius: 4px 4px 0 0;
    position: relative;
    transition: all 0.3s;
    cursor: pointer;
    min-width: 10px;
}

.timeline-bar:hover {
    background: linear-gradient(180deg, #6b5bff, #4c98ff);
    transform: scaleY(1.05);
}

.timeline-tooltip {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(15, 23, 42, 0.95);
    border: 1px solid rgba(91, 75, 255, 0.4);
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
    z-index: 10;
}

.timeline-bar:hover .timeline-tooltip {
    opacity: 1;
}

.timeline-labels {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.timeline-label {
    flex: 1;
    text-align: center;
    font-size: 0.75rem;
    color: rgba(226, 232, 240, 0.6);
}
</style>

<div class="admin-container">
    <div class="analytics-header">
        <h1><i class="fas fa-chart-pie"></i> Analytics Dashboard</h1>
        <p>Detailed insights and trends across the platform</p>
    </div>

    <div class="admin-nav">
        <a href="<?= url_path('src/admin/dashboard.php') ?>">
            <i class="fas fa-chart-line"></i> Overview
        </a>
        <a href="<?= url_path('src/admin/users.php') ?>">
            <i class="fas fa-users"></i> User Management
        </a>
        <a href="<?= url_path('src/admin/content.php') ?>">
            <i class="fas fa-images"></i> Content Browser
        </a>
        <a href="<?= url_path('src/admin/analytics.php') ?>" class="active">
            <i class="fas fa-chart-pie"></i> Analytics
        </a>
        <a href="<?= url_path('src/dashboard.php') ?>">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- User Growth Chart -->
    <div class="chart-card">
        <h3><i class="fas fa-users"></i> User Growth (Last 30 Days)</h3>
        <div class="timeline-chart">
            <?php 
            $maxUsers = max(array_column($userGrowth, 'count'));
            foreach ($userGrowth as $day):
                $height = $maxUsers > 0 ? ($day['count'] / $maxUsers * 100) : 0;
            ?>
                <div class="timeline-bar" style="height: <?= $height ?>%">
                    <div class="timeline-tooltip">
                        <?= date('M j', strtotime($day['date'])) ?><br>
                        <?= $day['count'] ?> users
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="timeline-labels">
            <?php foreach (array_slice($userGrowth, 0, 7) as $day): ?>
                <div class="timeline-label"><?= date('M j', strtotime($day['date'])) ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Content Growth Chart -->
    <div class="chart-card">
        <h3><i class="fas fa-chart-line"></i> Content Creation (Last 30 Days)</h3>
        <div class="timeline-chart">
            <?php 
            $maxContent = 0;
            foreach ($contentGrowth as $day) {
                $total = $day['clothes'] + $day['outfits'];
                if ($total > $maxContent) $maxContent = $total;
            }
            foreach ($contentGrowth as $day):
                $total = $day['clothes'] + $day['outfits'];
                $height = $maxContent > 0 ? ($total / $maxContent * 100) : 0;
            ?>
                <div class="timeline-bar" style="height: <?= $height ?>%">
                    <div class="timeline-tooltip">
                        <?= date('M j', strtotime($day['date'])) ?><br>
                        <?= $day['clothes'] ?> clothes<br>
                        <?= $day['outfits'] ?> outfits
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Two-column layout for charts -->
    <div class="chart-grid">
        <!-- Top Categories -->
        <div class="chart-card">
            <h3><i class="fas fa-tags"></i> Top Categories</h3>
            <div class="bar-chart">
                <?php 
                $maxCat = max(array_column($topCategories, 'count'));
                foreach ($topCategories as $cat): 
                    $percentage = ($cat['count'] / $maxCat) * 100;
                ?>
                    <div class="bar-item">
                        <div class="bar-label"><?= ucfirst(h($cat['category'])) ?></div>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?= $percentage ?>%">
                                <?= $cat['count'] ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Most Worn Items -->
        <div class="chart-card">
            <h3><i class="fas fa-fire"></i> Most Worn Items</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Owner</th>
                            <th>Wears</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mostWorn as $item): ?>
                            <tr>
                                <td><strong><?= h($item['name']) ?></strong></td>
                                <td><?= ucfirst(h($item['category'])) ?></td>
                                <td><?= h($item['user_name']) ?></td>
                                <td><strong><?= $item['wear_count'] ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- User Engagement -->
    <div class="chart-card">
        <h3><i class="fas fa-chart-bar"></i> User Engagement Rankings</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Clothes</th>
                        <th>Outfits</th>
                        <th>Planned</th>
                        <th>Total Activity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($engagementStats as $index => $user): ?>
                        <tr>
                            <td><strong>#<?= $index + 1 ?></strong></td>
                            <td><strong><?= h($user['name']) ?></strong></td>
                            <td><?= h($user['email']) ?></td>
                            <td><?= $user['clothes_count'] ?></td>
                            <td><?= $user['outfits_count'] ?></td>
                            <td><?= $user['planned_count'] ?></td>
                            <td><strong><?= $user['total_activity'] ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
