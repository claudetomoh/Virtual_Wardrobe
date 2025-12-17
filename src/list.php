<?php
include __DIR__ . '/../templates/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2><i class="fa-solid fa-tshirt" style="margin-right:8px;color:var(--ow-mint)"></i> My Outfits</h2>
        <a href="create.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create New Outfit</a>
    </div>
    
    <?php if (empty($outfits)): ?>
        <div style="background: rgba(255,255,255,0.95); padding: 4rem; border-radius: 20px; text-align: center;">
            <span class="empty-icon icon-outfit" aria-hidden="true"></span>
            <h3 style="font-size: 2rem; margin-bottom: 1rem; color: var(--dark);">No Outfits Yet</h3>
            <p style="color: #666; margin-bottom: 2rem; font-size: 1.2rem;">Start creating outfit combinations from your wardrobe!</p>
            <a href="create.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create First Outfit</a>
        </div>
    <?php else: ?>
        <div style="background: rgba(255,255,255,0.95); padding: 1.5rem; border-radius: 20px; margin-bottom: 1rem; box-shadow: var(--shadow);">
            <p style="color: #666; font-size: 1.1rem; margin: 0;">
                You have <strong><?=count($outfits)?></strong> saved outfit<?=count($outfits) != 1 ? 's' : ''?>
            </p>
        </div>
        
        <div class="outfits-list">
            <?php foreach ($outfits as $index => $outfit): ?>
                <div class="outfit-card" style="animation-delay: <?=$index * 0.1?>s;">
                    <?php if ($outfit['title']): ?>
                        <h3><i class="fa-solid fa-sparkles" style="margin-right:0.5rem;color:var(--ow-mint)"></i> <?=htmlspecialchars($outfit['title'])?></h3>
                    <?php else: ?>
                        <h3><i class="fa-solid fa-tshirt" style="margin-right:0.5rem;color:var(--ow-mint)"></i> Outfit #<?=$outfit['id']?></h3>
                    <?php endif; ?>
                    
                    <div style="color: #999; font-size: 0.9rem; margin-bottom: 1rem;">
                        Created: <?=date('M d, Y', strtotime($outfit['created_at']))?>
                    </div>
                    
                    <div class="outfit-items">
                        <?php
                        $itemIds = array_filter([$outfit['top_id'], $outfit['bottom_id'], $outfit['shoe_id'], $outfit['accessory_id']]);
                        if (!empty($itemIds)) {
                            $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
                            $stmt = $pdo->prepare("SELECT name, image_path, category FROM '. TBL_CLOTHES .' WHERE id IN ($placeholders)");
                            $stmt->execute($itemIds);
                            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($items as $item):
                        ?>
                            <div class="outfit-item">
                                <img src="<?=htmlspecialchars($item['image_path'])?>" alt="<?=htmlspecialchars($item['name'])?>" title="Click to enlarge">
                                <p><?=htmlspecialchars($item['name'])?></p>
                                <small><?=htmlspecialchars(ucfirst($item['category']))?></small>
                            </div>
                        <?php 
                            endforeach;
                        }
                        ?>
                    </div>
                    
                    <form method="POST" action="delete.php" onsubmit="return confirm('⚠️ Delete this outfit?');">
                        <input type="hidden" name="id" value="<?=$outfit['id']?>">
                        <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Delete Outfit</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="<?=h(url_path('public/js/main.js'))?>"></script>
<?php include __DIR__ . '/../templates/footer.php'; ?>