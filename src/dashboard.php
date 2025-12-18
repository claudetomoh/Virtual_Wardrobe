<?php
require_once __DIR__ . '/config.php';
requireLogin();

// Check if user is admin
$roleStmt = $pdo->prepare('SELECT role FROM ' . TBL_USERS . ' WHERE id = ?');
$roleStmt->execute([$_SESSION['user_id']]);
$userRole = $roleStmt->fetchColumn();
$isAdmin = ($userRole === 'admin');

// Wardrobe summary
$itemStmt = $pdo->prepare('SELECT COUNT(*) FROM ' . TBL_CLOTHES . ' WHERE user_id = ?');
$itemStmt->execute([$_SESSION['user_id']]);
$clothesCount = (int) $itemStmt->fetchColumn();

$outfitStmt = $pdo->prepare('SELECT COUNT(*) FROM ' . TBL_OUTFITS . ' WHERE user_id = ?');
$outfitStmt->execute([$_SESSION['user_id']]);
$outfitCount = (int) $outfitStmt->fetchColumn();

// Category stats
$catStmt = $pdo->prepare('SELECT category, COUNT(*) AS cnt FROM ' . TBL_CLOTHES . ' WHERE user_id = ? GROUP BY category');
$catStmt->execute([$_SESSION['user_id']]);
$categoryStats = $catStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Suggested vw_outfits (limit 3) with item names and images
$suggestStmt = $pdo->prepare(
    'SELECT o.id, o.title, IFNULL(o.is_favorite, 0) AS is_favorite,
            t.name AS top_name, t.image_path AS top_image, t.category AS top_cat,
            b.name AS bottom_name, b.image_path AS bottom_image, b.category AS bottom_cat,
            s.name AS shoe_name, s.image_path AS shoe_image, s.category AS shoe_cat,
            a.name AS accessory_name, a.image_path AS accessory_image, a.category AS accessory_cat
     FROM ' . TBL_OUTFITS . ' o
     LEFT JOIN ' . TBL_CLOTHES . ' t ON t.id = o.top_id AND t.user_id = o.user_id
     LEFT JOIN ' . TBL_CLOTHES . ' b ON b.id = o.bottom_id AND b.user_id = o.user_id
     LEFT JOIN ' . TBL_CLOTHES . ' s ON s.id = o.shoe_id AND s.user_id = o.user_id
     LEFT JOIN ' . TBL_CLOTHES . ' a ON a.id = o.accessory_id AND a.user_id = o.user_id
     WHERE o.user_id = ?
     ORDER BY o.created_at DESC
     LIMIT 7'
);
$suggestStmt->execute([$_SESSION['user_id']]);
$suggestedOutfits = $suggestStmt->fetchAll(PDO::FETCH_ASSOC);

// Most worn clothes
$topClothesStmt = $pdo->prepare('SELECT id, name, image_path, wear_count FROM ' . TBL_CLOTHES . ' WHERE user_id = ? ORDER BY wear_count DESC, last_worn_at DESC LIMIT 5');
$topClothesStmt->execute([$_SESSION['user_id']]);
$topClothes = $topClothesStmt->fetchAll(PDO::FETCH_ASSOC);

// Favorite vw_outfits (server-side)
$favOutfitsStmt = $pdo->prepare('SELECT id, title FROM ' . TBL_OUTFITS . ' WHERE user_id = ? AND is_favorite = 1 ORDER BY created_at DESC LIMIT 5');
$favOutfitsStmt->execute([$_SESSION['user_id']]);
$favOutfits = $favOutfitsStmt->fetchAll(PDO::FETCH_ASSOC);

// Weekly planner: next 7 days starting Monday
$weekStart = new DateTimeImmutable('monday this week');
$today = new DateTimeImmutable('today');
// ensure week start is not in the future if today is earlier in week
if ($today < $weekStart) {
  $weekStart = $weekStart->modify('-7 days');
}
$dates = [];
for ($i = 0; $i < 7; $i++) {
  $d = $weekStart->modify("+{$i} days");
  $dates[$i] = $d->format('Y-m-d');
}

// Fetch planned vw_outfits for this week
$ph = implode(',', array_fill(0, count($dates), '?'));
$plannedSql = "SELECT p.id AS plan_id, p.planned_for, o.id AS outfit_id, o.title, o.top_id, o.bottom_id, o.shoe_id, o.accessory_id,
          t.name AS top_name, t.image_path AS top_image, t.category AS top_cat,
          b.name AS bottom_name, b.image_path AS bottom_image, b.category AS bottom_cat,
          s.name AS shoe_name, s.image_path AS shoe_image, s.category AS shoe_cat,
          a.name AS accessory_name, a.image_path AS accessory_image, a.category AS accessory_cat
        FROM " . TBL_OUTFITS_PLANNED . " p
        INNER JOIN " . TBL_OUTFITS . " o ON p.outfit_id = o.id
        LEFT JOIN " . TBL_CLOTHES . " t ON t.id = o.top_id AND t.user_id = o.user_id
        LEFT JOIN " . TBL_CLOTHES . " b ON b.id = o.bottom_id AND b.user_id = o.user_id
        LEFT JOIN " . TBL_CLOTHES . " s ON s.id = o.shoe_id AND s.user_id = o.user_id
        LEFT JOIN " . TBL_CLOTHES . " a ON a.id = o.accessory_id AND a.user_id = o.user_id
        WHERE p.user_id = ? AND p.planned_for IN ($ph)";
