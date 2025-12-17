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

// Get filter parameters
$searchUser = $_GET['user'] ?? '';
$searchCategory = $_GET['category'] ?? '';
$contentType = $_GET['type'] ?? 'all'; // all, clothes, outfits

// Build query for clothes
$clothesQuery = 'SELECT c.id, c.name, c.category, c.image_path, c.created_at, c.wear_count, 
                        u.name as user_name, u.email as user_email
                 FROM '. TBL_CLOTHES .' c
                 INNER JOIN '. TBL_USERS .' u ON c.user_id = u.id
                 WHERE 1=1';
$clothesParams = [];

if ($searchUser) {
    $clothesQuery .= ' AND (u.name LIKE ? OR u.email LIKE ?)';
    $clothesParams[] = "%{$searchUser}%";
    $clothesParams[] = "%{$searchUser}%";
}

if ($searchCategory) {
    $clothesQuery .= ' AND c.category = ?';
    $clothesParams[] = $searchCategory;
}

$clothesQuery .= ' ORDER BY c.created_at DESC LIMIT 50';

$clothes = [];
if ($contentType === 'all' || $contentType === 'clothes') {
    $stmt = $pdo->prepare($clothesQuery);
    $stmt->execute($clothesParams);
    $clothes = $stmt->fetchAll();
}

// Build query for outfits
$outfitsQuery = 'SELECT o.id, o.title, o.created_at,
                        u.name as user_name, u.email as user_email,
                        COUNT(DISTINCT p.id) as plan_count
                 FROM '. TBL_OUTFITS .' o
                 INNER JOIN '. TBL_USERS .' u ON o.user_id = u.id
                 LEFT JOIN '. TBL_OUTFITS .'_planned p ON p.outfit_id = o.id
                 WHERE 1=1';
$outfitsParams = [];

if ($searchUser) {
    $outfitsQuery .= ' AND (u.name LIKE ? OR u.email LIKE ?)';
    $outfitsParams[] = "%{$searchUser}%";
    $outfitsParams[] = "%{$searchUser}%";
}

$outfitsQuery .= ' GROUP BY o.id, o.title, o.created_at, u.name, u.email ORDER BY o.created_at DESC LIMIT 50';

$outfits = [];
if ($contentType === 'all' || $contentType === 'outfits') {
    $stmt = $pdo->prepare($outfitsQuery);
    $stmt->execute($outfitsParams);
    $outfits = $stmt->fetchAll();
}

