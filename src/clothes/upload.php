<?php
require_once __DIR__ . '/../config.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? 'other';
        $colour = trim($_POST['colour'] ?? '');

        if (empty($name)) {
            $error = 'Name is required';
        } else if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Image upload error';
        } else {
            $file = $_FILES['image'];
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            
            if (!in_array($mime, $allowed)) {
                $error = 'Invalid image type. Only JPEG, PNG, and WebP allowed.';
            } else if ($file['size'] > 10 * 1024 * 1024) {
                $error = 'File too large. Maximum 10MB allowed.';
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $targetDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $targetPath = $targetDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $relPath = url_path('public/uploads/') . $filename;
                    $stmt = $pdo->prepare('INSERT INTO '. TBL_CLOTHES .' (user_id, name, category, colour, image_path) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$_SESSION['user_id'], $name, $category, $colour, $relPath]);
                    header('Location: list.php');
                    exit;
                } else {
                    $error = 'Failed to move uploaded file';
                }
            }
        }
    }
}

include __DIR__ . '/../templates/header.php';
?>

<div class="page-shell upload-page">
    <div class="upload-header">
        <div>
            <p class="upload-eyebrow">New item</p>
            <h1 class="upload-title">Upload Clothing Item</h1>
            <p class="upload-subtext">Capture a crisp photo, choose the right category, and keep your wardrobe layered and organized.</p>
        </div>
        <div class="upload-meta">
            <span class="badge-outline">Max 10MB</span>
            <span class="badge-outline">JPEG · PNG · WebP</span>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="notice error"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <div class="upload-grid">
        <div class="form-panel upload-panel">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
                <div class="form-grid">
                    <div class="form-field">
                        <label for="name">Name</label>
                        <input id="name" class="form-control" type="text" name="name" required value="<?=htmlspecialchars($_POST['name'] ?? '')?>" placeholder="e.g., Indigo Overshirt">
                    </div>

                    <div class="form-field">
                        <label for="category">Category</label>
                        <select id="category" name="category" required class="form-control">
                            <option value="top">Top</option>
                            <option value="bottom">Bottom</option>
                            <option value="shoes">Shoes</option>
                            <option value="accessory">Accessory</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="colour">Color</label>
                        <input id="colour" class="form-control" type="text" name="colour" value="<?=htmlspecialchars($_POST['colour'] ?? '')?>" placeholder="e.g., Navy, Charcoal">
                    </div>

                    <div class="form-field">
                        <label for="image">Image</label>
                        <input id="image" class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
                        <small>Use natural light; keep the background minimal for a clean preview.</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Upload</button>
                    <a href="list.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <aside class="upload-preview-card" aria-live="polite">
            <div class="preview-header">
                <h3 class="preview-title">Live Preview</h3>
                <span class="preview-badge">Layered</span>
            </div>
            <div id="image-preview" class="preview-frame">
                <p class="preview-empty">Your image will appear here.</p>
            </div>
            <p class="preview-hint">Polish the shot for a professional, layered look across the wardrobe grid.</p>
            <ul class="preview-tips">
                <li><span class="preview-dot"></span><span>Center the garment with a touch of breathing room.</span></li>
                <li><span class="preview-dot"></span><span>Use even lighting to keep colors true to life.</span></li>
                <li><span class="preview-dot"></span><span>Square crop works best for balanced layouts.</span></li>
            </ul>
        </aside>
    </div>
</div>

<script>
(() => {
    const fileInput = document.getElementById('image');
    const preview = document.getElementById('image-preview');
    const placeholder = '<p class="preview-empty">Your image will appear here.</p>';

    if (!fileInput || !preview) return;
    preview.innerHTML = placeholder;

    fileInput.addEventListener('change', (event) => {
        const file = event.target.files && event.target.files[0];

        if (!file) {
            preview.innerHTML = placeholder;
            return;
        }

        const url = URL.createObjectURL(file);
        const img = new Image();
        img.src = url;
        img.alt = file.name;
        img.onload = () => URL.revokeObjectURL(url);

        preview.innerHTML = '';
        preview.appendChild(img);
    });
})();
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
