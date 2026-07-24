import { animate, stagger, inView } from "motion";
import * as THREE from "three";

const EASE_OUT_EXPO = [0.16, 1, 0.3, 1] as const;

/* ---------------------------------------------------------------------------
 * Fondo 3D — campo de partículas flotantes con parallax de mouse.
 * Sustituye el fondo estático (img/fondo16.png) del index legacy.
 * ------------------------------------------------------------------------- */
function initBackground(): void {
    const canvas = document.getElementById("bg-canvas") as HTMLCanvasElement | null;
    if (!canvas) return;

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(window.innerWidth, window.innerHeight);

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.z = 22;

    const PARTICLE_COUNT = 420;
    const positions = new Float32Array(PARTICLE_COUNT * 3);
    const speeds = new Float32Array(PARTICLE_COUNT);

    for (let i = 0; i < PARTICLE_COUNT; i++) {
        positions[i * 3] = (Math.random() - 0.5) * 60;
        positions[i * 3 + 1] = (Math.random() - 0.5) * 36;
        positions[i * 3 + 2] = (Math.random() - 0.5) * 30;
        speeds[i] = 0.02 + Math.random() * 0.05;
    }

    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute("position", new THREE.BufferAttribute(positions, 3));

    const material = new THREE.PointsMaterial({
        color: 0xff8a3d,
        size: 0.11,
        transparent: true,
        opacity: 0.55,
        sizeAttenuation: true,
    });

    const points = new THREE.Points(geometry, material);
    scene.add(points);

    // Un segundo campo, más tenue y gris, para dar profundidad.
    const dimGeometry = geometry.clone();
    const dimMaterial = new THREE.PointsMaterial({
        color: 0x8a8d97,
        size: 0.06,
        transparent: true,
        opacity: 0.35,
    });
    const dimPoints = new THREE.Points(dimGeometry, dimMaterial);
    dimPoints.position.z = -12;
    scene.add(dimPoints);

    let mouseX = 0;
    let mouseY = 0;
    window.addEventListener("pointermove", (e) => {
        mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
        mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
    });

    window.addEventListener("resize", () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });

    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    let frameId: number;
    const clock = new THREE.Clock();
    function tick() {
        frameId = requestAnimationFrame(tick);
        const t = clock.getElapsedTime();

        const posAttr = points.geometry.getAttribute("position") as THREE.BufferAttribute;
        for (let i = 0; i < PARTICLE_COUNT; i++) {
            const y = posAttr.getY(i);
            const newY = y + speeds[i] * 0.05;
            posAttr.setY(i, newY > 18 ? -18 : newY);
        }
        posAttr.needsUpdate = true;

        points.rotation.y = t * 0.02;
        dimPoints.rotation.y = -t * 0.015;

        camera.position.x += (mouseX * 2 - camera.position.x) * 0.03;
        camera.position.y += (-mouseY * 1.2 - camera.position.y) * 0.03;
        camera.lookAt(0, 0, 0);

        renderer.render(scene, camera);
    }

    if (!prefersReducedMotion) {
        tick();
    } else {
        renderer.render(scene, camera);
    }

    document.addEventListener("visibilitychange", () => {
        if (document.hidden) cancelAnimationFrame(frameId);
        else if (!prefersReducedMotion) tick();
    });
}

/* ---------------------------------------------------------------------------
 * Animaciones de entrada — fade-in + slide-up con stagger sobre la tarjeta
 * de login y sus campos. Hover glow en el botón principal.
 * ------------------------------------------------------------------------- */
