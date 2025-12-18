<?php
require_once __DIR__ . '/../config.php';
requireLogin();

$stmt = $pdo->prepare('SELECT *, IFNULL(is_favorite, 0) AS is_favorite FROM ' . TBL_OUTFITS . ' WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$outfits = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../templates/header.php';
?>
<div class="page-shell outfits-shell">
    <div class="section-header" style="margin-bottom:0.75rem;">
        <div>
            <p class="hero-kicker" style="letter-spacing:0.18em;">Outfits</p>
            <h2 class="hero-title" style="font-size:2.1rem; margin:0;">Your looks, polished</h2>
            <p class="muted">Filter, favorite, wear-track, share, and plan across the week.</p>
        </div>
        <div class="hero-actions">
            <a href="create.php" class="pill-btn pill-primary"><i class="fa-solid fa-plus"></i> Create outfit</a>
            <a href="<?= h(url_path('src/clothes/list.php')) ?>" class="pill-btn pill-ghost"><i class="fa-solid fa-shirt"></i> Wardrobe</a>
        </div>
    </div>

    <?php if (!empty($_GET['share_token']) && !empty($_GET['outfit_id'])): ?>
        <?php $shareUrl = url_path('public/share.php') . '?token=' . urlencode($_GET['share_token']); ?>
        <div class="card glass-card share-banner">
            <div>
                <strong>Outfit shared</strong>
                <p class="muted">Share link created for outfit #<?= (int) $_GET['outfit_id'] ?>.</p>
            </div>
            <div class="input-glass" style="flex:1;">
                <i class="fa-solid fa-link"></i>
                <input type="text" id="shareLink" value="<?= h($shareUrl) ?>" readonly>
            </div>
            <button id="copyShareBtn" class="btn btn-primary"><i class="fa-solid fa-copy"></i> Copy</button>
        </div>
    <?php endif; ?>

    <div class="card glass-card" style="margin-bottom:1rem;">
        <div class="filter-row">
            <div class="input-glass" style="flex:1; min-width:220px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="outfitSearch" type="text" placeholder="Search vw_outfits or items...">
            </div>
            <div class="filter-chips" id="chipRow">
                <button class="chip active" data-filter="all">All</button>
                <button class="chip" data-filter="top">Tops</button>
                <button class="chip" data-filter="bottom">Bottoms</button>
                <button class="chip" data-filter="shoe">Shoes</button>
                <button class="chip" data-filter="accessory">Accessories</button>
            </div>
            <label class="fav-toggle" style="margin-left:auto;">
                <input type="checkbox" id="outfitFavOnly"> Favorites only
            </label>
        </div>
    </div>

    <?php if (empty($outfits)): ?>
        <div class="card glass-card empty-card" style="text-align:center;">
            <h3 class="card-title">No Outfits Yet</h3>
            <p class="muted">Start creating outfit combinations from your wardrobe.</p>
            <a href="create.php" class="pill-btn pill-primary"><i class="fa-solid fa-plus"></i> Create first outfit</a>
        </div>
    <?php else: ?>
        <div class="card glass-card" style="margin-bottom:1rem;">
            <p class="muted">You have <strong><?= count($outfits) ?></strong> saved outfit<?= count($outfits) != 1 ? 's' : '' ?>.</p>
        </div>
        <div class="outfits-list" id="outfits-list">
            <?php foreach ($outfits as $index => $outfit): ?>
                <?php
                $itemIds = array_values(array_filter([
                    $outfit['top_id'],
                    $outfit['bottom_id'],
                    $outfit['shoe_id'],
                    $outfit['accessory_id'],
                ]));
                $items = [];
                $tags = [];
                if (!empty($itemIds)) {
                        $ph = implode(',', array_fill(0, count($itemIds), '?'));
                        $stmt = $pdo->prepare("SELECT name, image_path, category FROM " . TBL_CLOTHES . " WHERE id IN ($ph)");
                        $stmt->execute($itemIds);
                        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($items as $it) if (!empty($it['category'])) $tags[] = strtolower($it['category']);
                }
                $tagAttr = implode(',', array_unique($tags));
                ?>
                <div class="outfit-card" data-tags="<?= h($tagAttr) ?>" data-title="<?= h(strtolower($outfit['title'] ?: 'Outfit #'.$outfit['id'])) ?>" data-is-favorite="<?= (int)$outfit['is_favorite'] ?>" style="animation-delay: <?= $index * 0.1 ?>s;">
                    <div class="outfit-card-header">
                        <div>
                            <h3 class="card-title" style="margin:0; display:flex; align-items:center; gap:0.4rem;">
                                <i class="fa-solid fa-sparkles" style="color:var(--vw-primary);"></i>
                                <?= h($outfit['title'] ?: 'Outfit #'.$outfit['id']) ?>
                            </h3>
                            <p class="muted" style="margin:0; font-size:0.9rem;">Created <?= date('M d, Y', strtotime($outfit['created_at'])) ?></p>
                        </div>
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <button class="fav-btn small" type="button" data-id="<?= $outfit['id'] ?>" data-is-favorite="<?= (int)$outfit['is_favorite'] ?>" aria-label="Favorite"><i class="fa-solid fa-star"></i></button>
                        </div>
                    </div>

                    <?php if (!empty($items)): ?>
                        <div class="outfit-items">
                            <?php foreach ($items as $item): ?>
                                <div class="outfit-item" data-tag="<?= h(strtolower($item['category'] ?? '')) ?>">
                                    <img src="<?= h($item['image_path']) ?>" alt="<?= h($item['name']) ?>">
                                    <p><?= h($item['name']) ?></p>
                                    <small><?= h(ucfirst($item['category'])) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="outfit-empty-state">
                            <i class="fa-solid fa-triangle-exclamation" style="font-size:2rem; color:var(--vw-accent); opacity:0.6;"></i>
                            <p style="margin:0.5rem 0 0; color:rgba(232,237,247,0.7); font-weight:600;">Items Missing</p>
                            <p style="margin:0.3rem 0 0; font-size:0.85rem; color:rgba(232,237,247,0.5);">
                                The clothing items for this outfit have been deleted from your wardrobe.
                            </p>
                            <p style="margin:0.5rem 0 0; font-size:0.85rem; color:rgba(232,237,247,0.5);">
                                Re-create this outfit with existing items or delete it.
                            </p>
                        </div>
                    <?php endif; ?>

                    <div class="outfit-actions">
                        <form class="wear-form" method="POST" action="wear.php">
                            <input type="hidden" name="id" value="<?= $outfit['id'] ?>">
                            <input type="hidden" name="redirect" value="<?= h(url_path('src/outfits/list.php')) ?>">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()); ?>">
                            <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-person-walking"></i> Wear</button>
                        </form>
                        <form method="POST" action="share.php">
                            <input type="hidden" name="id" value="<?= $outfit['id'] ?>">
                            <input type="hidden" name="expiry_days" value="7">
                            <input type="hidden" name="redirect" value="<?= h(url_path('src/outfits/list.php')) ?>">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()); ?>">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-link"></i> Share</button>
                        </form>
                        <div class="plan-wrapper">
                            <button class="btn btn-secondary btn-plan" data-id="<?= $outfit['id'] ?>"><i class="fa-solid fa-calendar-days"></i> Plan</button>
                            <form method="POST" action="<?= h(url_path('src/planner/plan.php')) ?>" class="plan-form" style="display:none;">
                                <input type="hidden" name="outfit_id" value="<?= $outfit['id'] ?>">
                                <input type="hidden" name="redirect" value="<?= h(url_path('src/outfits/list.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()); ?>">
                                <input type="date" name="planned_for" required value="<?= h(date('Y-m-d')) ?>">
                                <select name="season_hint">
                                    <option value="all">All</option>
                                    <option value="spring">Spring</option>
                                    <option value="summer">Summer</option>
                                    <option value="fall">Fall</option>
                                    <option value="winter">Winter</option>
                                </select>
                                <button class="btn btn-primary" type="submit">Save</button>
                            </form>
                        </div>
                        <form method="POST" action="delete.php" class="delete-form">
                            <input type="hidden" name="id" value="<?= $outfit['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()); ?>">
                            <button type="submit" class="btn btn-danger" aria-label="Delete outfit"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script>
