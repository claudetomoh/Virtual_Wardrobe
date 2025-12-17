<?php
require_once __DIR__ . '/../config.php';
requireLogin();

$userId = $_SESSION['user_id'];

// Top worn vw_clothes with images
$topClothesStmt = $pdo->prepare('SELECT id, name, category, image_path, wear_count, last_worn_at FROM '. TBL_CLOTHES .' WHERE user_id = ? ORDER BY wear_count DESC, last_worn_at DESC LIMIT 5');
$topClothesStmt->execute([$userId]);
$topClothes = $topClothesStmt->fetchAll(PDO::FETCH_ASSOC);

// Top worn outfits
$topOutfitsStmt = $pdo->prepare('SELECT id, title, wear_count, last_worn_at FROM '. TBL_OUTFITS .' WHERE user_id = ? ORDER BY wear_count DESC, last_worn_at DESC LIMIT 5');
$topOutfitsStmt->execute([$userId]);
$topOutfits = $topOutfitsStmt->fetchAll(PDO::FETCH_ASSOC);

// Counts
$countStmt = $pdo->prepare('SELECT 
    SUM(favorite = 1) AS favorites,
    SUM(in_laundry = 1) AS in_laundry,
    COUNT(*) AS total
  FROM '. TBL_CLOTHES .' WHERE user_id = ?');
$countStmt->execute([$userId]);
$counts = $countStmt->fetch(PDO::FETCH_ASSOC) ?: ['favorites' => 0, 'in_laundry' => 0, 'total' => 0];

include __DIR__ . '/../templates/header.php';
?>
<style>
.stats-shell {
  max-width: 1200px;
  margin: 1.5rem auto 2rem;
  padding: 0 1rem;
}
.stats-header {
  margin-bottom: 1.5rem;
}
.stats-header h1 {
  margin: 0 0 0.3rem;
  font-size: 2rem;
  font-weight: 800;
  color: #f7f8ff;
}
.stats-header p {
  margin: 0;
  color: rgba(232, 237, 247, 0.7);
}
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.kpi-card {
  background: linear-gradient(145deg, rgba(91, 123, 255, 0.08), rgba(59, 130, 246, 0.06));
  border: 1px solid rgba(91, 123, 255, 0.2);
  border-radius: 16px;
  padding: 1.3rem 1.5rem;
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  transition: all 180ms ease;
}
.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.28);
  border-color: rgba(91, 123, 255, 0.35);
}
.kpi-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: rgba(232, 237, 247, 0.7);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}
.kpi-value {
  font-size: 2.5rem;
  font-weight: 800;
  color: #f7f8ff;
  line-height: 1;
  margin-bottom: 0.3rem;
}
.kpi-icon {
  font-size: 1.8rem;
  opacity: 0.6;
  margin-bottom: 0.5rem;
}
.stats-section {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.22);
}
.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.2rem;
  padding-bottom: 0.8rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.section-header h3 {
  margin: 0 0 0.2rem;
  font-size: 1.3rem;
  font-weight: 700;
  color: #f7f8ff;
}
.items-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1rem;
}
.stat-item-card {
  background: linear-gradient(145deg, rgba(17, 24, 39, 0.9), rgba(15, 23, 42, 0.95));
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px;
  padding: 0.8rem;
  transition: all 180ms ease;
  position: relative;
  overflow: hidden;
}
.stat-item-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, rgba(249, 115, 22, 0.5), rgba(251, 146, 60, 0.5));
  opacity: 0;
  transition: opacity 180ms ease;
}
.stat-item-card:hover {
  transform: translateY(-3px);
  border-color: rgba(91, 123, 255, 0.3);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
}
.stat-item-card:hover::before {
  opacity: 1;
}
.stat-item-image {
  width: 100%;
  height: 160px;
  object-fit: cover;
  border-radius: 10px;
  background: rgba(0, 0, 0, 0.3);
  margin-bottom: 0.6rem;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.stat-item-info {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}
.stat-item-name {
  font-weight: 700;
  color: #f7f8ff;
  font-size: 0.95rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.stat-item-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.85rem;
  color: rgba(232, 237, 247, 0.6);
}
.wear-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  background: rgba(249, 115, 22, 0.15);
  border: 1px solid rgba(249, 115, 22, 0.3);
  padding: 0.25rem 0.6rem;
  border-radius: 8px;
  font-weight: 700;
  color: #fbbf24;
  font-size: 0.8rem;
}
.outfit-list {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}
.outfit-stat-card {
  background: linear-gradient(145deg, rgba(17, 24, 39, 0.9), rgba(15, 23, 42, 0.95));
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1rem 1.2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  transition: all 180ms ease;
}
.outfit-stat-card:hover {
  transform: translateX(4px);
  border-color: rgba(91, 123, 255, 0.3);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.25);
}
.outfit-stat-info h4 {
  margin: 0 0 0.3rem;
  font-weight: 700;
  color: #f7f8ff;
  font-size: 1rem;
}
.outfit-stat-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.85rem;
  color: rgba(232, 237, 247, 0.6);
}
.empty-state {
  text-align: center;
  padding: 2rem;
  color: rgba(232, 237, 247, 0.5);
}
</style>
  <div class="stats-header">
    <h1>Wardrobe Statistics</h1>
    <p>Track your style habits and most-loved items</p>
  </div>

  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-icon"><i class="fa-solid fa-star" style="color: #fbbf24;"></i></div>
      <div class="kpi-value"><?php echo $counts['favorites']; ?></div>
      <div class="kpi-label">Favorites</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon"><i class="fa-solid fa-tshirt" style="color: #10b981;"></i></div>
      <div class="kpi-value"><?php echo $counts['in_laundry']; ?></div>
      <div class="kpi-label">In Laundry</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon"><i class="fa-solid fa-shirt" style="color: #3b82f6;"></i></div>
      <div class="kpi-value"><?php echo $counts['total']; ?></div>
      <div class="kpi-label">Total Items</div>
    </div>
  </div>

  <div class="stats-section">
    <div class="section-header">
      <div>
        <h3>Most Worn Items</h3>
      </div>
    </div>
    <?php if (count($topClothes) > 0): ?>
      <div class="items-grid">
        <?php foreach ($topClothes as $item): ?>
          <div class="stat-item-card">
            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="stat-item-image">
            <div class="stat-item-info">
              <div class="stat-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
              <div class="stat-item-meta">
                <span><?php echo htmlspecialchars($item['category']); ?></span>
              </div>
              <div style="margin-top: 0.3rem;">
                <span class="wear-badge">
                  <i class="fa-solid fa-fire"></i> Worn <?php echo $item['wear_count']; ?>x
                </span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="fa-solid fa-box-open" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
        <p>No clothing items worn yet</p>
      </div>
    <?php endif; ?>
  </div>

  <div class="stats-section">
    <div class="section-header">
      <div>
        <h3>Most Worn Outfits</h3>
      </div>
    </div>
    <?php if (count($topOutfits) > 0): ?>
      <div class="outfit-list">
        <?php foreach ($topOutfits as $outfit): ?>
          <div class="outfit-stat-card">
            <div class="outfit-stat-info">
              <h4><?php echo htmlspecialchars($outfit['title']); ?></h4>
              <div class="outfit-stat-meta">
                <span><i class="fa-solid fa-calendar-day"></i> <?php echo $outfit['last_worn_at'] ? date('M d, Y', strtotime($outfit['last_worn_at'])) : 'Never worn'; ?></span>
              </div>
            </div>
            <span class="wear-badge">
              <i class="fa-solid fa-fire"></i> <?php echo $outfit['wear_count']; ?>x
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="fa-solid fa-box-open" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
        <p>No vw_outfits worn yet</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
