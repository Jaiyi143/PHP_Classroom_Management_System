<?php
/** @var string $authPrompt */
?>
<section class="auth-banner">
    <img class="auth-banner-image" src="../../assets/classroom-management-banner.png?v=2" alt="Classroom Management System">
    <div class="auth-banner-content">
        <h1>Classroom Management System</h1>
        <p><?= htmlspecialchars($authPrompt, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <img class="absolute bottom-0 left-1/4 z-[1] h-[min(38vh,350px)] w-auto -translate-x-1/2 object-contain max-[900px]:left-1/2 max-[900px]:h-[170px]" src="../../assets/jerjer.png" alt="Jerjer mascot holding a phone">
</section>
