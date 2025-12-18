<?php
require_once __DIR__ . '/../config.php';
requireLogin();

$category = $_GET['category'] ?? '';
$q = trim($_GET['q'] ?? '');

$sql = 'SELECT * FROM '. TBL_CLOTHES .' WHERE user_id = ?';
$params = [$_SESSION['user_id']];

if ($category) {
    $sql .= ' AND category = ?';
    $params[] = $category;
}

if ($q !== '') {
    $sql .= ' AND (name LIKE ? OR colour LIKE ?)';
    $params[] = "%{$q}%";
    $params[] = "%{$q}%";
}

$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clothes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../templates/header.php';
?>

<div class="page-shell wardrobe-shell">
    <div class="section-header" style="margin-bottom:0.75rem;">
        <div>
            <p class="hero-kicker" style="letter-spacing:0.18em;">Wardrobe</p>
            <h2 class="hero-title" style="font-size:2.1rem; margin:0;">Your curated closet</h2>
            <p class="muted">Filter, favorite, and plan outfits from a glassy grid.</p>
        </div>
        <div class="hero-actions">
            <a class="pill-btn pill-primary" href="<?= h(url_path('src/clothes/upload.php')); ?>"><i class="fa-solid fa-cloud-arrow-up"></i> Upload item</a>
            <a class="pill-btn pill-ghost" href="<?= h(url_path('src/outfits/create.php')); ?>"><i class="fa-solid fa-wand-magic-sparkles"></i> Build outfit</a>
        </div>
    </div>

    <div class="card glass-card" style="margin-bottom:1rem;">
        <div class="filter-row">
            <div class="filter-chips" aria-label="Filter by category">
                <?php
                    $cats = [
                        '' => 'All',
                        'top' => 'Tops',
                        'bottom' => 'Bottoms',
                        'shoes' => 'Shoes',
                        'accessory' => 'Accessories',
                        'other' => 'Other',
                    ];
                ?>
                <?php foreach ($cats as $key => $label): $active = ($category === $key) || ($key === '' && !$category); ?>
                    <a class="chip <?= $active ? 'active' : '' ?>" href="list.php<?= $key ? '?category=' . urlencode($key) : '' ?>"><?= h($label) ?></a>
                <?php endforeach; ?>
            </div>
            <div style="display:flex; gap:0.6rem; flex-wrap:wrap; align-items:center;">
                <div class="input-glass">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input id="wardrobeSearch" type="search" placeholder="Search name or color" value="<?= h($q) ?>">
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($clothes)): ?>
        <div class="card glass-card" style="text-align:center;">
            <h3 class="card-title">No items yet</h3>
            <p class="muted">Upload your first piece to start planning outfits.</p>
            <a class="pill-btn pill-primary" href="<?= h(url_path('src/clothes/upload.php')); ?>"><i class="fa-solid fa-cloud-arrow-up"></i> Upload item</a>
        </div>
    <?php else: ?>
        <div class="wardrobe-grid modern" id="wardrobe-grid">
            <?php foreach ($clothes as $item): ?>
                <div class="wardrobe-card" data-id="<?= (int)$item['id'] ?>" data-name="<?= h(strtolower($item['name'])) ?>" data-colour="<?= h(strtolower($item['colour'] ?? '')) ?>">
                    <div class="wardrobe-thumb">
                        <img src="<?= h($item['image_path']) ?>" alt="<?= h($item['name']) ?>">
                        <button class="fav-toggle" data-id="<?= (int)$item['id'] ?>" data-fav="<?= (int)$item['favorite'] ?>" aria-label="Favorite"><i class="fa-solid fa-star"></i></button>
                        <button class="laundry-toggle" data-id="<?= (int)$item['id'] ?>" data-laundry="<?= (int)$item['in_laundry'] ?>" aria-label="In Laundry" title="<?= $item['in_laundry'] ? 'In laundry' : 'Mark as in laundry' ?>"><i class="fa-solid fa-soap"></i></button>
                        <span class="tag chip-sm"><?= h(ucfirst($item['category'])) ?></span>
                    </div>
                    <div class="wardrobe-meta">
                        <div class="wardrobe-title">
                            <h4><?= h($item['name']) ?></h4>
                            <?php if (!empty($item['colour'])): ?><span class="muted"><?= h($item['colour']) ?></span><?php endif; ?>
                        </div>
                        <div class="wardrobe-actions">
                            <form method="POST" action="delete.php" class="delete-form">
                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                <button type="submit" class="btn btn-secondary btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const favButtons = document.querySelectorAll('.fav-toggle');
    const laundryButtons = document.querySelectorAll('.laundry-toggle');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    // Favorite button handler
    favButtons.forEach(btn => {
        const id = btn.dataset.id;
        if (btn.dataset.fav && btn.dataset.fav === '1') btn.classList.add('active');
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            try {
                const form = new FormData();
                form.append('id', id);
                form.append('action', 'toggle_favorite');
                form.append('csrf_token', csrf);
                const resp = await fetch('<?=h(url_path('src/clothes/toggle.php'))?>', { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await resp.json();
                if (json.success) {
                    const active = json.favorite == 1;
                    btn.classList.toggle('active', active);
                    btn.dataset.fav = active ? '1' : '0';
                    window.showToast(active ? 'Marked item as favorite' : 'Removed item from favorites', 'success');
                }
            } catch (e) {
                console.error(e);
                window.showToast('Error toggling favorite', 'error');
            }
            btn.disabled = false;
        });
    });

    // Laundry button handler
    laundryButtons.forEach(btn => {
        const id = btn.dataset.id;
        if (btn.dataset.laundry && btn.dataset.laundry === '1') btn.classList.add('active');
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            try {
                const form = new FormData();
                form.append('id', id);
                form.append('action', 'toggle_laundry');
                form.append('csrf_token', csrf);
                const resp = await fetch('<?=h(url_path('src/clothes/toggle.php'))?>', { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await resp.json();
                if (json.success) {
                    const active = json.in_laundry == 1;
                    btn.classList.toggle('active', active);
                    btn.dataset.laundry = active ? '1' : '0';
                    btn.title = active ? 'In laundry' : 'Mark as in laundry';
                    window.showToast(active ? 'Item marked as in laundry' : 'Item removed from laundry', 'success');
                }
            } catch (e) {
                console.error(e);
                window.showToast('Error toggling laundry status', 'error');
            }
            btn.disabled = false;
        });
    });

        // search filtering client side
        const searchInput = document.getElementById('wardrobeSearch');
        const cards = document.querySelectorAll('#wardrobe-grid .wardrobe-card');
        const applySearch = () => {
            const term = (searchInput?.value || '').toLowerCase();
            cards.forEach(card => {
                const name = card.dataset.name || '';
                const colour = card.dataset.colour || '';
                const match = !term || name.includes(term) || colour.includes(term);
                card.style.display = match ? '' : 'none';
            });
        };
        if (searchInput) searchInput.addEventListener('input', applySearch);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const deleteForms = document.querySelectorAll('.delete-form');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    deleteForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!await Modal.confirm('Are you sure you want to delete this item? This action cannot be undone.', 'danger')) return;
            const fd = new FormData(form);
            if (!fd.get('csrf_token')) fd.append('csrf_token', csrf);
            try {
                const res = await fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json().catch(()=>null);
                if (res.ok && json && json.success) {
                    const itemId = fd.get('id');
                    const itemCard = document.querySelector('.wardrobe-card[data-id="' + itemId + '"]');
                    if (itemCard) itemCard.remove();
                    if (typeof refreshDashboardPlanner === 'function') refreshDashboardPlanner();
                    window.showToast('Item deleted', 'success');
                } else {
                    window.showToast('Failed to delete item', 'error');
                }
            } catch (err) { console.error(err); window.showToast('Error deleting item', 'error'); }
        });
    });
});
</script>