function initEntranceAnimations(): void {
    animate(
        ".brand",
        { opacity: [0, 1], transform: ["translateY(-8px)", "translateY(0px)"] },
        { duration: 0.6, ease: EASE_OUT_EXPO }
    );

    animate(
        ".auth-card",
        { opacity: [0, 1], transform: ["translateY(18px) scale(0.98)", "translateY(0px) scale(1)"] },
        { duration: 0.7, delay: 0.15, ease: EASE_OUT_EXPO }
    );

    animate(
        ".field, .submit-row, .auth-foot",
        { opacity: [0, 1], transform: ["translateY(14px)", "translateY(0px)"] },
        { duration: 0.5, delay: stagger(0.08, { startDelay: 0.35 }), ease: EASE_OUT_EXPO }
    );

    animate(
        ".status-line",
        { opacity: [0, 1] },
        { duration: 0.5, delay: 0.9 }
    );
}

function initHoverEffects(): void {
    const button = document.querySelector<HTMLButtonElement>(".btn-primary");
    if (button) {
        button.addEventListener("mouseenter", () => {
            animate(button, { transform: ["scale(1)", "scale(1.015)"] }, { duration: 0.2, ease: "easeOut" });
        });
        button.addEventListener("mouseleave", () => {
            animate(button, { transform: ["scale(1.015)", "scale(1)"] }, { duration: 0.2, ease: "easeOut" });
        });
    }

    document.querySelectorAll<HTMLElement>(".field input").forEach((el) => {
        el.addEventListener("focus", () => {
            animate(el, { transform: ["scale(1)", "scale(1.01)"] }, { duration: 0.15, ease: "easeOut" });
        });
        el.addEventListener("blur", () => {
            animate(el, { transform: ["scale(1.01)", "scale(1)"] }, { duration: 0.15, ease: "easeOut" });
        });
    });
}

/* ---------------------------------------------------------------------------
 * CustomSelect — reemplazo visual premium del <select> nativo para "cargo" y
 * "sede". El <select> original se mantiene en el DOM (oculto visualmente) y
 * sigue siendo la fuente de verdad para el POST (mismo name/value), así que
 * el backend PHP no requiere ningún cambio.
 * ------------------------------------------------------------------------- */
class CustomSelect {
    private select: HTMLSelectElement;
    private field: HTMLElement | null;
    private wrapper: HTMLDivElement;
    private trigger: HTMLButtonElement;
    private triggerLabel: HTMLSpanElement;
    private panel: HTMLDivElement;
    private list: HTMLDivElement;
    private highlight: HTMLDivElement;
    private options: HTMLDivElement[] = [];
    private placeholder: string;
    private isOpen = false;
    private activeIndex = -1;
    private highlightReady = false;

    constructor(select: HTMLSelectElement) {
        this.select = select;
        this.field = select.closest(".field");
        this.placeholder =
            select.querySelector<HTMLOptionElement>('option[value=""]')?.textContent?.trim() ?? "Selecciona";

        this.wrapper = document.createElement("div");
        this.wrapper.className = "select-shell";

        this.trigger = document.createElement("button");
        this.trigger.type = "button";
        this.trigger.className = "select-trigger";
        this.trigger.setAttribute("aria-haspopup", "listbox");
        this.trigger.setAttribute("aria-expanded", "false");
        if (select.id) this.trigger.id = `${select.id}__trigger`;

        this.triggerLabel = document.createElement("span");
        this.triggerLabel.className = "select-trigger-label";
        this.triggerLabel.textContent = this.placeholder;
        this.trigger.appendChild(this.triggerLabel);

        const chevron = document.createElement("span");
        chevron.className = "select-chevron";
        chevron.innerHTML =
            '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="7" viewBox="0 0 11 7" fill="none"><path d="M1 1L5.5 5.5L10 1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        this.trigger.appendChild(chevron);

        this.panel = document.createElement("div");
        this.panel.className = "select-panel";
        this.panel.setAttribute("role", "listbox");

        this.highlight = document.createElement("div");
        this.highlight.className = "select-highlight";

        this.list = document.createElement("div");
        this.list.className = "select-list";
        this.list.appendChild(this.highlight);

        select.querySelectorAll("option").forEach((opt) => {
            if (opt.value === "") return; // el placeholder no es seleccionable
            const item = document.createElement("div");
            item.className = "select-option";
            item.textContent = opt.textContent?.trim() ?? opt.value;
            item.dataset.value = opt.value;
            item.setAttribute("role", "option");
            this.list.appendChild(item);
            this.options.push(item);

            item.addEventListener("mouseenter", () => this.setActive(this.options.indexOf(item), false));
            item.addEventListener("click", () => this.commit(this.options.indexOf(item)));
        });

        this.panel.appendChild(this.list);
        this.wrapper.appendChild(this.trigger);
        this.wrapper.appendChild(this.panel);

        select.parentElement?.insertBefore(this.wrapper, select);
        this.wrapper.appendChild(select);
        select.classList.add("select-native");
        select.tabIndex = -1;
        select.removeAttribute("required");

        const label = select.id ? document.querySelector<HTMLLabelElement>(`label[for="${select.id}"]`) : null;
        if (label) label.htmlFor = this.trigger.id;

        this.trigger.addEventListener("click", () => this.toggle());
        this.trigger.addEventListener("keydown", (e) => this.onTriggerKeydown(e));
        document.addEventListener("click", (e) => {
            if (!this.wrapper.contains(e.target as Node)) this.close();
        });
    }