$planStmt = $pdo->prepare($plannedSql);
$planStmt->execute(array_merge([$_SESSION['user_id']], $dates));
$plannedRows = $planStmt->fetchAll(PDO::FETCH_ASSOC);
$plannedMap = [];
foreach ($plannedRows as $r) {
  $plannedMap[$r['planned_for']] = $r;
}

include __DIR__ . '/templates/header.php';
?>
<style>
.most-worn-grid {
  display: grid;
  gap: 0.55rem;
  grid-template-columns: repeat(auto-fit, minmax(160px, 190px));
  justify-content: start;
}
.most-worn-item {
  max-width: 190px;
  padding: 0.55rem;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.12);
  box-shadow: 0 8px 14px rgba(0, 0, 0, 0.18);
}
.most-worn-item img {
  max-height: 150px;
  width: 100%;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.18);
}

/* Recent vw_outfits cards - enhanced with layers and boxes */
.outfit-card {
  padding: 1.2rem;
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.03));
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 16px;
  box-shadow: 0 16px 32px rgba(0, 0, 0, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.08);
  transition: all 200ms cubic-bezier(0.4, 0, 0.2, 1);
}
.outfit-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  border-color: rgba(91, 123, 255, 0.3);
}
.outfit-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.9rem;
  padding-bottom: 0.7rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.outfit-card-header h4 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #f7f8ff;
  letter-spacing: 0.2px;
}
.outfit-card .outfit-items {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
  gap: 0.75rem;
}
.outfit-card .item {
  background: linear-gradient(145deg, rgba(17, 24, 39, 0.85), rgba(15, 23, 42, 0.9));
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 0.6rem;
  transition: all 180ms ease;
  position: relative;
  overflow: hidden;
}
.outfit-card .item::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, rgba(91, 123, 255, 0.4), rgba(59, 130, 246, 0.4));
  opacity: 0;
  transition: opacity 180ms ease;
}
.outfit-card .item:hover {
  transform: translateY(-2px);
  border-color: rgba(91, 123, 255, 0.35);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}
