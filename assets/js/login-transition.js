import { startLogoScene } from './logo-loading-scene.js';

const shell = document.querySelector('[data-auth-shell]');
const panel = shell?.querySelector('.auth-panel');
const form = panel?.querySelector('form');
const button = form?.querySelector('.auth-submit-button');
const error = panel?.querySelector('.auth-form-error');
const loadingPanel = shell?.querySelector('.auth-loading-panel');
const visual = loadingPanel?.querySelector('[data-auth-loading-scene]');
const stage = loadingPanel?.querySelector('[data-auth-stage]');

if (shell && panel && form && button && error && loadingPanel && visual && stage) {
    let isLoading = false;
    let attempt = 0;
    let controller = null;
    let stopScene = null;

    const pause = (milliseconds) => new Promise((resolve) => window.setTimeout(resolve, milliseconds));

    function setReaction(reaction) {
        loadingPanel.dataset.reaction = reaction;
        document.dispatchEvent(new CustomEvent('auth:mascot', { detail: { reaction } }));
    }

    async function showStage(message) {
        stage.classList.add('is-changing');
        await pause(180);
        stage.textContent = message;
        stage.classList.remove('is-changing');
    }

    function restoreForm(message) {
        stopScene?.();
        stopScene = null;
        document.body.classList.remove('auth-is-loading');
        shell.classList.remove('is-authenticating');
        panel.removeAttribute('aria-hidden');
        panel.inert = false;
        loadingPanel.setAttribute('aria-hidden', 'true');
        form.removeAttribute('aria-busy');
        button.disabled = false;
        isLoading = false;

        if (message) {
            error.textContent = message;
            error.hidden = false;
            form.querySelector('#username')?.focus();
        }
    }

    async function authenticate() {
        if (isLoading) return;
        isLoading = true;
        const currentAttempt = ++attempt;
        error.hidden = true;

        const formData = new FormData(form);
        controller = new AbortController();
        const request = fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
            signal: controller.signal,
        }).then(async (response) => ({ ok: response.ok, data: await response.json() })).catch(() => null);

        form.setAttribute('aria-busy', 'true');
        button.disabled = true;
        panel.inert = true;
        panel.setAttribute('aria-hidden', 'true');
        loadingPanel.removeAttribute('aria-hidden');
        loadingPanel.focus();
        stage.textContent = 'Authenticating…';
        shell.classList.add('is-authenticating');
        document.body.classList.add('auth-is-loading');
        setReaction('authenticating');
        stopScene = startLogoScene(visual);

        await pause(700);
        if (currentAttempt !== attempt) return;
        await showStage('Checking your account…');
        setReaction('checking');
        const result = await request;
        await pause(450);
        if (currentAttempt !== attempt) return;

        if (result?.ok && result.data?.success && typeof result.data.redirect === 'string') {
            await showStage('Opening your dashboard…');
            setReaction('success');
            await pause(600);
            if (currentAttempt !== attempt) return;
            window.location.assign(new URL(result.data.redirect, form.action).href);
            return;
        }

        await showStage(result ? 'Could not sign in' : 'Connection problem');
        setReaction('failure');
        await pause(500);
        if (currentAttempt !== attempt) return;
        restoreForm(result
            ? 'Sign in failed. Check your username' + (form.elements.role.value === 'admin' ? ' and password.' : '.')
            : 'Could not connect. Please try again.');
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        void authenticate();
    });

    window.addEventListener('pagehide', () => {
        attempt++;
        controller?.abort();
        stopScene?.();
        stopScene = null;
    });
    window.addEventListener('pageshow', (event) => {
        if (event.persisted && isLoading) {
            restoreForm();
            setReaction('reset');
        }
    });
}
