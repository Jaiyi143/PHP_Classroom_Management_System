import * as THREE from 'three';

export function startLogoScene(visual) {
    const fallback = visual.querySelector('.auth-loading-logo-fallback');
    fallback.hidden = false;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return null;

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