// Filter by search + tag + favorites
document.addEventListener('DOMContentLoaded',()=>{
    const searchInput=document.getElementById('outfitSearch');
    const chips=document.querySelectorAll('.chip');
    const cards=document.querySelectorAll('.outfit-card');
    const favOnly=document.getElementById('outfitFavOnly');
    window.applyOutfitFilters=()=>{
        const term=(searchInput?.value||'').toLowerCase();
        const filter=(document.querySelector('.chip.active')||{}).dataset?.filter||'all';
        const wantFav=favOnly?.checked;
        cards.forEach(card=>{
            const tags=(card.dataset.tags||'').toLowerCase();
            const title=(card.dataset.title||'').toLowerCase();
            const isFav=card.dataset.isFavorite==='1';
            const matchTerm=!term||title.includes(term)||tags.includes(term);
            const matchTag=filter==='all'||tags.includes(filter);
            const matchFav=!wantFav||isFav;
            card.style.display=(matchTerm&&matchTag&&matchFav)?'':'none';
        });
    };
    chips.forEach(chip=>chip.addEventListener('click',()=>{chips.forEach(c=>c.classList.remove('active'));chip.classList.add('active');window.applyOutfitFilters();}));
    searchInput?.addEventListener('input',window.applyOutfitFilters);
    favOnly?.addEventListener('change',window.applyOutfitFilters);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', ()=>{
    const deleteForms = document.querySelectorAll('.delete-form');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    deleteForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!confirm('⚠️ Delete this outfit?')) return;
            const fd = new FormData(form);
            if (!fd.get('csrf_token')) fd.append('csrf_token', csrf);
            try {
                const res = await fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json().catch(()=>null);
                if (res.ok && json && json.success) {
                    // remove the outfit card
                    const card = form.closest('.outfit-card');
                    if (card) card.remove();
                    if (typeof refreshDashboardPlanner === 'function') refreshDashboardPlanner();
                    window.showToast('Outfit deleted', 'success');
                } else {
                    window.showToast('Failed to delete outfit', 'error');
                }
            } catch (err) { console.error(err); window.showToast('Error deleting outfit', 'error'); }
        });
    });
});
</script>
<script>
    // show success toast if there's a share token in query string
    document.addEventListener('DOMContentLoaded', () => {
        const q = new URLSearchParams(window.location.search);
        const token = q.get('share_token');
        if (token && typeof window.showToast === 'function') {
            window.showToast('Share link created', 'success');
        }
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', ()=>{
    const wearForms = document.querySelectorAll('.wear-form');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    wearForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!confirm('Mark this outfit as worn? This will update the wear count and put items in laundry.')) return;
            const fd = new FormData(form);
            if (!fd.get('csrf_token')) fd.append('csrf_token', csrf);
            try {
                const res = await fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (res.ok) {
                    window.showToast('Outfit marked as worn', 'success');
                    // disable all buttons in the form and show worn state
                    form.querySelectorAll('button, input').forEach(el => el.disabled = true);
                    const btn = form.querySelector('button[type="submit"]');
                    if (btn) btn.innerHTML = '<i class="fa-solid fa-person-walking"></i> Worn';
                } else { window.showToast('Failed to mark as worn', 'error'); }
            } catch (err) { console.error(err); window.showToast('Error marking as worn', 'error'); }
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const planForms = document.querySelectorAll('.plan-form');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    planForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(form);
            // ensure csrf included
            if (!fd.get('csrf_token')) fd.append('csrf_token', csrf);
            try {
                const res = await fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json().catch(() => null);
                if (!res.ok) {
                    window.showToast('Failed to save plan', 'error');
                } else {
                    window.showToast('Plan saved', 'success');
                    // hide form and show scheduled pill
                    form.style.display = 'none';
                    const wrapper = form.closest('.plan-wrapper');
                    if (wrapper) {
                        let pill = wrapper.querySelector('.planned-pill');
                        if (!pill) {
                            pill = document.createElement('div');
                            pill.className = 'planned-pill';
                            pill.innerHTML = '<i class="fa-solid fa-calendar-days"></i> Planned';
                            wrapper.appendChild(pill);
                        }
                    }
                }
            } catch (err) { console.error(err); window.showToast('Error saving plan', 'error'); }
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', ()=>{
    const copyBtn = document.getElementById('copyShareBtn');
    const shareInput = document.getElementById('shareLink');
    if (copyBtn && shareInput) {
        copyBtn.addEventListener('click', ()=>{
            shareInput.select();
            document.execCommand('copy');
            window.showToast('Share link copied to clipboard', 'success');
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', ()=>{
    const planBtns = document.querySelectorAll('.btn-plan');
    planBtns.forEach(btn => {
        btn.addEventListener('click', e => {
            const wrapper = btn.closest('.plan-wrapper');
            const form = wrapper.querySelector('.plan-form');
            form.style.display = form.style.display === 'none' ? 'inline-flex' : 'none';
        });
    });
});
</script>
<script>
// Favorite toggle via AJAX
document.addEventListener('DOMContentLoaded', ()=>{
    const buttons = document.querySelectorAll('.fav-btn.small');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    buttons.forEach(btn => {
        const id = btn.dataset.id;
        // set initial active from dataset (server); we'll use PHP to mark a data-active if needed
        const card = btn.closest('.outfit-card');
        if (card && card.dataset.isFavorite === '1') btn.classList.add('active');
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
                    const active = json.favorite == 1;
                    btn.classList.toggle('active', active);
                    const card = btn.closest('.outfit-card');
                    if (card) card.dataset.isFavorite = active ? '1' : '0';
                    if (typeof window.applyOutfitFilters === 'function') window.applyOutfitFilters();
                    window.showToast(active ? 'Marked as favorite' : 'Removed from favorites', 'success');
                }
            } catch (e) {
                console.error(e);
                window.showToast('Error toggling favorite', 'error');
            }
            btn.disabled = false;
        });
    });
});
</script>
<?php include __DIR__ . '/../templates/footer.php'; ?>
