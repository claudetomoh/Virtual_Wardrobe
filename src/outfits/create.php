<?php
require_once __DIR__ . '/../config.php';
requireLogin();

$error = '';
$success = '';

$tops = $pdo->prepare('SELECT id, name, image_path FROM ' . TBL_CLOTHES . ' WHERE user_id = ? AND category = "top" ORDER BY name');
$tops->execute([$_SESSION['user_id']]);
$tops = $tops->fetchAll(PDO::FETCH_ASSOC);

$bottoms = $pdo->prepare('SELECT id, name, image_path FROM ' . TBL_CLOTHES . ' WHERE user_id = ? AND category = "bottom" ORDER BY name');
$bottoms->execute([$_SESSION['user_id']]);
$bottoms = $bottoms->fetchAll(PDO::FETCH_ASSOC);

$shoes = $pdo->prepare('SELECT id, name, image_path FROM ' . TBL_CLOTHES . ' WHERE user_id = ? AND category = "shoes" ORDER BY name');
$shoes->execute([$_SESSION['user_id']]);
$shoes = $shoes->fetchAll(PDO::FETCH_ASSOC);

$accessories = $pdo->prepare('SELECT id, name, image_path FROM ' . TBL_CLOTHES . ' WHERE user_id = ? AND category = "accessory" ORDER BY name');
$accessories->execute([$_SESSION['user_id']]);
$accessories = $accessories->fetchAll(PDO::FETCH_ASSOC);

$others = $pdo->prepare('SELECT id, name, image_path FROM ' . TBL_CLOTHES . ' WHERE user_id = ? AND category = "other" ORDER BY name');
$others->execute([$_SESSION['user_id']]);
$others = $others->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $topId = $_POST['top_id'] ?: null;
        $bottomId = $_POST['bottom_id'] ?: null;
        $shoeId = $_POST['shoe_id'] ?: null;
        $accessoryId = $_POST['accessory_id'] ?: null;
        $otherId = $_POST['other_id'] ?: null;

        if (!$topId && !$bottomId && !$shoeId) {
            $error = 'Please select at least one item';
        } else {
            $selected = array_filter([$topId, $bottomId, $shoeId, $accessoryId, $otherId], fn($v) => $v !== null && $v !== '');
            if (!empty($selected)) {
                $placeholders = implode(',', array_fill(0, count($selected), '?'));
                $params = $selected;
                array_unshift($params, $_SESSION['user_id']);
                $check = $pdo->prepare("SELECT COUNT(*) FROM " . TBL_CLOTHES . " WHERE user_id = ? AND id IN ($placeholders)");
                $check->execute($params);
                $count = (int)$check->fetchColumn();
                if ($count !== count($selected)) {
                    $error = 'One or more selected items are invalid.';
                }
            }

            if (!$error) {
                $stmt = $pdo->prepare('INSERT INTO ' . TBL_OUTFITS . ' (user_id, top_id, bottom_id, shoe_id, accessory_id, other_id, title) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$_SESSION['user_id'], $topId, $bottomId, $shoeId, $accessoryId, $otherId, $title]);
                header('Location: list.php');
                exit;
            }
        }
    }
}

include __DIR__ . '/../templates/header.php';
?>

