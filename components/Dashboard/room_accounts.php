<?php
require __DIR__ . '/../../room_helpers.php';
requireLogin();
require __DIR__ . '/../../room_config.php';
requireAdminTeacher($pdo);

$currentUserId = positiveId($_SESSION['room_user_id'] ?? null);
$actorStmt = $pdo->prepare('SELECT username, role FROM users WHERE user_id = ?');
$actorStmt->execute([$currentUserId]);
$actor = $actorStmt->fetch();
if (!$actor) {
    header('Location: ../LOGIN/room_logout.php');
    exit;
}

$_SESSION['room_role'] = $actor['role'];
$_SESSION['room_username'] = $actor['username'];

$accounts = $pdo->query("SELECT user_id, username, email, role FROM users ORDER BY CASE WHEN role = 'admin' THEN 0 ELSE 1 END, user_id")->fetchAll();

ob_start();
?>
<div class="account-intro">
    <p>Manage account usernames and passwords.</p>
    <div class="account-intro-actions">
        <span><?= count($accounts) ?> accounts</span>
        <a class="account-edit-link account-add-link" href="room_account_create.php">Add account</a>
    </div>
</div>
<?php if (isset($_GET['updated'])): ?><p class="auth-success" role="status">Account updated successfully.</p><?php endif; ?>
<?php if (isset($_GET['created'])): ?><p class="auth-success" role="status">Account added successfully.</p><?php endif; ?>
<div class="account-grid account-list">
    <?php foreach ($accounts as $account): ?>
        <?php $accountId = (int) $account['user_id']; ?>
        <section class="account-card" aria-labelledby="account-title-<?= $accountId ?>">
            <div class="account-card-heading">
                <span class="account-avatar" aria-hidden="true"><?= e(strtoupper(substr($account['username'], 0, 1))) ?></span>
                <div>
                    <span class="account-eyebrow"><?= e(strtoupper(roomRoleLabel($account['role']))) ?> ACCOUNT</span>
                    <h2 id="account-title-<?= $accountId ?>"><?= e($account['username']) ?></h2>
                    <?php if ($account['email']): ?><p><?= e($account['email']) ?></p><?php endif; ?>
                </div>
                <?php if ($accountId === $currentUserId): ?><span class="account-self">You</span><?php endif; ?>
            </div>
            <a class="account-edit-link" href="room_account_edit.php?id=<?= $accountId ?>">Edit account</a>
        </section>
    <?php endforeach; ?>
</div>
<?php page('Manage Accounts', ob_get_clean());
