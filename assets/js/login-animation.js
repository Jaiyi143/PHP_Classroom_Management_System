import * as THREE from 'three';

const greeting = document.querySelector('.auth-mascot-greeting');
const greetingMessage = greeting?.querySelector('[data-auth-mascot-message]');

if (greeting && greetingMessage) {
    const mascot = greeting.closest('.auth-banner-mascot');
    const messages = [
        'Hello! Let’s find your room.',
        'Sign in to explore campus rooms and see what’s available.',
    ];
    let currentMessage = 0;
    let intervalId = null;
    let swapId = null;
    let reactionResetId = null;

    function changeMessage(message) {
        window.clearTimeout(swapId);
        greeting.classList.add('is-changing');
        swapId = window.setTimeout(() => {
            greetingMessage.textContent = message;
            greeting.setAttribute('aria-label', `Jerjer says: ${message}`);
            greeting.classList.remove('is-changing');
            swapId = null;
        }, 320);
    }

    function showNextMessage() {
        if (document.hidden || document.body.classList.contains('auth-is-loading')) return;
        currentMessage = (currentMessage + 1) % messages.length;
        changeMessage(messages[currentMessage]);
    }

    function startMessages() {
        if (intervalId === null) intervalId = window.setInterval(showNextMessage, 4200);
    }

    function stopMessages() {
        window.clearInterval(intervalId);
        window.clearTimeout(swapId);
        intervalId = null;
        swapId = null;
        greeting.classList.remove('is-changing');
    }

    function restoreGreeting() {
        mascot?.removeAttribute('data-reaction');
        currentMessage = 0;
        changeMessage(messages[0]);
        if (!greeting.matches(':hover')) startMessages();
    }

    document.addEventListener('auth:mascot', (event) => {
        const reaction = event.detail?.reaction;
        const reactions = {
            authenticating: 'You’ve got this! I’m checking.',
            checking: 'Looking up your account…',
            success: 'Welcome back! Let’s explore rooms.',
            failure: 'That didn’t work. Let’s try again.',
        };

        window.clearTimeout(reactionResetId);
        reactionResetId = null;
        stopMessages();
        if (reaction === 'reset') {
            restoreGreeting();
            return;
        }
        if (!reactions[reaction]) return;

        mascot.dataset.reaction = reaction;
        changeMessage(reactions[reaction]);
        if (reaction === 'failure') reactionResetId = window.setTimeout(restoreGreeting, 2600);
    });

    greeting.addEventListener('mouseenter', () => {
        if (!mascot?.hasAttribute('data-reaction')) stopMessages();
    });
    greeting.addEventListener('mouseleave', () => {
        if (!mascot?.hasAttribute('data-reaction')) startMessages();
    });
    window.addEventListener('pagehide', () => {
        window.clearTimeout(reactionResetId);
        stopMessages();
    });
    window.addEventListener('pageshow', () => {
        if (!mascot?.hasAttribute('data-reaction')) startMessages();
    });
    startMessages();
}

const container = document.querySelector('[data-auth-three-scene]');

if (container) {
    let activeScene = null;

    function start() {
        if (activeScene) return;

        const width = container.clientWidth;
        const height = container.clientHeight;
        if (!width || !height) return;

        let renderer;
        try {
            renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true, powerPreference: 'low-power' });
        } catch {
            // The banner image remains visible when WebGL is unavailable.
            return;
        }

        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
        renderer.setClearColor(0x000000, 0);
        container.appendChild(renderer.domElement);

        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 100);
        camera.position.z = 12;

        const count = 65;
        const positions = new Float32Array(count * 3);
        const particleGeometry = new THREE.BufferGeometry();
        particleGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        const particleMaterial = new THREE.PointsMaterial({
            color: 0xffe8d7,
            size: 2.5,
            sizeAttenuation: false,
            transparent: true,
            opacity: 0.62,
            depthWrite: false,
        });
        const particles = new THREE.Points(particleGeometry, particleMaterial);
        scene.add(particles);

        function resize() {
            const nextWidth = container.clientWidth;
            const nextHeight = container.clientHeight;
            if (!nextWidth || !nextHeight) return;

            camera.aspect = nextWidth / nextHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(nextWidth, nextHeight);

            const viewHeight = 2 * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)) * camera.position.z;
            const viewWidth = viewHeight * camera.aspect;
            for (let index = 0; index < count; index++) {
                positions[index * 3] = (Math.random() - 0.5) * viewWidth * 0.95;
                positions[index * 3 + 1] = (Math.random() - 0.5) * viewHeight * 0.9;
                positions[index * 3 + 2] = (Math.random() - 0.5) * 3;
            }
            particleGeometry.attributes.position.needsUpdate = true;
        }

        function animate(time) {
            const loading = document.body.classList.contains('auth-is-loading');
            particles.rotation.y = Math.sin(time * 0.00006) * 0.1;
            particleMaterial.opacity = loading ? 0.8 : 0.62;
            renderer.render(scene, camera);
        }

        function onVisibilityChange() {
            renderer.setAnimationLoop(document.hidden ? null : animate);
        }

        const resizeObserver = new ResizeObserver(resize);
        resizeObserver.observe(container);
        document.addEventListener('visibilitychange', onVisibilityChange);
        resize();
        onVisibilityChange();

        activeScene = () => {
            renderer.setAnimationLoop(null);
            resizeObserver.disconnect();
            document.removeEventListener('visibilitychange', onVisibilityChange);
            particleGeometry.dispose();
            particleMaterial.dispose();
            renderer.dispose();
            renderer.domElement.remove();
            activeScene = null;
        };
    }

    window.addEventListener('pagehide', () => activeScene?.());
    window.addEventListener('pageshow', start);
    start();
}
