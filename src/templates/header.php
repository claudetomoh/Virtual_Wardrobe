<?php
require_once __DIR__ . '/../config.php';
$isDashboard = isset($_SERVER['SCRIPT_NAME']) && str_contains($_SERVER['SCRIPT_NAME'], 'dashboard.php');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Virtual Wardrobe</title>
  <link rel="stylesheet" href="<?=h(url_path('public/css/styles.css'))?>?v=20251216" />
  <!-- Add FontAwesome for professional icons in header and dashboard buttons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Inter font for consistent, modern typography -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <meta name="csrf-token" content="<?=h(csrf_token())?>" />
  <meta name="user-id" content="<?=h($_SESSION['user_id'] ?? 0)?>" />
  <meta name="socket-server-url" content="<?=h(getenv('SOCKET_SERVER_URL') ?: 'http://localhost:3000')?>" />
  <?php if (isLoggedIn()): ?>
    <meta name="socket-token" content="<?=h(socket_jwt_for_user($_SESSION['user_id']));?>" />
  <?php endif; ?>
  <script src="https://cdn.socket.io/4.7.2/socket.io.min.js" async defer></script>
</head>
<body>
<nav class="navbar">
  <div class="nav-container">
    <a href="<?=h(url_path('public/index.php'))?>" class="nav-brand">
      <span class="nav-dot"></span>
      Virtual Wardrobe
    </a>

    <div class="nav-cluster">
      <?php if (isLoggedIn()): ?>
        <div class="nav-main">
          <a class="nav-link<?= !$isDashboard && str_contains($_SERVER['SCRIPT_NAME'], 'clothes') ? ' active' : '' ?>" href="<?=h(url_path('src/clothes/list.php'))?>">Wardrobe</a>
          <a class="nav-link<?= !$isDashboard && str_contains($_SERVER['SCRIPT_NAME'], 'outfits') ? ' active' : '' ?>" href="<?=h(url_path('src/outfits/list.php'))?>">Outfits</a>
          <a class="nav-link<?= !$isDashboard && str_contains($_SERVER['SCRIPT_NAME'], 'planner') ? ' active' : '' ?>" href="<?=h(url_path('src/planner/calendar.php'))?>">Planner</a>
          <a class="nav-link<?= $isDashboard ? ' active' : '' ?>" href="<?=h(url_path('src/dashboard.php'))?>">Dashboard</a>
        </div>

        <div class="nav-actions" aria-label="Status and profile">
          <div class="status-cluster">
            <span id="liveStatusDot" class="live-status" title="Live connection status"></span>
            <button id="liveBadge" class="live-badge" type="button" title="Live updates" aria-expanded="false" aria-controls="livePanel">0</button>
          </div>

          <div class="profile-shell" aria-label="Profile menu">
            <button class="avatar-btn" type="button" aria-haspopup="true" aria-expanded="false">
              <span class="nav-avatar" title="<?=htmlspecialchars($_SESSION['user_name'])?>"><?=htmlspecialchars(substr($_SESSION['user_name'], 0, 1))?></span>
            </button>
            <div class="profile-menu" role="menu">
              <a role="menuitem" href="<?=h(url_path('src/auth/logout.php'))?>"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
            </div>
          </div>
        </div>

        <!-- Live activity panel -->
        <div id="livePanel" class="live-panel" aria-hidden="true">
          <div class="live-panel-header">
            <div>
              <div class="live-panel-title">Live updates</div>
              <div class="live-panel-sub">Latest planner activity</div>
            </div>
            <button id="livePanelClose" class="live-panel-close" type="button" aria-label="Close live updates">×</button>
          </div>
          <div id="livePanelList" class="live-panel-list">
            <div class="live-panel-empty">No recent updates yet.</div>
          </div>
        </div>
      <?php else: ?>
        <div class="nav-main">
          <a class="nav-link" href="<?=h(url_path('src/auth/login.php'))?>">Login</a>
          <a class="nav-link" href="<?=h(url_path('src/auth/register.php'))?>">Register</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main class="main-content">