.outfit-card .item:hover::before {
  opacity: 1;
}
.outfit-card .item img {
  width: 100%;
  height: 140px;
  object-fit: cover;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.3);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
  margin-bottom: 0.5rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.outfit-card .item span {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: #e8edf7;
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.outfit-card .fav-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(232, 237, 247, 0.6);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 180ms ease;
}
.outfit-card .fav-btn:hover {
  background: rgba(249, 115, 22, 0.12);
  border-color: rgba(249, 115, 22, 0.3);
  color: #fbbf24;
}
.outfit-card .fav-btn.active {
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.18), rgba(251, 146, 60, 0.16));
  border-color: rgba(249, 115, 22, 0.4);
  color: #fbbf24;
  box-shadow: 0 4px 12px rgba(249, 115, 22, 0.25);
}
</style>
<div class="page-shell">
  <section class="hero-surface">
    <a id="hero"></a>
    <div class="hero-kicker">VIRTUAL WARDROBE · OUTFIT PLANNER</div>
    <h1 class="hero-title">Organize. Style. Shine.</h1>
    <p class="hero-sub">Upload your clothes, curate categories, and build vw_outfits with confidence.</p>
    <div class="hero-actions">
      <?php if ($isAdmin): ?>
      <a class="pill-btn pill-admin" href="<?= h(url_path('src/admin/dashboard.php')); ?>" style="background: linear-gradient(135deg, #5b4bff, #3c88ff); color: white; border: 1px solid rgba(91, 75, 255, 0.4);">
        <i class="fa-solid fa-shield-halved"></i>
        <span>Admin Panel</span>
      </a>
      <?php endif; ?>
      <a class="pill-btn pill-primary" href="<?= h(url_path('src/clothes/list.php')); ?>">
        <i class="fa-solid fa-cloud-arrow-up"></i>
        <span>Upload wardrobe</span>
      </a>
      <a class="pill-btn pill-ghost" href="<?= h(url_path('src/outfits/create.php')); ?>">
        <i class="fa-solid fa-wand-magic-sparkles"></i>
        <span>Create outfit</span>
      </a>
      <a class="pill-btn pill-accent" href="<?= h(url_path('src/planner/calendar.php')); ?>">
        <i class="fa-solid fa-calendar-days"></i>
        <span>Planner</span>
      </a>
    </div>
    <div class="hero-grid">
      <div class="feature-tile">
        <div class="feature-icon blue">
          <i class="fa-solid fa-cloud-arrow-up"></i>
        </div>
        <div class="feature-content">
          <h4>Upload Your Wardrobe</h4>
          <p>Snap, store, and manage every look with crisp previews.</p>
        </div>
      </div>
      <div class="feature-tile">
        <div class="feature-icon pink">
          <i class="fa-solid fa-layer-group"></i>
        </div>
        <div class="feature-content">
          <h4>Organize by Category</h4>
          <p>Smart filters for tops, bottoms, shoes, and accessories.</p>
        </div>
      </div>
      <div class="feature-tile">
        <div class="feature-icon orange">
          <i class="fa-solid fa-pencil"></i>
        </div>
        <div class="feature-content">
          <h4>Create Outfits</h4>
          <p>Mix & match, save favorites, and plan ahead.</p>
        </div>
      </div>
    </div>
  </section>

    <?php if ($isDashboard ?? true): ?>
    <nav class="section-nav" aria-label="Dashboard sections">
      <div class="nav-links-inline">
        <a href="#hero"><i class="fa-solid fa-compass"></i> Overview</a>
        <a href="#stats"><i class="fa-solid fa-chart-pie"></i> Stats</a>
        <a href="#quick"><i class="fa-solid fa-bolt"></i> Quick</a>
        <a href="#categories"><i class="fa-solid fa-grid-2"></i> Categories</a>
        <a href="#most-worn"><i class="fa-solid fa-fire"></i> Most worn</a>
        <a href="#favorites"><i class="fa-solid fa-star"></i> Favorites</a>
        <a href="#planner"><i class="fa-solid fa-calendar-days"></i> Planner</a>
        <a href="#recent"><i class="fa-solid fa-clock-rotate-left"></i> Recent</a>
      </div>
    </nav>
    <?php endif; ?>

  <a id="stats"></a>
  <div class="kpi-grid card-stack">
    <div class="kpi-card">
      <div class="kpi-label">Clothing items</div>
      <div class="kpi-value"><?= $clothesCount; ?></div>
      <div class="muted">Ready to tag, favorite, and plan.</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Outfits created</div>
      <div class="kpi-value"><?= $outfitCount; ?></div>
      <div class="muted">Rotate, track, and share looks.</div>
    </div>
  </div>

  <a id="categories"></a>
  <section class="card">
    <div class="section-header">
      <h3 class="card-title">Quick Stats by Category</h3>
      <a class="link" href="<?= h(url_path('src/clothes/list.php')); ?>">Go to wardrobe</a>
    </div>
    <?php if (!empty($categoryStats)): ?>
      <ul class="category-list">
        <?php foreach ($categoryStats as $category => $count): ?>
          <li>
            <span class="category-name"><?= h(ucfirst($category)); ?></span>
            <span class="category-count"><?= (int) $count; ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted">No items yet. Start by adding your first piece.</p>
    <?php endif; ?>
  </section>

  <!-- Quick actions -->
  <a id="quick"></a>
  <section class="card">
    <div class="section-header">
      <h3 class="card-title">Quick Actions</h3>
      </div>
      <div class="quick-actions">
        <a class="btn btn-secondary" href="<?= h(url_path('src/clothes/list.php')); ?>"><i class="fa-solid fa-shirt"></i> Manage Wardrobe</a>
        <a class="btn btn-primary" href="<?= h(url_path('src/outfits/create.php')); ?>"><i class="fa-solid fa-plus"></i> Create Outfit</a>
        <a class="btn btn-secondary" href="<?= h(url_path('src/outfits/list.php')); ?>"><i class="fa-solid fa-eye"></i> View Outfits</a>
        <a class="btn btn-secondary" href="<?=h(url_path('src/planner/calendar.php'))?>"><i class="fa-solid fa-calendar-days"></i> Calendar</a>
      <form id="clearLaundryForm" method="POST" action="<?=h(url_path('src/clothes/clear_laundry.php'))?>" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
        <button class="btn btn-danger" type="button" onclick="handleClearLaundry(event)"><i class="fa-solid fa-broom"></i> Clear Laundry</button>
      </form>
      <button id="autoPlanBtn" class="btn btn-secondary"><i class="fa-solid fa-bolt"></i> Auto Plan Week</button>
    </div>
  </section>

  <a id="most-worn"></a>
  <section class="card">
    <div class="section-header">
      <h3 class="card-title">Most Worn</h3>
      <a class="link" href="<?= h(url_path('src/stats/list.php')); ?>">View stats</a>
    </div>
    <?php if (!empty($topClothes)): ?>
      <div class="most-worn-grid">
        <?php foreach ($topClothes as $it): ?>
          <div class="most-worn-item">
            <img src="<?=h($it['image_path'])?>" alt="<?=h($it['name'])?>">
            <div>
              <p class="muted" style="font-weight:600"><?=h($it['name'])?></p>
              <p class="muted">Worn <?=h((int)$it['wear_count'])?>x</p>

            <!-- Dashboard Event Edit Modal -->
            <div id="dashEventModal" class="modal" aria-hidden="true" style="display:none;">
              <div class="modal-content card">
                <div class="modal-header">
                  <h3 id="dashModalTitle">Edit Plan</h3>
                  <button id="dashModalClose" class="btn">✕</button>
                </div>
                <form id="dashModalForm">
                  <input type="hidden" name="id" id="dashModalPlanId" value="">
                  <div style="margin:0.5rem 0;">
                    <label>Outfit</label>
                    <div id="dashModalOutfitTitle" style="font-weight:700; margin-bottom:0.4rem;"></div>
                  </div>
                  <div class="form-group">
                    <label>Note</label>
                    <input type="text" name="note" id="dashModalNote" maxlength="255">
                  </div>
                  <div class="form-group">
                    <label>Season</label>
                    <select name="season_hint" id="dashModalSeason">
                      <option value="all">All</option>
                      <option value="spring">Spring</option>
                      <option value="summer">Summer</option>
                      <option value="fall">Fall</option>
                      <option value="winter">Winter</option>
                    </select>
                  </div>
                  <div style="display:flex; gap:0.5rem; justify-content:flex-end; margin-top:0.5rem;">
                    <button type="button" id="dashModalDelete" class="btn btn-danger">Delete</button>
                    <button type="submit" id="dashModalSave" class="btn btn-primary">Save</button>
                    <button type="button" id="dashModalCancel" class="btn btn-secondary">Close</button>
                  </div>
                </form>
              </div>
            </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="muted">No wear data yet.</p>
    <?php endif; ?>
  </section>

  <a id="favorites"></a>
  <section class="card">
    <div class="section-header">
      <h3 class="card-title">Favorites (Outfits)</h3>
    </div>
    <?php if (!empty($favOutfits)): ?>
      <ul class="fav-list">
        <?php foreach ($favOutfits as $f): ?>
          <li><?=h($f['title'] ?: 'Untitled outfit')?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted">No favorite outfits. Mark some with the star!</p>
    <?php endif; ?>
  </section>

  <a id="planner"></a>
  <section class="card">
    <div class="section-header">
      <h3 class="card-title">Weekly Planner</h3>
      <p class="muted">Organize your outfits for the week ahead — drag, drop, or auto-plan with one click</p>
    </div>
    <?php
      $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
      $planner = $suggestedOutfits;
    ?>
    <div class="planner-grid">
      <?php foreach ($dates as $i => $date): $day = $days[$i] ?? date('D', strtotime($date)); $slot = $plannedMap[$date] ?? null; ?>
        <div class="planner-slot" data-slot-date="<?=h($date)?>">
          <p class="label"><?= $day; ?> <small style="font-weight:600; color: rgba(255,255,255,0.6); margin-left:0.5rem; font-size:0.78rem;"><?= h(date('M j', strtotime($date))) ?></small></p>
          <?php if ($slot): ?>
            <?php 
              // Assign a color class based on the outfit id for visual differentiation
              $colorIdx = 1 + ((int)$slot['outfit_id'] % 7); 
              $colorClass = "color-$colorIdx";
            ?>
            <div class="planner-card <?= $colorClass ?>" data-outfit-id="<?= (int)$slot['outfit_id']; ?>">
              <h4><?= h($slot['title'] ?: 'Outfit'); ?></h4>
              <div class="planner-items">
                <?php if ($slot['top_name']): ?><span><?= h($slot['top_name']); ?></span><?php endif; ?>
                <?php if ($slot['bottom_name']): ?><span><?= h($slot['bottom_name']); ?></span><?php endif; ?>
                <?php if ($slot['shoe_name']): ?><span><?= h($slot['shoe_name']); ?></span><?php endif; ?>
                <?php if ($slot['accessory_name']): ?><span><?= h($slot['accessory_name']); ?></span><?php endif; ?>
              </div>
              <div style="display:flex; gap:0.5rem; margin-top:0.6rem;">
                <form method="POST" action="<?=h(url_path('src/planner/delete.php'))?>">
                  <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
                  <input type="hidden" name="id" value="<?=h($slot['plan_id'] ?? '')?>">
                  <input type="hidden" name="redirect" value="<?=h(url_path('src/dashboard.php'))?>">
                  <button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i> Remove</button>
                </form>
              </div>
            </div>
          <?php else: ?>
            <div class="planner-empty muted">No outfit assigned</div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <a id="recent"></a>
  <section class="card">
    <div class="section-header">
      <h3 class="card-title">Recent Outfits</h3>
      <div class="filter-row">
        <div class="filter-chips">
          <button class="chip active" data-filter="all">All</button>
          <button class="chip" data-filter="top">Tops</button>
          <button class="chip" data-filter="bottom">Bottoms</button>
          <button class="chip" data-filter="shoe">Shoes</button>
          <button class="chip" data-filter="accessory">Accessories</button>
        </div>
        <label class="fav-toggle">
          <input type="checkbox" id="favOnly">
          <span>Favorites only</span>
        </label>
      </div>
    </div>
    <?php if (!empty($suggestedOutfits)): ?>
      <div class="outfit-grid" id="outfit-grid">
        <?php foreach ($suggestedOutfits as $outfit): 
          $tags = array_filter([$outfit['top_cat'] ?? '', $outfit['bottom_cat'] ?? '', $outfit['shoe_cat'] ?? '', $outfit['accessory_cat'] ?? '']);
          $tagAttr = implode(',', array_unique($tags));
        ?>
          <div class="outfit-card" draggable="true" data-outfit-id="<?= (int)$outfit['id']; ?>" data-tags="<?= h($tagAttr); ?>">
            <div class="outfit-card-header">
              <h4><?= h($outfit['title'] ?: 'Outfit'); ?></h4>
              <button class="fav-btn" type="button" data-id="<?= (int)$outfit['id'] ?>" data-is-favorite="<?= (int)$outfit['is_favorite'] ?>" aria-label="Favorite"><i class="fa-solid fa-star"></i></button>
            </div>
            <div class="outfit-items">
              <?php if (!empty($outfit['top_name'])): ?>
                <div class="item" data-tag="top">
                  <img src="<?= h($outfit['top_image']); ?>" alt="<?= h($outfit['top_name']); ?>">
                  <span><?= h($outfit['top_name']); ?></span>
                </div>
              <?php endif; ?>
              <?php if (!empty($outfit['bottom_name'])): ?>
                <div class="item" data-tag="bottom">
                  <img src="<?= h($outfit['bottom_image']); ?>" alt="<?= h($outfit['bottom_name']); ?>">
                  <span><?= h($outfit['bottom_name']); ?></span>
                </div>
              <?php endif; ?>
              <?php if (!empty($outfit['shoe_name'])): ?>
                <div class="item" data-tag="shoe">
                  <img src="<?= h($outfit['shoe_image']); ?>" alt="<?= h($outfit['shoe_name']); ?>">
                  <span><?= h($outfit['shoe_name']); ?></span>
                </div>
              <?php endif; ?>
              <?php if (!empty($outfit['accessory_name'])): ?>
                <div class="item" data-tag="accessory">
                  <img src="<?= h($outfit['accessory_image']); ?>" alt="<?= h($outfit['accessory_name']); ?>">
                  <span><?= h($outfit['accessory_name']); ?></span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="muted">No vw_outfits yet. Create one to see it here.</p>
    <?php endif; ?>
  </section>
  </div>

  <script>
