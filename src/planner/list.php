<?php
require_once __DIR__ . '/../config.php';
requireLogin();

// Fetch user's vw_outfits for selection
$outfitsStmt = $pdo->prepare('SELECT id, title, created_at FROM ' . TBL_OUTFITS . ' WHERE user_id = ? ORDER BY created_at DESC');
$outfitsStmt->execute([$_SESSION['user_id']]);
$outfits = $outfitsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch planned vw_outfits upcoming
$plansStmt = $pdo->prepare('SELECT p.*, o.title
                            FROM ' . TBL_OUTFITS_PLANNED . ' p
                            INNER JOIN ' . TBL_OUTFITS . ' o ON p.outfit_id = o.id
                            WHERE p.user_id = ?
                            ORDER BY p.planned_for ASC, p.id DESC');
$plansStmt->execute([$_SESSION['user_id']]);
$plans = $plansStmt->fetchAll(PDO::FETCH_ASSOC);

$redirectPath = url_path('src/planner/list.php');
$today = date('Y-m-d');

include __DIR__ . '/../templates/header.php';
?>
<div class="planner-shell">
  <div class="planner-header">
    <div>
      <p class="eyebrow">Planner</p>
      <h1 class="planner-title">Plan by list</h1>
      <p class="muted">Create and manage dated outfit plans with quick edits.</p>
    </div>
    <div class="planner-actions">
      <a class="btn btn-secondary" href="<?=h(url_path('src/planner/calendar.php'))?>">Calendar</a>
      <a class="btn btn-primary" href="<?=h(url_path('src/planner/list.php'))?>">List</a>
    </div>
  </div>

  <div class="planner-layout list-layout">
    <section class="planner-panel form-panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">New plan</p>
          <h3>Create an entry</h3>
          <p class="muted small">Pick an outfit, date, and optional note.</p>
        </div>
      </div>
      <?php if (empty($outfits)): ?>
        <div class="planner-empty">You need at least one outfit to create a plan.</div>
      <?php else: ?>
        <form method="post" action="<?=url_path('src/planner/plan.php')?>" class="form-grid planner-form">
          <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
          <input type="hidden" name="redirect" value="<?=h($redirectPath)?>">
          <label class="form-field">Outfit
            <select name="outfit_id" required>
              <option value="">Select an outfit</option>
              <?php foreach ($outfits as $o): ?>
                <option value="<?=$o['id']?>"><?=h($o['title'] ?: 'Untitled outfit')?> (<?=h(date('M j', strtotime($o['created_at'])))?>)</option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="form-field">Date
            <input type="date" name="planned_for" required value="<?=h($today)?>">
          </label>
          <label class="form-field">Season
            <select name="season_hint">
              <option value="all">All</option>
              <option value="spring">Spring</option>
              <option value="summer">Summer</option>
              <option value="fall">Fall</option>
              <option value="winter">Winter</option>
            </select>
          </label>
          <label class="form-field">Note
            <input type="text" name="note" maxlength="255" placeholder="Optional note (e.g. dinner, meeting)">
          </label>
          <button class="btn btn-primary" type="submit">Save plan</button>
        </form>
      <?php endif; ?>
    </section>

    <section class="planner-panel list-panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Upcoming</p>
          <h3>Your scheduled outfits</h3>
          <p class="muted small">Sorted by date (soonest first).</p>
        </div>
      </div>
      <?php if (empty($plans)): ?>
        <div class="planner-empty">No plans yet.</div>
      <?php else: ?>
        <div class="plan-list">
          <?php foreach ($plans as $plan): ?>
            <div class="plan-row">
              <div class="plan-date">
                <p class="label">Date</p>
                <p><?=h(date('D, M j', strtotime($plan['planned_for'])))?></p>
              </div>
              <div class="plan-body">
                <div class="plan-title"><?=h($plan['title'] ?: 'Untitled outfit')?></div>
                <?php if (!empty($plan['note'])): ?>
                  <p class="muted small"><?=h($plan['note'])?></p>
                <?php endif; ?>
                <span class="pill pill-type">Season: <?=h($plan['season_hint'])?></span>
              </div>
              <form class="plan-actions" method="post" action="<?=url_path('src/planner/delete.php')?>">
                <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
                <input type="hidden" name="id" value="<?=h($plan['id'])?>">
                <input type="hidden" name="redirect" value="<?=h($redirectPath)?>">
                <button class="btn-text" type="submit">Remove</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
