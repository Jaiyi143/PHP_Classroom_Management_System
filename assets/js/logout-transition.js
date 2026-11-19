import { startLogoScene } from './logo-loading-scene.js';

const logoutLink = document.querySelector('[data-room-chat-logout]');
const overlay = document.querySelector('[data-logout-overlay]');
const confirmation = overlay?.querySelector('[data-logout-confirm]');
const loading = overlay?.querySelector('[data-logout-loading]');
const cancelButton = overlay?.querySelector('[data-logout-cancel]');
const proceedButton = overlay?.querySelector('[data-logout-proceed]');
const stage = overlay?.querySelector('[data-logout-stage]');
const visual = overlay?.querySelector('[data-logout-logo-scene]');

if (logoutLink && overlay && confirmation && loading && cancelButton && proceedButton && stage && visual) {
    const background = [...document.querySelectorAll('.dashboard-sidebar, .dashboard-content, .room-chat')];
    let previousInert = [];
    let loadingStarted = false;
    let stopScene = null;
    const pause = (milliseconds) => new Promise((resolve) => window.setTimeout(resolve, milliseconds));

    function closeConfirmation() {
        if (loadingStarted) return;
        overlay.hidden = true;
        document.body.classList.remove('logout-confirm-open');
        previousInert.forEach(({ element, inert }) => { element.inert = inert; });
        logoutLink.focus();
    }

    logoutLink.addEventListener('click', (event) => {
        event.preventDefault();
        if (!overlay.hidden) return;
        previousInert = background.map((element) => ({ element, inert: element.inert }));
        background.forEach((element) => { element.inert = true; });
        overlay.hidden = false;
        document.body.classList.add('logout-confirm-open');
        cancelButton.focus();
    });

    cancelButton.addEventListener('click', closeConfirmation);
    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) closeConfirmation();
    });
    overlay.addEventListener('keydown', (event) => {
        if (loadingStarted) return;
        if (event.key === 'Escape') closeConfirmation();
        if (event.key !== 'Tab') return;
        if (event.shiftKey && document.activeElement === cancelButton) {
            event.preventDefault();
            proceedButton.focus();
        } else if (!event.shiftKey && document.activeElement === proceedButton) {
            event.preventDefault();
            cancelButton.focus();
        }
    });

    proceedButton.addEventListener('click', async () => {
        if (loadingStarted) return;
        loadingStarted = true;
        confirmation.hidden = true;
        loading.hidden = false;
        overlay.classList.add('is-loading');
        document.body.classList.add('is-logging-out');
        loading.focus();
        stopScene = startLogoScene(visual);

        await pause(650);
        stage.classList.add('is-changing');
        await pause(180);
        stage.textContent = 'Ending your session…';
        stage.classList.remove('is-changing');
        await pause(650);
        stage.classList.add('is-changing');
        await pause(180);
        stage.textContent = 'Back to sign in…';
        stage.classList.remove('is-changing');
        await pause(450);

        const chatKey = document.querySelector('[data-room-chat-key]')?.dataset.roomChatKey;
        if (chatKey) {
            try { sessionStorage.removeItem(chatKey); } catch { /* Storage may be disabled. */ }
        }
        window.location.assign(logoutLink.href);
    });

    window.addEventListener('pagehide', () => { stopScene?.(); });
}
