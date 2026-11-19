<div class="logout-overlay" data-logout-overlay hidden>
    <section class="logout-confirm" role="dialog" aria-modal="true" aria-labelledby="logout-heading" data-logout-confirm>
        <span class="room-modal-kicker">TUP VISAYAS · CAMPUS ROOMS</span>
        <h2 id="logout-heading">Log out?</h2>
        <p>You will return to the sign-in page.</p>
        <div class="logout-confirm-actions">
            <button type="button" data-logout-cancel>Stay here</button>
            <button type="button" data-logout-proceed>Log out</button>
        </div>
    </section>
    <section class="logout-loading" aria-label="Signing out" tabindex="-1" data-logout-loading hidden>
        <div class="auth-loading-visual" data-logo-src="<?= e($base) ?>assets/tup-logo.png" data-logout-logo-scene>
            <img class="auth-loading-logo-fallback" src="<?= e($base) ?>assets/tup-logo.png" alt="">
        </div>
        <img class="auth-loading-jerjer" src="<?= e($base) ?>assets/jerjer.png" alt="" aria-hidden="true">
        <p class="auth-loading-caption">TUP VISAYAS · CAMPUS ROOMS</p>
        <p class="auth-loading-message" role="status" aria-live="polite" data-logout-stage>Signing out…</p>
        <div class="auth-loading-progress" aria-hidden="true"><span></span><span></span><span></span></div>
    </section>
</div>
