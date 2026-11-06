<?php
require __DIR__ . '/../../room_helpers.php';
requireLogin();
require __DIR__ . '/../../room_config.php';

$currentUserId = positiveId($_SESSION['room_user_id'] ?? null);
$actorStmt = $pdo->prepare('SELECT username, role FROM users WHERE user_id = ?');
$actorStmt->execute([$currentUserId]);
$actor = $actorStmt->fetch();
if (!$actor) {
    header('Location: ../LOGIN/room_logout.php');
    exit;
}

$isAdmin = $actor['role'] === 'admin';
$_SESSION['room_role'] = $actor['role'];
$_SESSION['room_username'] = $actor['username'];

if ($isAdmin) {
    $accounts = $pdo->query("SELECT user_id, username, email, role FROM users ORDER BY CASE WHEN role = 'admin' THEN 0 ELSE 1 END, user_id")->fetchAll();
} else {
    $accountsStmt = $pdo->prepare('SELECT user_id, username, email, role FROM users WHERE user_id = ?');
    $accountsStmt->execute([$currentUserId]);
    $accounts = $accountsStmt->fetchAll();
}

ob_start();
?>
<div class="account-intro">
    <p><?= $isAdmin ? 'Manage account usernames and passwords.' : 'Manage your username and password.' ?></p>
    <div class="account-intro-actions">
        <span><?= $isAdmin ? count($accounts) . ' accounts' : 'Your account' ?></span>
        <?php if ($isAdmin): ?><a class="account-edit-link account-add-link" href="room_account_create.php">Add account</a><?php endif; ?>
    </div>
</div>
<?php if (isset($_GET['updated'])): ?><p class="auth-success" role="status">Account updated successfully.</p><?php endif; ?>
<?php if (isset($_GET['created'])): ?><p class="auth-success" role="status">Account added successfully.</p><?php endif; ?>
<div class="account-grid">
    <?php foreach ($accounts as $account): ?>
        <?php $accountId = (int) $account['user_id']; ?>
        <section class="account-card" aria-labelledby="account-title-<?= $accountId ?>">
            <div class="account-card-heading">
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
<?php page($isAdmin ? 'Manage Accounts' : 'My Account', ob_get_clean());
