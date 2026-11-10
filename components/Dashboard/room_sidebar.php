<aside class="dashboard-sidebar" aria-label="Dashboard sidebar">
    <input class="sidebar-toggle sr-only" type="checkbox" id="sidebar-toggle" aria-label="Toggle sidebar" autocomplete="off">
    <div class="sidebar-rail">
        <label class="sidebar-toggle-button" for="sidebar-toggle" title="Open or close sidebar"><img src="<?= $base ?>assets/tup-logo.png" alt=""><span class="sr-only">Open or close sidebar</span></label>
        <span class="sidebar-rail-name" aria-hidden="true">TECHNOLOGICAL UNIVERSITY OF THE PHILIPPINES – VISAYAS</span>
    </div>
    <div class="sidebar-panel">
        <a class="sidebar-brand" href="<?= $base ?>room_index.php">
            <img class="sidebar-logo" src="<?= $base ?>assets/tup-logo.png" alt="">
            <span class="sidebar-brand-copy"><strong>TECHNOLOGICAL UNIVERSITY OF THE PHILIPPINES</strong><small>CLASSROOM MANAGEMENT</small></span>
        </a>
        <nav class="sidebar-nav" aria-label="Dashboard navigation">
            <a href="<?= $base ?>room_index.php" <?= $currentPage === 'room_index.php' ? 'aria-current="page"' : '' ?>>DASHBOARD</a>
            <?php if (($_SESSION['room_role'] ?? '') === 'admin'): ?>
                <a href="<?= $base ?>components/CRUDE/room_manage.php" <?= in_array($currentPage, ['room_manage.php', 'room_create.php', 'room_edit.php', 'room_delete.php'], true) ? 'aria-current="page"' : '' ?>>EDIT ROOMS</a>
                <a href="<?= $base ?>components/Dashboard/room_accounts.php" <?= in_array($currentPage, ['room_accounts.php', 'room_account_edit.php', 'room_account_create.php'], true) ? 'aria-current="page"' : '' ?>>ACCOUNTS</a>
            <?php endif; ?>
        </nav>
        <nav class="sidebar-nav sidebar-nav-bottom" aria-label="Account navigation">
            <a href="<?= $base ?>components/LOGIN/room_logout.php">LOG OUT</a>
        </nav>
        <p class="sidebar-note">CLASSROOM MANAGEMENT <span aria-hidden="true">◆</span></p>
    </div>
</aside>