document.addEventListener('DOMContentLoaded', () => {
  const chips = document.querySelectorAll('.chip');
  const cards = document.querySelectorAll('#outfit-grid .outfit-card');
  const favBtns = document.querySelectorAll('.fav-btn');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  // Determine initial favorite state from server-rendered attributes
  const favState = new Set();
  favBtns.forEach(btn => {
    const id = btn.closest('.outfit-card').dataset.outfitId;
    const isFav = btn.dataset.isFavorite === '1' || btn.dataset.isFavorite === 'true' || btn.classList.contains('active');
    if (isFav) { favState.add(id); btn.classList.add('active'); }
    btn.addEventListener('click', async () => {
      btn.disabled = true;
      try {
        const form = new FormData();
        form.append('id', id);
        form.append('action', 'toggle_favorite');
        form.append('csrf_token', csrf);
        const res = await fetch('<?=h(url_path('src/outfits/toggle.php'))?>', { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json();
        if (json.success) {
          if (json.favorite == 1) { favState.add(id); btn.classList.add('active'); }
          else { favState.delete(id); btn.classList.remove('active'); }
          applyFilters();
        }
      } catch (e) { console.error(e); }
      btn.disabled = false;
    });
  });

  const favOnly = document.getElementById('favOnly');
  const applyFilters = () => {
    const active = document.querySelector('.chip.active');
    const filter = active ? active.dataset.filter : 'all';
    cards.forEach(card => {
      const tags = (card.dataset.tags || '').toLowerCase();
      const isFav = favState.has(card.dataset.outfitId);
      const matchTag = filter === 'all' || tags.includes(filter);
      const matchFav = !favOnly.checked || isFav;
      card.style.display = (matchTag && matchFav) ? '' : 'none';
      card.querySelector('.fav-btn')?.classList.toggle('active', isFav);
    });
  };
  chips.forEach(chip => chip.addEventListener('click', () => { chips.forEach(c => c.classList.remove('active')); chip.classList.add('active'); applyFilters(); }));
  favOnly?.addEventListener('change', applyFilters);
  favBtns.forEach(btn => btn.addEventListener('click', () => applyFilters()));
});
</script>
<script>
// Drag & drop from vw_outfits to planner
document.addEventListener('DOMContentLoaded', ()=>{
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const outfitCards = document.querySelectorAll('.outfit-card[draggable="true"]');
  const slots = document.querySelectorAll('.planner-slot');
  outfitCards.forEach(card=>{
    card.addEventListener('dragstart', e=>{
      e.dataTransfer.setData('text/plain', card.dataset.outfitId);
      card.classList.add('dragging');
    });
    card.addEventListener('dragend', e=> card.classList.remove('dragging'));
  });
  slots.forEach(slot=>{
    slot.addEventListener('dragover', e=>e.preventDefault());
    slot.addEventListener('drop', async e=>{
      e.preventDefault();
      const outfitId = e.dataTransfer.getData('text/plain');
      const date = slot.dataset.slotDate;
      if (!outfitId || !date) return;
      try {
        const form = new FormData();
        form.append('outfit_id', outfitId);
        form.append('planned_for', date);
        form.append('csrf_token', csrf);
        form.append('redirect', '<?=h(url_path("src/dashboard.php"))?>');
        const resp = await fetch('<?=h(url_path('src/planner/plan.php'))?>', { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (resp.ok) {
          // refresh planner area without full reload
          await refreshDashboardPlanner();
        }
      } catch (err) {
        console.error(err);
      }
    });
  });
  // Auto-plan quick action: assign vw_outfits to empty slots
  const autoPlanBtn = document.getElementById('autoPlanBtn');
  autoPlanBtn?.addEventListener('click', async () => {
    // Get all visible outfit cards (respecting favorites filter)
    const outfitCards = Array.from(document.querySelectorAll('#outfit-grid .outfit-card'))
      .filter(card => card.style.display !== 'none');
    
    const vw_outfits = outfitCards.map(c => c.dataset.outfitId).filter(Boolean);
    
    // Get all empty planner slots
    const allSlots = Array.from(document.querySelectorAll('.planner-slot'));
    const emptySlots = allSlots.filter(s => {
      const existingCard = s.querySelector('.planner-card');
      const emptyIndicator = s.querySelector('.planner-empty');
      return !existingCard || emptyIndicator;
    });
    
    console.log('Available outfits:', vw_outfits.length);
    console.log('Empty slots:', emptySlots.length);
    
    if (vw_outfits.length === 0) { 
      window.showToast('No outfits available to plan. Create some outfits first!', 'info'); 
      return; 
    }
    
    if (emptySlots.length === 0) { 
      window.showToast('No empty days to schedule. All days already have outfits!', 'info'); 
      return; 
    }
    
    if (!await Modal.confirm(`Auto-plan ${Math.min(vw_outfits.length, emptySlots.length)} outfits into empty days?`, 'success')) return;
    
    let planned = 0;
    for (let i = 0; i < Math.min(vw_outfits.length, emptySlots.length); i++) {
      const outfitId = vw_outfits[i];
      const date = emptySlots[i].dataset.slotDate;
      
      if (!date) {
        console.warn('No date found for slot', emptySlots[i]);
        continue;
      }
      
      const form = new FormData();
      form.append('outfit_id', outfitId);
      form.append('planned_for', date);
      form.append('csrf_token', csrf);
      
      try {
        const response = await fetch('<?=h(url_path('src/planner/plan.php'))?>', { 
          method: 'POST', 
          body: form 
        });
        
        if (response.ok) {
          planned++;
          console.log(`Planned outfit ${outfitId} for ${date}`);
        }
      } catch (err) {
        console.error('Error planning outfit:', err);
      }
    }
    
    if (planned > 0) {
      window.showToast(`Auto-planned ${planned} outfit${planned > 1 ? 's' : ''} successfully!`, 'success');
      // Refresh the dashboard planner slots
      await refreshDashboardPlanner();
    } else {
      window.showToast('Failed to auto-plan outfits. Please try again.', 'error');
    }
  });
  // clear laundry via AJAX  
  window.handleClearLaundry = async function(e) {
    e.preventDefault();
    if (!await Modal.confirm('Clear laundry for all items? This will mark all items as not in laundry.', 'warning')) return;
    const clearLaundryForm = document.getElementById('clearLaundryForm');
    const fd = new FormData(clearLaundryForm);
    try {
      const res = await fetch(clearLaundryForm.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (res.ok) {
        window.showToast('Laundry cleared', 'success');
        await refreshDashboardPlanner();
      } else {
        window.showToast('Failed to clear laundry', 'error');
      }
    } catch (err) {
      console.error(err);
      window.showToast('Error clearing laundry', 'error');
    }
  };

  const clearLaundryForm = document.querySelector('form[action$="clothes/clear_laundry.php"]');
  if (clearLaundryForm) {
    clearLaundryForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(clearLaundryForm);
      try {
        const res = await fetch(clearLaundryForm.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (res.ok) {
          window.showToast('Laundry cleared', 'success');
          // optionally refresh planner or counts
          await refreshDashboardPlanner();
        } else {
          window.showToast('Failed to clear laundry', 'error');
        }
      } catch (err) { console.error(err); window.showToast('Error clearing laundry', 'error'); }
    });
  }

  async function refreshDashboardPlanner() {
    try {
      const slots = Array.from(document.querySelectorAll('.planner-slot'));
      if (slots.length === 0) return;
      const dates = slots.map(s => s.dataset.slotDate).filter(Boolean);
      if (dates.length === 0) return;
      const start = dates[0];
      const end = dates[dates.length - 1];
      const url = '<?=h(url_path('src/planner/events.php'))?>?start=' + encodeURIComponent(start) + '&end=' + encodeURIComponent(end);
      const res = await fetch(url);
      if (!res.ok) return;
      const events = await res.json();
      // Clear all slots
      slots.forEach(s => { s.querySelectorAll('.planner-card, .planner-empty, .planner-more, .planner-popover').forEach(el => el.remove()); s.insertAdjacentHTML('beforeend', '<div class="planner-empty muted">No outfit assigned</div>'); });
      // Group events by date
      const eventsByDate = {};
      events.forEach(ev => { eventsByDate[ev.start] = eventsByDate[ev.start] || []; eventsByDate[ev.start].push(ev); });
      // Place events by date with stacking and 'more' indicator
      Object.keys(eventsByDate).forEach(date => {
        const slot = document.querySelector('.planner-slot[data-slot-date="' + date + '"]');
        if (!slot) return;
        const empty = slot.querySelector('.planner-empty'); if (empty) empty.remove();
        const arr = eventsByDate[date] || [];
        // show up to 3 cards
        const maxShow = 3;
        arr.slice(0, maxShow).forEach(ev => {
          const div = document.createElement('div');
          // Apply color class based on outfit ID
          const outfitId = ev.extendedProps.outfit_id;
          const colorIdx = 1 + (parseInt(outfitId) % 7);
          div.className = 'planner-card color-' + colorIdx;
          div.setAttribute('data-outfit-id', outfitId);
          div.setAttribute('data-plan-id', ev.id);
          div.setAttribute('data-note', ev.extendedProps?.note || '');
          const h = document.createElement('h4'); h.innerText = ev.title;
          h.title = (ev.extendedProps && ev.extendedProps.note) ? ev.extendedProps.note : '';
          div.appendChild(h);
          slot.appendChild(div);
          // click to open modal edit
          div.addEventListener('click', () => openDashboardModalWithPlan(ev.id, ev));
        });
        if (arr.length > maxShow) {
          const more = document.createElement('div');
          more.className = 'planner-more';
          more.innerText = `+${arr.length - maxShow} more`;
          more.addEventListener('click', (e) => {
            // toggle popover
            let pop = slot.querySelector('.planner-popover');
            if (pop) { pop.remove(); return; }
            pop = document.createElement('div');
            pop.className = 'planner-popover';
            const list = document.createElement('ul');
            arr.forEach(it => {
              const li = document.createElement('li');
              li.dataset.planId = it.id;
              li.dataset.note = it.extendedProps && it.extendedProps.note ? it.extendedProps.note : '';
              li.dataset.season = it.extendedProps && it.extendedProps.season_hint ? it.extendedProps.season_hint : '';
              const title = document.createElement('span');
              title.className = 'planner-popover-title';
              title.innerText = it.title + (it.extendedProps && it.extendedProps.note ? ' — ' + it.extendedProps.note : '');
              const actions = document.createElement('div');
              actions.className = 'planner-popover-actions';
              const editBtn = document.createElement('button');
              editBtn.className = 'btn btn-secondary pop-edit';
              editBtn.type = 'button';
              editBtn.innerText = 'Edit';
              editBtn.dataset.planId = it.id;
              const delBtn = document.createElement('button');
              delBtn.className = 'btn btn-danger pop-delete';
              delBtn.type = 'button';
              delBtn.innerText = 'Delete';
              delBtn.dataset.planId = it.id;
              actions.appendChild(editBtn);
              actions.appendChild(delBtn);
              li.appendChild(title);
              li.appendChild(actions);
              list.appendChild(li);
            });
            pop.appendChild(list);
            pop.addEventListener('click', ev => { ev.stopPropagation(); });
            slot.appendChild(pop);
            // wire actions inside popover
            pop.querySelectorAll('.pop-edit').forEach(b => b.addEventListener('click', (e) => {
              const planId = b.dataset.planId;
              // open dashboard modal with plan data
              openDashboardModalWithPlan(planId, arr.find(a => a.id == planId));
            }));
            pop.querySelectorAll('.pop-delete').forEach(b => b.addEventListener('click', async (e) => {
              const planId = b.dataset.planId;
              if (!await Modal.confirm('Are you sure you want to delete this plan?', 'danger')) return;
              try {
                const form = new FormData();
                form.append('id', planId);
                form.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                const res = await fetch('<?=h(url_path('src/planner/delete.php'))?>', { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json();
                if (json && json.success) {
                  window.showToast('Plan deleted', 'success');
                  refreshDashboardPlanner();
                } else { window.showToast('Failed to delete plan', 'error'); }
              } catch (err) { console.error(err); window.showToast('Error deleting plan', 'error'); }
            }));
            // clicking outside closes
            setTimeout(() => {
              const close = (ev) => { if (!slot.contains(ev.target)) { pop.remove(); document.removeEventListener('click', close); } };
              document.addEventListener('click', close);
            }, 0);
          });
          slot.appendChild(more);
        }
      });
    } catch (e) { console.error('refreshPlanner error', e); }
  }
  // real-time subscription for planner updates (prefer socket.io)
  (function() {
    const socketServerUrl = document.querySelector('meta[name="socket-server-url"]')?.getAttribute('content') || '';
    const socketToken = document.querySelector('meta[name="socket-token"]')?.getAttribute('content') || '';
    const liveBadge = document.getElementById('liveBadge');
    const liveStatus = document.getElementById('liveStatusDot');
    const livePanel = document.getElementById('livePanel');
    const livePanelList = document.getElementById('livePanelList');
    const livePanelClose = document.getElementById('livePanelClose');
    const recentLiveItems = [];
    const showLiveNotification = () => {
      try {
        if (liveBadge) {
          liveBadge.innerText = (parseInt(liveBadge.innerText || '0') + 1) + '';
          liveBadge.style.transform = 'scale(1.05)';
          setTimeout(()=>liveBadge.style.transform = '', 400);
        }
      } catch (e) {}
    };
    const setLiveStatus = (state) => {
      if (!liveStatus) return;
      liveStatus.classList.remove('is-connected', 'is-reconnecting');
      if (state === 'connected') liveStatus.classList.add('is-connected');
      else if (state === 'reconnecting') liveStatus.classList.add('is-reconnecting');
    };
    const renderLivePanel = () => {
      if (!livePanelList) return;
      if (!recentLiveItems.length) {
        livePanelList.innerHTML = '<div class="live-panel-empty">No recent updates yet.</div>';
        return;
      }
      livePanelList.innerHTML = recentLiveItems
        .map(item => `<div class="live-panel-item"><strong>${item.title}</strong><br/><span>${item.time}</span></div>`)
        .join('');
    };
    const addLiveItem = (payload = {}) => {
      const action = payload.action || 'update';
      const title = `Planner ${action}`;
      const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      recentLiveItems.unshift({ title, time });
      if (recentLiveItems.length > 6) recentLiveItems.pop();
      renderLivePanel();
    };
    const resetLiveBadge = () => { try { if (liveBadge) { liveBadge.innerText = '0'; liveBadge.style.transform = ''; } } catch (e) {} };
    const openLivePanel = () => {
      if (!livePanel || !liveBadge) return;
      livePanel.setAttribute('aria-hidden', 'false');
      liveBadge.setAttribute('aria-expanded', 'true');
      resetLiveBadge();
      renderLivePanel();
    };
    const closeLivePanel = () => {
      if (!livePanel || !liveBadge) return;
      livePanel.setAttribute('aria-hidden', 'true');
      liveBadge.setAttribute('aria-expanded', 'false');
    };
    if (liveBadge) {
      liveBadge.addEventListener('click', () => {
        const isOpen = livePanel && livePanel.getAttribute('aria-hidden') === 'false';
        if (isOpen) closeLivePanel(); else openLivePanel();
      });
    }
    if (livePanelClose) livePanelClose.addEventListener('click', closeLivePanel);
    document.addEventListener('click', (ev) => {
      if (!livePanel || livePanel.getAttribute('aria-hidden') === 'true') return;
      if (livePanel.contains(ev.target) || (liveBadge && liveBadge.contains(ev.target))) return;
      closeLivePanel();
    });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) { resetLiveBadge(); closeLivePanel(); } });
    if (typeof io !== 'undefined' && socketServerUrl) {
      const sock = io(socketServerUrl, { 
        auth: { token: socketToken },
        timeout: 5000,
        reconnectionDelay: 2000,
        reconnectionDelayMax: 10000,
        reconnectionAttempts: 3
      });
      window.socket = sock; // expose for Playwright tests and debugging
      console.log('Socket client init', { serverUrl: socketServerUrl, tokenPresent: !!socketToken });
      sock.on('planner_update', payload => {
        refreshDashboardPlanner();
        window.showToast('Planner updated', 'info');
        addLiveItem(payload || {});
        if (typeof window.incrementLiveBadge === 'function') {
          window.incrementLiveBadge();
        } else {
          showLiveNotification();
        }
        window.__lastPlannerUpdate = Date.now();
        console.log('Received planner_update', payload);
      });
      sock.on('connect', () => {
        window.__socket_connected = true;
        setLiveStatus('connected');
        console.log('Socket connected to ' + socketServerUrl + ' socketId=' + (sock && sock.id));
        window.showToast('Live sync connected', 'success');
      });
      sock.on('disconnect', () => {
        window.__socket_connected = false;
        setLiveStatus('');
        window.showToast('Live sync disconnected', 'info');
      });
      sock.io.on('reconnect_attempt', () => setLiveStatus('reconnecting'));
      sock.on('connect_error', (err) => {
        console.warn('Socket connect error', err);
        window.__socket_connected = false;
        setLiveStatus('');
        window.showToast('Real-time updates unavailable', 'error');
      });
    } else if (!!window.EventSource) {
      const es = new EventSource('<?=h(url_path('src/planner/stream.php'))?>');
      es.onmessage = e => {
        try {
          const d = JSON.parse(e.data);
          if (d.type === 'planner_update') {
            refreshDashboardPlanner();
            addLiveItem({ action: 'update' });
            showLiveNotification();
          }
        } catch (err) {}
      };
      es.onerror = (err) => {
        console.warn('SSE connection error', err);
        setLiveStatus('');
        es.close();
      };
    }
  })();

  // Dashboard modal handlers for edit/delete
  const dashModal = document.getElementById('dashEventModal');
  const dashModalClose = document.getElementById('dashModalClose');
  const dashModalCancel = document.getElementById('dashModalCancel');
  const dashModalForm = document.getElementById('dashModalForm');
  const dashModalDelete = document.getElementById('dashModalDelete');
  const dashModalSave = document.getElementById('dashModalSave');

  function openDashboardModalWithPlan(planId, planObj) {
    if (!dashModal) return;
    document.getElementById('dashModalPlanId').value = planId;
    document.getElementById('dashModalOutfitTitle').innerText = planObj.title || 'Outfit';
    document.getElementById('dashModalNote').value = planObj.extendedProps?.note || '';
    document.getElementById('dashModalSeason').value = planObj.extendedProps?.season_hint || 'all';
    dashModal.setAttribute('aria-hidden', 'false'); dashModal.style.display = 'grid';
    dashModal._planObj = planObj;
  }
  function closeDashboardModal() { dashModal.setAttribute('aria-hidden', 'true'); dashModal.style.display = 'none'; dashModal._planObj = null; }
  if (dashModalClose) dashModalClose.addEventListener('click', closeDashboardModal);
  if (dashModalCancel) dashModalCancel.addEventListener('click', closeDashboardModal);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDashboardModal(); });

  // Save via AJAX
  if (dashModalForm) {
    dashModalForm.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const planId = document.getElementById('dashModalPlanId').value;
      const note = document.getElementById('dashModalNote').value;
      const season = document.getElementById('dashModalSeason').value;
      try {
        dashModalSave.disabled = true;
        const fd = new FormData(); fd.append('id', planId); fd.append('note', note); fd.append('season_hint', season); fd.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        const res = await fetch('<?=h(url_path('src/planner/update.php'))?>', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json().catch(() => null);
        if (json && json.success) { closeDashboardModal(); refreshDashboardPlanner(); window.showToast('Plan updated', 'success'); }
        else { window.showToast('Failed to update plan', 'error'); }
      } catch (err) { console.error(err); window.showToast('Error updating plan', 'error'); }
      dashModalSave.disabled = false;
    });
  }
  // Delete via modal
  if (dashModalDelete) {
    dashModalDelete.addEventListener('click', async () => {
      if (!await Modal.confirm('Are you sure you want to delete this plan?', 'danger')) return;
      const planId = document.getElementById('dashModalPlanId').value;
      dashModalDelete.disabled = true;
      try {
        const fd = new FormData(); fd.append('id', planId); fd.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        const res = await fetch('<?=h(url_path('src/planner/delete.php'))?>', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json().catch(() => null);
        if (json && json.success) { closeDashboardModal(); refreshDashboardPlanner(); window.showToast('Plan deleted', 'success'); }
        else { window.showToast('Failed to delete plan', 'error'); }
      } catch (err) { console.error(err); window.showToast('Error deleting plan', 'error'); }
      dashModalDelete.disabled = false;
    });
  }
});
</script>
<?php include __DIR__ . '/templates/footer.php'; ?>