// Get categories for filter
$categories = $pdo->query('SELECT DISTINCT category FROM '. TBL_CLOTHES .' ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../templates/header.php';
?>

<style>
.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.content-header {
    background: linear-gradient(135deg, rgba(91, 75, 255, 0.15), rgba(32, 216, 210, 0.15));
    border: 1px solid rgba(91, 75, 255, 0.3);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.content-header h1 {
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

.filters-bar {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 16px;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: rgba(226, 232, 240, 0.9);
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 0.65rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    color: white;
    font-size: 0.9rem;
}

.filter-group button {
    padding: 0.65rem 1.5rem;
    background: linear-gradient(135deg, #5b4bff, #3c88ff);
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-group button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(91, 75, 255, 0.3);
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.content-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    transition: all 0.2s;
}

.content-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
}

.content-image {
    width: 100%;
    height: 220px;
    object-fit: cover;
    background: rgba(0, 0, 0, 0.3);
}

.content-info {
    padding: 1.25rem;
}

.content-title {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.content-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: rgba(226, 232, 240, 0.7);
    margin-bottom: 0.75rem;
}

.content-user {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    font-size: 0.85rem;
}

.content-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.btn-view, .btn-delete {
    flex: 1;
    padding: 0.5rem;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.05);
    color: white;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    text-align: center;
}

.btn-view:hover {
    background: rgba(91, 75, 255, 0.2);
    border-color: rgba(91, 75, 255, 0.4);
}

.btn-delete:hover {
    background: rgba(239, 68, 68, 0.2);
    border-color: rgba(239, 68, 68, 0.5);
}

.category-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: linear-gradient(135deg, rgba(91, 75, 255, 0.2), rgba(60, 136, 255, 0.2));
    border: 1px solid rgba(91, 75, 255, 0.3);
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}
</style>

<div class="admin-container">
    <div class="content-header">
        <h1><i class="fas fa-images"></i> Content Browser</h1>
        <p>View and manage all user content across the platform</p>
    </div>

    <div class="admin-nav">
        <a href="<?= url_path('src/admin/dashboard.php') ?>">
            <i class="fas fa-chart-line"></i> Overview
        </a>
        <a href="<?= url_path('src/admin/users.php') ?>">
            <i class="fas fa-users"></i> User Management
        </a>
        <a href="<?= url_path('src/admin/content.php') ?>" class="active">
            <i class="fas fa-images"></i> Content Browser
        </a>
        <a href="<?= url_path('src/admin/analytics.php') ?>">
            <i class="fas fa-chart-pie"></i> Analytics
        </a>
        <a href="<?= url_path('src/dashboard.php') ?>">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <form method="GET" class="filters-bar">
        <div class="filter-group">
            <label>Content Type</label>
            <select name="type">
                <option value="all" <?= $contentType === 'all' ? 'selected' : '' ?>>All Content</option>
                <option value="clothes" <?= $contentType === 'clothes' ? 'selected' : '' ?>>Clothing Items</option>
                <option value="outfits" <?= $contentType === 'outfits' ? 'selected' : '' ?>>Outfits</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Search User</label>
            <input type="text" name="user" placeholder="Name or email..." value="<?= h($searchUser) ?>">
        </div>
        
        <?php if ($contentType !== 'outfits'): ?>
        <div class="filter-group">
            <label>Category</label>
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= h($cat) ?>" <?= $searchCategory === $cat ? 'selected' : '' ?>>
                        <?= ucfirst(h($cat)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        
        <div class="filter-group" style="display: flex; align-items: flex-end;">
            <button type="submit"><i class="fas fa-search"></i> Filter</button>
        </div>
    </form>

    <?php if ($contentType === 'all' || $contentType === 'clothes'): ?>
        <?php if (count($clothes) > 0): ?>
            <h2 style="margin: 2rem 0 1rem; font-size: 1.5rem; font-weight: 700;">
                <i class="fas fa-shirt"></i> Clothing Items (<?= count($clothes) ?>)
            </h2>
            <div class="content-grid">
                <?php foreach ($clothes as $item): ?>
                    <div class="content-card">
                        <img src="<?= h($item['image_path']) ?>" 
                             alt="<?= h($item['name']) ?>" 
                             class="content-image"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="content-image-placeholder" style="display:none; width:100%; height:220px; background:linear-gradient(135deg, rgba(91,75,255,0.2), rgba(60,136,255,0.2)); align-items:center; justify-content:center; font-size:3rem; color:rgba(255,255,255,0.3);">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="content-info">
                            <div class="content-title"><?= h($item['name']) ?></div>
                            <div class="content-meta">
                                <span class="category-badge"><?= ucfirst(h($item['category'])) ?></span>
                                <span>👀 <?= $item['wear_count'] ?> wears</span>
                            </div>
                            <div class="content-user">
                                <i class="fas fa-user"></i>
                                <strong><?= h($item['user_name']) ?></strong>
                            </div>
                            <div class="content-meta" style="margin-top: 0.5rem;">
                                <i class="fas fa-clock"></i>
                                <?= date('M j, Y', strtotime($item['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($contentType === 'all' || $contentType === 'outfits'): ?>
        <?php if (count($outfits) > 0): ?>
            <h2 style="margin: 2rem 0 1rem; font-size: 1.5rem; font-weight: 700;">
                <i class="fas fa-layer-group"></i> Outfits (<?= count($outfits) ?>)
            </h2>
            <div class="content-grid">
                <?php foreach ($outfits as $outfit): ?>
                    <div class="content-card">
                        <div class="content-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(91, 75, 255, 0.2), rgba(60, 136, 255, 0.2));">
                            <i class="fas fa-layer-group" style="font-size: 4rem; color: rgba(255, 255, 255, 0.3);"></i>
                        </div>
                        <div class="content-info">
                            <div class="content-title"><?= h($outfit['title']) ?></div>
                            <div class="content-meta">
                                <span>📅 Planned <?= $outfit['plan_count'] ?> time<?= $outfit['plan_count'] != 1 ? 's' : '' ?></span>
                            </div>
                            <div class="content-user">
                                <i class="fas fa-user"></i>
                                <strong><?= h($outfit['user_name']) ?></strong>
                            </div>
                            <div class="content-meta" style="margin-top: 0.5rem;">
                                <i class="fas fa-clock"></i>
                                <?= date('M j, Y', strtotime($outfit['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (count($clothes) === 0 && count($outfits) === 0): ?>
        <div style="text-align: center; padding: 4rem 2rem; color: rgba(226, 232, 240, 0.6);">
            <i class="fas fa-search" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
            <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">No Content Found</h3>
            <p>Try adjusting your filters or search criteria</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