    get hasValue(): boolean {
        return this.select.value !== "";
    }

    clearError(): void {
        this.trigger.classList.remove("select-trigger--error");
    }

    showError(): void {
        this.trigger.classList.add("select-trigger--error");
        animate(
            this.trigger,
            { transform: ["translateX(0px)", "translateX(-6px)", "translateX(6px)", "translateX(-4px)", "translateX(4px)", "translateX(0px)"] },
            { duration: 0.4, ease: "easeOut" }
        );
    }

    private toggle(): void {
        if (this.isOpen) this.close();
        else this.open();
    }

    private open(): void {
        if (this.isOpen) return;
        this.isOpen = true;
        this.highlightReady = false;
        this.clearError();
        this.trigger.setAttribute("aria-expanded", "true");
        this.panel.classList.add("select-panel--open");
        this.field?.classList.add("field--select-open");

        const selectedIndex = this.options.findIndex((o) => o.dataset.value === this.select.value);
        this.setActive(selectedIndex >= 0 ? selectedIndex : 0, true);

        animate(
            this.panel,
            { opacity: [0, 1], transform: ["translateY(-6px) scale(0.97)", "translateY(0px) scale(1)"] },
            { duration: 0.22, ease: EASE_OUT_EXPO }
        );
        animate(
            this.options,
            { opacity: [0, 1], transform: ["translateY(-4px)", "translateY(0px)"] },
            { duration: 0.22, delay: stagger(0.025), ease: EASE_OUT_EXPO }
        );
    }

    private close(): void {
        if (!this.isOpen) return;
        this.isOpen = false;
        this.trigger.setAttribute("aria-expanded", "false");
        animate(
            this.panel,
            { opacity: [1, 0], transform: ["translateY(0px) scale(1)", "translateY(-6px) scale(0.97)"] },
            { duration: 0.16, ease: "easeIn" }
        ).then(() => {
            if (!this.isOpen) {
                this.panel.classList.remove("select-panel--open");
                this.field?.classList.remove("field--select-open");
            }
        });
    }

    private setActive(index: number, instant: boolean): void {
        if (index < 0 || index >= this.options.length) return;
        this.activeIndex = index;
        this.options.forEach((o, i) => o.classList.toggle("select-option--active", i === index));

        const el = this.options[index];
        const top = el.offsetTop;
        const height = el.offsetHeight;

        if (!this.highlightReady || instant) {
            this.highlight.style.top = `${top}px`;
            this.highlight.style.height = `${height}px`;
            this.highlight.style.opacity = "1";
            this.highlightReady = true;
        } else {
            animate(this.highlight, { top: `${top}px`, height: `${height}px` }, { duration: 0.2, ease: EASE_OUT_EXPO });
        }
    }