<div class="page-shell outfit-create-page">
    <div class="outfit-create-header">
        <div>
            <p class="upload-eyebrow">Outfit builder</p>
            <h1 class="upload-title">Create Outfit</h1>
            <p class="upload-subtext">Pair tops, bottoms, shoes, and accessories with a polished, layered preview.</p>
        </div>
        <div class="upload-meta">
            <span class="badge-outline"><i class="fa-solid fa-wand-magic-sparkles"></i> Guided</span>
            <span class="badge-outline"><i class="fa-solid fa-layer-group"></i> Glass UI</span>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="notice error"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <div class="outfit-create-grid">
        <div class="form-panel outfit-create-panel">
            <form method="POST" class="outfit-form">
                <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
                <div class="form-grid">
                    <div class="form-field">
                        <label for="title">Outfit Title <small>(optional)</small></label>
                        <input id="title" class="form-control" type="text" name="title" value="<?=htmlspecialchars($_POST['title'] ?? '')?>" placeholder="e.g., Weekend Layers">
                    </div>

                    <div class="form-field">
                        <label for="top_id"><i class="fa-solid fa-shirt"></i> Top</label>
                        <select id="top_id" name="top_id" class="form-control outfit-select" data-slot="top">
                            <option value="" data-name="None" data-image="">None</option>
                            <?php foreach ($tops as $item): ?>
                                <option value="<?=$item['id']?>" data-name="<?=htmlspecialchars($item['name'])?>" data-image="<?=htmlspecialchars($item['image_path'])?>"><?=htmlspecialchars($item['name'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="bottom_id"><i class="fa-solid fa-grip-lines"></i> Bottom</label>
                        <select id="bottom_id" name="bottom_id" class="form-control outfit-select" data-slot="bottom">
                            <option value="" data-name="None" data-image="">None</option>
                            <?php foreach ($bottoms as $item): ?>
                                <option value="<?=$item['id']?>" data-name="<?=htmlspecialchars($item['name'])?>" data-image="<?=htmlspecialchars($item['image_path'])?>"><?=htmlspecialchars($item['name'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="shoe_id"><i class="fa-solid fa-shoe-prints"></i> Shoes</label>
                        <select id="shoe_id" name="shoe_id" class="form-control outfit-select" data-slot="shoes">
                            <option value="" data-name="None" data-image="">None</option>
                            <?php foreach ($shoes as $item): ?>
                                <option value="<?=$item['id']?>" data-name="<?=htmlspecialchars($item['name'])?>" data-image="<?=htmlspecialchars($item['image_path'])?>"><?=htmlspecialchars($item['name'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="accessory_id"><i class="fa-solid fa-sparkles"></i> Accessory</label>
                        <select id="accessory_id" name="accessory_id" class="form-control outfit-select" data-slot="accessory">
                            <option value="" data-name="None" data-image="">None</option>
                            <?php foreach ($accessories as $item): ?>
                                <option value="<?=$item['id']?>" data-name="<?=htmlspecialchars($item['name'])?>" data-image="<?=htmlspecialchars($item['image_path'])?>"><?=htmlspecialchars($item['name'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="other_id"><i class="fa-solid fa-ellipsis"></i> Other</label>
                        <select id="other_id" name="other_id" class="form-control outfit-select" data-slot="other">
                            <option value="" data-name="None" data-image="">None</option>
                            <?php foreach ($others as $item): ?>
                                <option value="<?=$item['id']?>" data-name="<?=htmlspecialchars($item['name'])?>" data-image="<?=htmlspecialchars($item['image_path'])?>"><?=htmlspecialchars($item['name'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Outfit</button>
                    <a href="list.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <aside class="outfit-preview-card" aria-live="polite">
            <div class="preview-header">
                <h3 class="preview-title">Live Stack</h3>
                <span class="preview-badge"><i class="fa-solid fa-layer-group"></i> Layered</span>
            </div>
            <div class="outfit-preview-grid">
                <div class="preview-slot" data-slot="top">
                    <div class="slot-label">Top</div>
                    <div class="slot-frame">
                        <div class="slot-empty">Select a top</div>
                    </div>
                </div>
                <div class="preview-slot" data-slot="bottom">
                    <div class="slot-label">Bottom</div>
                    <div class="slot-frame">
                        <div class="slot-empty">Select a bottom</div>
                    </div>
                </div>
                <div class="preview-slot" data-slot="shoes">
                    <div class="slot-label">Shoes</div>
                    <div class="slot-frame">
                        <div class="slot-empty">Select shoes</div>
                    </div>
                </div>
                <div class="preview-slot" data-slot="accessory">
                    <div class="slot-label">Accessory</div>
                    <div class="slot-frame">
                        <div class="slot-empty">Optional accessory</div>
                    </div>
                </div>
                <div class="preview-slot" data-slot="other">
                    <div class="slot-label">Other</div>
                    <div class="slot-frame">
                        <div class="slot-empty">Optional other</div>
                    </div>
                </div>
            </div>
            <p class="preview-hint">Your selections stack here for a quick, elegant review.</p>
        </aside>
    </div>
</div>

<script>
(() => {
    const selects = document.querySelectorAll('.outfit-select');
    const slots = new Map();

    document.querySelectorAll('.preview-slot').forEach((slot) => {
        slots.set(slot.dataset.slot, slot.querySelector('.slot-frame'));
    });

    const renderSlot = (slotKey, option) => {
        const frame = slots.get(slotKey);
        if (!frame) return;

        const name = option?.dataset.name || 'None';
        const image = option?.dataset.image || '';
        frame.innerHTML = '';

        if (image) {
            const img = new Image();
            img.src = image;
            img.alt = name;
            img.loading = 'lazy';
            img.className = 'slot-image';
            frame.appendChild(img);
        } else {
            const div = document.createElement('div');
            div.className = 'slot-empty';
            div.textContent = name === 'None' ? 'Not set' : name;
            frame.appendChild(div);
        }
    };

    selects.forEach((select) => {
        const slotKey = select.dataset.slot;
        renderSlot(slotKey, select.selectedOptions[0]);
        select.addEventListener('change', (e) => {
            renderSlot(slotKey, e.target.selectedOptions[0]);
        });
    });
})();
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
