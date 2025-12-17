<?php
require_once __DIR__ . '/../src/config.php';

$token = $_GET['token'] ?? '';
if ($token === '' || !preg_match('/^[a-f0-9]{32}$/', $token)) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

// Fetch shared outfit if token valid
$sql = 'SELECT s.token, s.expires_at, o.*, 
               t.image_path AS top_img, t.name AS top_name,
               b.image_path AS bottom_img, b.name AS bottom_name,
               s1.image_path AS shoe_img, s1.name AS shoe_name,
               a.image_path AS accessory_img, a.name AS accessory_name
        FROM '. TBL_SHARED_OUTFITS .' s
        INNER JOIN '. TBL_OUTFITS .' o ON s.outfit_id = o.id
        LEFT JOIN '. TBL_CLOTHES .' t ON o.top_id = t.id
        LEFT JOIN '. TBL_CLOTHES .' b ON o.bottom_id = b.id
        LEFT JOIN '. TBL_CLOTHES .' s1 ON o.shoe_id = s1.id
        LEFT JOIN '. TBL_CLOTHES .' a ON o.accessory_id = a.id
        WHERE s.token = ? AND s.is_public = 1 AND (s.expires_at IS NULL OR s.expires_at >= NOW())
        LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute([$token]);
$share = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$share) {
    http_response_code(404);
    echo 'Share link is invalid or expired.';
    exit;
}

include __DIR__ . '/../src/templates/header.php';
?>
<div class="page-shell share-shell">
<section class="card">
  <div class="card-header">
    <div>
      <h2><?=h($share['title'] ?: 'Shared outfit')?></h2>
      <p class="muted">Shared outfit preview</p>
    </div>
  </div>
  <div class="outfit-share-grid">
    <div>
      <p class="label">Top</p>
      <?php if ($share['top_img']): ?>
        <?php
          $path = $share['top_img'];
              if (strpos($path, '/uploads/') === false && preg_match('#/uploads/[^\s]+#', $path, $m)) {
                $path = $m[0];
              }
          $img = url_path('public' . ltrim($path, '/'));
        ?>
        <div class="thumb" style="background-image:url('<?=h($img)?>');"></div>
        <p class="muted"><?=h($share['top_name'])?></p>
      <?php else: ?><p class="muted">None</p><?php endif; ?>
    </div>
    <div>
      <p class="label">Bottom</p>
      <?php if ($share['bottom_img']): ?>
        <?php
          $path = $share['bottom_img'];
          if (strpos($path, '/uploads/') === false && preg_match('#/uploads/[^\s]+#', $path, $m)) {
              $path = $m[0];
          }
          $img = url_path('public' . ltrim($path, '/'));
        ?>
        <div class="thumb" style="background-image:url('<?=h($img)?>');"></div>
        <p class="muted"><?=h($share['bottom_name'])?></p>
      <?php else: ?><p class="muted">None</p><?php endif; ?>
    </div>
    <div>
      <p class="label">Shoes</p>
      <?php if ($share['shoe_img']): ?>
        <?php
          $path = $share['shoe_img'];
          if (strpos($path, '/uploads/') === false && preg_match('#/uploads/[^\s]+#', $path, $m)) {
              $path = $m[0];
          }
          $img = url_path('public' . ltrim($path, '/'));
        ?>
        <div class="thumb" style="background-image:url('<?=h($img)?>');"></div>
        <p class="muted"><?=h($share['shoe_name'])?></p>
      <?php else: ?><p class="muted">None</p><?php endif; ?>
    </div>
    <div>
      <p class="label">Accessory</p>
      <?php if ($share['accessory_img']): ?>
        <?php
          $path = $share['accessory_img'];
          if (strpos($path, '/uploads/') === false && preg_match('#/uploads/[^\s]+#', $path, $m)) {
              $path = $m[0];
          }
          $img = url_path('public' . ltrim($path, '/'));
        ?>
        <div class="thumb" style="background-image:url('<?=h($img)?>');"></div>
        <p class="muted"><?=h($share['accessory_name'])?></p>
      <?php else: ?><p class="muted">None</p><?php endif; ?>
    </div>
  </div>
</section>
</div>
<?php include __DIR__ . '/../src/templates/footer.php'; ?>
