<?php
require_once __DIR__ . '/../config.php';
requireLogin();

// Check if user is admin
$stmt = $pdo->prepare('SELECT role FROM '. TBL_USERS .' WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$userRole = $stmt->fetchColumn();

if ($userRole !== 'admin') {
    header('Location: ' . url_path('src/dashboard.php'));
    exit;
}

// Fetch all '. TBL_USERS .' with their stats
$usersStmt = $pdo->query('
    SELECT 
        u.id,
        u.name,
        u.email,
        u.role,
        u.created_at,
        COUNT(DISTINCT c.id) AS clothes_count,
        COUNT(DISTINCT o.id) AS outfits_count
    FROM '. TBL_USERS .' u
    LEFT JOIN '. TBL_CLOTHES .' c ON c.user_id = u.id
    LEFT JOIN '. TBL_OUTFITS .' o ON o.user_id = u.id
    GROUP BY u.id, u.name, u.email, u.role, u.created_at
    ORDER BY u.created_at DESC
');
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../templates/header.php';
?>

<style>
.admin-header {
    background: linear-gradient(135deg, rgba(91, 75, 255, 0.1), rgba(32, 216, 210, 0.1));
    border: 1px solid rgba(91, 75, 255, 0.3);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.admin-header h1 {
    font-size: 2rem;
    font-weight: 800;
    background: linear-gradient(135deg, #5b4bff, #20d8d2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 0.5rem;
}

.users-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
}

.user-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
}

.user-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
}

.user-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.user-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
}

.user-info h3 {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.user-email {
    font-size: 0.85rem;
    color: rgba(226, 232, 240, 0.7);
}

.user-role {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 0.5rem;
}

.role-admin {
    background: linear-gradient(135deg, #5b4bff, #3c88ff);
    color: white;
}

.role-user {
    background: rgba(255, 255, 255, 0.1);
    color: rgba(226, 232, 240, 0.9);
}

.user-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-box {
    text-align: center;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #5b4bff, #20d8d2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-label {
    font-size: 0.75rem;
    color: rgba(226, 232, 240, 0.7);
    margin-top: 0.25rem;
}

.user-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.action-btn {
    flex: 1;
    padding: 0.5rem;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.05);
    color: white;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.3);
}

.action-btn.danger:hover {
    background: rgba(239, 68, 68, 0.2);
    border-color: rgba(239, 68, 68, 0.5);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 10px;
    color: white;
    text-decoration: none;
    margin-bottom: 2rem;
    transition: all 0.2s;
}

.back-link:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(-4px);
}
</style>

<a href="<?= url_path('src/dashboard.php') ?>" class="back-link">
    <i class="fas fa-arrow-left"></i> Back to Dashboard
</a>

<div class="admin-header">
    <h1><i class="fas fa-users-cog"></i> User Management</h1>
    <p style="color: rgba(226, 232, 240, 0.8); margin: 0;">Manage all users and their wardrobe statistics</p>
</div>

<div class="users-grid">
    <?php foreach ($users as $user): ?>
        <div class="user-card">
            <div class="user-header">
                <div class="user-avatar">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div class="user-info">
                    <h3><?= h($user['name']) ?></h3>
                    <div class="user-email"><?= h($user['email']) ?></div>
                    <span class="user-role role-<?= h($user['role']) ?>">
                        <?= $user['role'] === 'admin' ? '<i class="fas fa-crown"></i>' : '<i class="fas fa-user"></i>' ?>
                        <?= ucfirst(h($user['role'])) ?>
                    </span>
                </div>
            </div>
            
            <div class="user-stats">
                <div class="stat-box">
                    <div class="stat-number"><?= $user['clothes_count'] ?></div>
                    <div class="stat-label">Clothing Items</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= $user['outfits_count'] ?></div>
                    <div class="stat-label">Outfits</div>
                </div>
            </div>

            <div class="user-actions">
                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                    <button class="action-btn" onclick="toggleRole(<?= $user['id'] ?>, '<?= $user['role'] ?>')">
                        <i class="fas fa-user-shield"></i> 
                        <?= $user['role'] === 'admin' ? 'Remove Admin' : 'Make Admin' ?>
                    </button>
                    <button class="action-btn danger" onclick="deleteUser(<?= $user['id'] ?>, '<?= h($user['name']) ?>')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                <?php else: ?>
                    <div style="color: rgba(226, 232, 240, 0.5); font-size: 0.85rem; text-align: center; padding: 0.5rem;">
                        <i class="fas fa-info-circle"></i> Current User
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
function toggleRole(userId, currentRole) {
    const action = currentRole === 'admin' ? 'remove admin privileges from' : 'grant admin privileges to';
    if (!confirm(`Are you sure you want to ${action} this user?`)) return;
    
    fetch('<?= url_path('src/admin/toggle_role.php') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to update user role'));
        }
    })
    .catch(err => alert('Error: ' + err.message));
}

function deleteUser(userId, userName) {
    if (!confirm(`Are you sure you want to delete user "${userName}"? This will permanently delete all their clothes, outfits, and data. This action cannot be undone.`)) return;
    
    const confirmDelete = prompt(`Type "DELETE" to confirm deletion of ${userName}:`);
    if (confirmDelete !== 'DELETE') {
        alert('Deletion cancelled.');
        return;
    }
    
    fetch('<?= url_path('src/admin/delete_user.php') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to delete user'));
        }
    })
    .catch(err => alert('Error: ' + err.message));
}
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
