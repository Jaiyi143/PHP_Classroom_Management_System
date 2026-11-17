import * as THREE from 'three';

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

    function startLogoScene() {
        const fallback = visual.querySelector('.auth-loading-logo-fallback');
        fallback.hidden = false;

        let renderer;
        try {
            renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true, powerPreference: 'low-power' });
        } catch {
            return null;
        }

        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
        renderer.setClearColor(0x000000, 0);
        renderer.setSize(visual.clientWidth, visual.clientHeight);
        renderer.domElement.setAttribute('aria-hidden', 'true');
        visual.appendChild(renderer.domElement);

        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(45, visual.clientWidth / visual.clientHeight, 0.1, 20);
        camera.position.z = 5;

        const group = new THREE.Group();
        scene.add(group);

        const ringGeometry = new THREE.TorusGeometry(1.35, 0.025, 8, 96);
        const outerRingGeometry = new THREE.TorusGeometry(1.48, 0.012, 8, 96);
        const ringMaterial = new THREE.MeshBasicMaterial({ color: 0x9f111b, transparent: true, opacity: 0.75 });
        const outerRingMaterial = new THREE.MeshBasicMaterial({ color: 0xe5a7ac, transparent: true, opacity: 0.7 });
        const ring = new THREE.Mesh(ringGeometry, ringMaterial);
        const outerRing = new THREE.Mesh(outerRingGeometry, outerRingMaterial);
        outerRing.rotation.x = Math.PI / 3;
        group.add(ring, outerRing);

        const logoGeometry = new THREE.PlaneGeometry(1.9, 1.9);
        const logoMaterial = new THREE.MeshBasicMaterial({ transparent: true, side: THREE.DoubleSide, depthWrite: false });
        const logo = new THREE.Mesh(logoGeometry, logoMaterial);
        logo.visible = false;
        logo.position.z = 0.12;
        group.add(logo);

        let disposed = false;
        const texture = new THREE.TextureLoader().load(visual.dataset.logoSrc, () => {
            if (disposed) return;
            texture.colorSpace = THREE.SRGBColorSpace;
            logoMaterial.map = texture;
            logoMaterial.needsUpdate = true;
            logo.visible = true;
            fallback.hidden = true;
        });

        renderer.setAnimationLoop((time) => {
            ring.rotation.z = time * 0.0012;
            outerRing.rotation.y = time * 0.0015;
            group.rotation.y = Math.sin(time * 0.001) * 0.16;
            logo.rotation.y = Math.sin(time * 0.001) * 0.08;
            renderer.render(scene, camera);
        });

        return () => {
            disposed = true;
            renderer.setAnimationLoop(null);
            texture.dispose();
            logoGeometry.dispose();
            logoMaterial.dispose();
            ringGeometry.dispose();
            outerRingGeometry.dispose();
            ringMaterial.dispose();
            outerRingMaterial.dispose();
            renderer.dispose();
            renderer.domElement.remove();
            fallback.hidden = false;
        };
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
        stopScene = startLogoScene();

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
