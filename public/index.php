<?php
require_once __DIR__ . '/../src/config.php';

if (isLoggedIn()) {
    header('Location: ' . url_path('src/dashboard.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Wardrobe & Outfit Planner</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?=h(url_path('public/css/landing.css'))?>">
</head>
<body>
    <div class="video-background">
        <video autoplay muted loop playsinline>
            <source src="<?=h(url_path('public/media/wardrobe-bg.mp4'))?>" type="video/mp4">
        </video>
        <div class="video-overlay"></div>
    </div>

    <main class="landing-shell">
        <header class="hero">
            <p class="eyebrow">VIRTUAL WARDROBE · OUTFIT PLANNER</p>
            <h1>Organize. Style. Shine.</h1>
            <p class="subtitle">Upload your clothes, curate categories, and build outfits with confidence.</p>
            <div class="cta-row">
                <a class="btn-landing primary" href="<?=h(url_path('src/auth/register.php'))?>">Get Started</a>
                <a class="btn-landing secondary" href="<?=h(url_path('src/auth/login.php'))?>">Login</a>
            </div>
        </header>

        <section class="feature-grid">
            <article class="feature-card">
                <div class="feature-icon-wrapper blue">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <h3>Upload Your Wardrobe</h3>
                <p>Snap, store, and manage every look with crystal-clear previews.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon-wrapper rose">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <h3>Organize by Category</h3>
                <p>Smart filters for tops, bottoms, shoes, accessories, and more.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon-wrapper amber">
                    <i class="fa-solid fa-pencil"></i>
                </div>
                <h3>Create Outfits</h3>
                <p>Mix and match items, save favorites, and plan ahead.</p>
            </article>
        </section>

        <footer class="landing-footer">© <?=date('Y')?> Virtual Wardrobe</footer>
    </main>
</body>
</html>