    private commit(index: number): void {
        const el = this.options[index];
        if (!el) return;
        const value = el.dataset.value ?? "";
        this.select.value = value;
        this.select.dispatchEvent(new Event("change", { bubbles: true }));
        this.triggerLabel.textContent = el.textContent;
        this.options.forEach((o, i) => o.classList.toggle("select-option--selected", i === index));
        this.clearError();
        this.close();
        this.trigger.focus();
    }

    private onTriggerKeydown(e: KeyboardEvent): void {
        switch (e.key) {
            case "ArrowDown":
                e.preventDefault();
                if (!this.isOpen) this.open();
                else this.setActive(Math.min(this.activeIndex + 1, this.options.length - 1), false);
                break;
            case "ArrowUp":
                e.preventDefault();
                if (!this.isOpen) this.open();
                else this.setActive(Math.max(this.activeIndex - 1, 0), false);
                break;
            case "Enter":
            case " ":
                e.preventDefault();
                if (!this.isOpen) this.open();
                else this.commit(this.activeIndex);
                break;
            case "Escape":
                this.close();
                break;
            case "Home":
                if (this.isOpen) {
                    e.preventDefault();
                    this.setActive(0, false);
                }
                break;
            case "End":
                if (this.isOpen) {
                    e.preventDefault();
                    this.setActive(this.options.length - 1, false);
                }
                break;
        }
    }
}

function initCustomSelects(): CustomSelect[] {
    const selects = document.querySelectorAll<HTMLSelectElement>("#cargo, #campo_sede");
    return Array.from(selects).map((select) => new CustomSelect(select));
}

/* ---------------------------------------------------------------------------
 * Envío del formulario — valida los selects premium (ya que perdieron el
 * "required" nativo al ocultarse) y da feedback visual mientras el POST
 * viaja al server. El submit real lo sigue manejando index.php.
 * ------------------------------------------------------------------------- */
function initFormSubmit(customSelects: CustomSelect[]): void {
    const form = document.querySelector<HTMLFormElement>(".auth-form");
    const button = document.querySelector<HTMLButtonElement>(".btn-primary");
    if (!form || !button) return;

    form.addEventListener("submit", (e) => {
        const invalid = customSelects.filter((cs) => !cs.hasValue);
        if (invalid.length > 0) {
            e.preventDefault();
            invalid.forEach((cs) => cs.showError());
            return;
        }
        button.disabled = true;
        button.textContent = "Verificando…";
    });
}

/* ---------------------------------------------------------------------------
 * Popup de sesión expirada (?motivo=sesion) — replica el comportamiento del
 * index.php legacy, con SweetAlert2 para mantener el patrón ya usado en el
 * resto de la plataforma.
 * ------------------------------------------------------------------------- */
function initSessionExpiredNotice(): void {
    const params = new URLSearchParams(window.location.search);
    if (params.get("motivo") !== "sesion") return;

    const Swal = (window as unknown as { Swal?: Record<string, (opts: Record<string, unknown>) => unknown> }).Swal;
    if (!Swal) return;

    Swal.fire({
        icon: "warning",
        title: "Sesión expirada",
        text: "Por favor, inicia sesión nuevamente.",
        background: "#141519",
        color: "#f2f2f3",
        confirmButtonColor: "#ff7a1a",
    });
}

/* ---------------------------------------------------------------------------
 * Revelado al hacer scroll (por si el layout crece más allá del viewport).
 * ------------------------------------------------------------------------- */
function initScrollReveal(): void {
    document.querySelectorAll<HTMLElement>("[data-reveal]").forEach((el) => {
        inView(el, () => {
            animate(el, { opacity: [0, 1], transform: ["translateY(24px)", "translateY(0px)"] }, { duration: 0.6, ease: EASE_OUT_EXPO });
        });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initBackground();
    initEntranceAnimations();
    initHoverEffects();
    const customSelects = initCustomSelects();
    initFormSubmit(customSelects);
    initSessionExpiredNotice();
    initScrollReveal();
});
