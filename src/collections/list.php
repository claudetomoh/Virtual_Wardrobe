<?php
require_once __DIR__ . '/../config.php';
requireLogin();

// Fetch vw_collections with counts
$stmt = $pdo->prepare('SELECT c.id, c.name, c.created_at, COUNT(ci.id) AS item_count
                       FROM '. TBL_COLLECTIONS .' c
                       LEFT JOIN '. TBL_COLLECTION_ITEMS .' ci ON ci.collection_id = c.id
                       WHERE c.user_id = ?
                       GROUP BY c.id, c.name, c.created_at
                       ORDER BY c.created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch items per collection
$itemsByCollection = [];
$collectionIds = array_column($collections, 'id');
if (!empty($collectionIds)) {
    $placeholders = implode(',', array_fill(0, count($collectionIds), '?'));
    $sql = "SELECT ci.id, ci.collection_id, ci.item_type, ci.item_id, ci.created_at,
                   CASE WHEN ci.item_type = 'clothing' THEN cl.name ELSE COALESCE(o.title, 'Untitled outfit') END AS item_name
            FROM '. TBL_COLLECTION_ITEMS .' ci
            INNER JOIN '. TBL_COLLECTIONS .' c ON ci.collection_id = c.id AND c.user_id = ?
            LEFT JOIN '. TBL_CLOTHES .' cl ON ci.item_type = 'clothing' AND ci.item_id = cl.id
            LEFT JOIN '. TBL_OUTFITS .' o ON ci.item_type = 'outfit' AND ci.item_id = o.id
            WHERE ci.collection_id IN ($placeholders)
            ORDER BY ci.created_at DESC";
    $params = array_merge([$_SESSION['user_id']], $collectionIds);
    $itemsStmt = $pdo->prepare($sql);
    $itemsStmt->execute($params);
    foreach ($itemsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $itemsByCollection[$row['collection_id']][] = $row;
    }
}

// Fetch selectable vw_clothes and outfits
$clothesStmt = $pdo->prepare('SELECT id, name, category FROM '. TBL_CLOTHES .' WHERE user_id = ? ORDER BY name');
$clothesStmt->execute([$_SESSION['user_id']]);
$clothes = $clothesStmt->fetchAll(PDO::FETCH_ASSOC);

$outfitsStmt = $pdo->prepare('SELECT id, title FROM '. TBL_OUTFITS .' WHERE user_id = ? ORDER BY created_at DESC');
$outfitsStmt->execute([$_SESSION['user_id']]);
$outfits = $outfitsStmt->fetchAll(PDO::FETCH_ASSOC);

$redirectPath = url_path('src/collections/list.php');

include __DIR__ . '/../templates/header.php';
?>
<section class="card">
  <div class="card-header">
    <div>
      <h2>Collections</h2>
      <p class="muted">Group vw_clothes and vw_outfits into saved collections.</p>
    </div>
  </div>
  <form method="post" action="<?=url_path('src/collections/create.php')?>" class="form-inline">
    <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
    <label>Name
      <input type="text" name="name" required maxlength="150" placeholder="e.g. Travel capsule">
    </label>
    <button class="btn-primary" type="submit">Create collection</button>
  </form>
</section>

<?php if (empty($collections)): ?>
  <section class="card">
    <p>No vw_collections yet. Create one to start grouping vw_outfits or favorite items.</p>
  </section>
<?php else: ?>
  <?php foreach ($collections as $collection): ?>
    <section class="card collection-card">
      <div class="card-header">
        <div>
          <h3><?=h($collection['name'])?></h3>
          <p class="muted"><?=$collection['item_count']?> item(s) · Created <?=h(date('M j, Y', strtotime($collection['created_at'])))?></p>
        </div>
        <form class="delete-collection-form" method="post" action="<?=url_path('src/collections/delete.php')?>" onsubmit="event.preventDefault();">
          <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
          <input type="hidden" name="id" value="<?=h($collection['id'])?>">
          <input type="hidden" name="redirect" value="<?=h($redirectPath)?>">
          <button class="btn-text" type="submit">Delete</button>
        </form>
      </div>

      <div class="collection-add">
        <?php if (!empty($clothes)): ?>
          <form method="post" action="<?=url_path('src/collections/add_item.php')?>" class="collection-form">
            <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
            <input type="hidden" name="collection_id" value="<?=h($collection['id'])?>">
            <input type="hidden" name="item_type" value="clothing">
            <input type="hidden" name="redirect" value="<?=h($redirectPath)?>">
            <label>Add clothing
              <select name="item_id" required>
                <option value="">Choose clothing</option>
                <?php foreach ($clothes as $c): ?>
                  <option value="<?=$c['id']?>"><?=h($c['name'])?> (<?=h($c['category'])?>)</option>
                <?php endforeach; ?>
              </select>
            </label>
            <button class="btn-secondary" type="submit">Add</button>
          </form>
        <?php endif; ?>

        <?php if (!empty($outfits)): ?>
          <form method="post" action="<?=url_path('src/collections/add_item.php')?>" class="collection-form">
            <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
            <input type="hidden" name="collection_id" value="<?=h($collection['id'])?>">
            <input type="hidden" name="item_type" value="outfit">
            <input type="hidden" name="redirect" value="<?=h($redirectPath)?>">
            <label>Add outfit
              <select name="item_id" required>
                <option value="">Choose outfit</option>
                <?php foreach ($outfits as $o): ?>
                  <option value="<?=$o['id']?>"><?=h($o['title'] ?: 'Untitled outfit')?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <button class="btn-secondary" type="submit">Add</button>
          </form>
        <?php endif; ?>
      </div>

      <div class="collection-items">
        <?php $items = $itemsByCollection[$collection['id']] ?? []; ?>
        <?php if (empty($items)): ?>
          <p class="muted">No items in this collection yet.</p>
        <?php else: ?>
          <?php foreach ($items as $item): ?>
            <div class="collection-item">
              <span class="pill pill-type"><?=h($item['item_type'])?></span>
              <span><?=h($item['item_name'])?></span>
              <form method="post" action="<?=url_path('src/collections/remove_item.php')?>">
                <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
                <input type="hidden" name="id" value="<?=h($item['id'])?>">
                <input type="hidden" name="redirect" value="<?=h($redirectPath)?>">
                <button class="btn-text" type="submit">Remove</button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  <?php endforeach; ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const deleteForms = document.querySelectorAll('.delete-collection-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!await Modal.confirm('Are you sure you want to delete this collection? This action cannot be undone.', 'danger')) return;
            form.submit();
        });
    });
});
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
