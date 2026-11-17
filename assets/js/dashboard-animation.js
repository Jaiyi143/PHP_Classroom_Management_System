import * as THREE from 'three';

const motionElements = document.querySelectorAll([
    '.dashboard-topline',
    '.dashboard-profile',
    '.dashboard-search',
    '.dashboard-summary-card',
    '.dashboard-filters',
    '.room-filter-link',
    '.dashboard-gallery > h2',
    '.dashboard-gallery-toolbar',
    '.room-card',
    '.account-intro',
    '.account-card',
    '.dashboard-editor',
    '.dashboard-edit-modal',
].join(','));

const motionStates = Array.from(motionElements, (element) => {
    const state = { element, visible: false, opacity: 0, y: 22, scale: 0.97, rx: 0, ry: 0, hover: false, pointerX: 0, pointerY: 0 };
    element.style.opacity = '0';
    element.style.transform = 'translate3d(0, 22px, 0) scale(.97)';

    if (element.matches('.room-card, .dashboard-summary-card, .dashboard-profile, .room-filter-link, .account-list .account-card')) {
        element.addEventListener('pointerenter', () => { state.hover = true; });
        element.addEventListener('pointermove', (event) => {
            const bounds = element.getBoundingClientRect();
            state.pointerX = ((event.clientX - bounds.left) / bounds.width - 0.5) * 5;
            state.pointerY = (0.5 - (event.clientY - bounds.top) / bounds.height) * 5;
        });
        element.addEventListener('pointerleave', () => {
            state.hover = false;
            state.pointerX = 0;
            state.pointerY = 0;
        });
    }
    return state;
});

const observer = new IntersectionObserver((entries) => {
    for (const entry of entries) {
        if (entry.isIntersecting) {
            const state = motionStates.find((item) => item.element === entry.target);
            if (state) state.visible = true;
            observer.unobserve(entry.target);
        }
    }
}, { threshold: 0.08 });
motionStates.forEach(({ element }) => observer.observe(element));

const counters = Array.from(document.querySelectorAll('.dashboard-summary-card strong'), (element) => {
    const target = Number(element.textContent.trim());
    if (!Number.isFinite(target)) return null;
    element.textContent = '0';
    return { element, target, value: 0 };
}).filter(Boolean);

let modalPanel = null;
let modalProgress = 0;
function syncRoomModal() {
    const nextPanel = document.querySelector('.room-modal:target .room-modal-panel');
    if (modalPanel && modalPanel !== nextPanel) {
        modalPanel.style.removeProperty('opacity');
        modalPanel.style.removeProperty('transform');
    }
    modalPanel = nextPanel;
    modalProgress = 0;
    if (modalPanel) {
        modalPanel.style.opacity = '0';
        modalPanel.style.transform = 'translateY(20px) scale(.96)';
    }
}
window.addEventListener('hashchange', syncRoomModal);
syncRoomModal();

const editDialog = document.querySelector('.dashboard-edit-modal');
if (editDialog) {
    for (const background of document.querySelectorAll('.dashboard-sidebar, .dashboard-topline, .room-chat')) {
        background.inert = true;
    }
    const closeLink = editDialog.querySelector('.room-modal-close');
    closeLink?.focus();
    editDialog.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeLink?.click();
            return;
        }
        if (event.key !== 'Tab') return;
        const focusable = [...editDialog.querySelectorAll('a[href], button:not([disabled]), input:not([type="hidden"]), select')];
        const first = focusable[0];
        const last = focusable.at(-1);
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    });
}

let frameId = null;
let previousTime = 0;
function animateElements(time) {
    const delta = Math.min((time - (previousTime || time)) / 1000, 0.05);
    previousTime = time;

    for (const state of motionStates) {
        state.opacity = THREE.MathUtils.damp(state.opacity, state.visible ? 1 : 0, 6, delta);
        state.y = THREE.MathUtils.damp(state.y, state.visible ? (state.hover ? -4 : 0) : 22, 7, delta);
        state.scale = THREE.MathUtils.damp(state.scale, state.visible ? (state.hover ? 1.015 : 1) : 0.97, 7, delta);
        state.rx = THREE.MathUtils.damp(state.rx, state.hover ? state.pointerY : 0, 9, delta);
        state.ry = THREE.MathUtils.damp(state.ry, state.hover ? state.pointerX : 0, 9, delta);
        state.element.style.opacity = state.opacity.toFixed(3);
        state.element.style.transform = `perspective(900px) translate3d(0, ${state.y.toFixed(2)}px, 0) scale(${state.scale.toFixed(4)}) rotateX(${state.rx.toFixed(2)}deg) rotateY(${state.ry.toFixed(2)}deg)`;
    }

    for (const counter of counters) {
        counter.value = THREE.MathUtils.damp(counter.value, counter.target, 4, delta);
        counter.element.textContent = String(Math.round(counter.value));
    }

    if (modalPanel) {
        modalProgress = THREE.MathUtils.damp(modalProgress, 1, 9, delta);
        modalPanel.style.opacity = modalProgress.toFixed(3);
        modalPanel.style.transform = `translateY(${((1 - modalProgress) * 20).toFixed(2)}px) scale(${(0.96 + modalProgress * 0.04).toFixed(4)})`;
    }

    frameId = window.requestAnimationFrame(animateElements);
}

function onVisibilityChange() {
    if (document.hidden) {
        window.cancelAnimationFrame(frameId);
        frameId = null;
    } else if (frameId === null) {
        previousTime = 0;
        frameId = window.requestAnimationFrame(animateElements);
    }
}
document.addEventListener('visibilitychange', onVisibilityChange);
onVisibilityChange();
