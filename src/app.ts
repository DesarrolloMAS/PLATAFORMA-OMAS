import { animate, stagger, inView } from "motion";
import * as THREE from "three";

const EASE_OUT_EXPO = [0.16, 1, 0.3, 1] as const;

/* ---------------------------------------------------------------------------
 * Fondo 3D — malla cibernética: nodos que flotan y se conectan por líneas
 * cuando quedan cerca entre sí (estilo red neuronal / grid tipo Antigravity),
 * en tonos azules sobre el fondo claro. Sustituye el campo de partículas.
 * ------------------------------------------------------------------------- */
function initBackground(): void {
    const canvas = document.getElementById("bg-canvas") as HTMLCanvasElement | null;
    if (!canvas) return;

    // Sin este try/catch, un WebGL no disponible (hardware/navegador sin
    // soporte) tira una excepción no capturada aquí y detiene TODO lo demás
    // que corre en el mismo DOMContentLoaded (animaciones, selects, hub, etc.)
    // ya que un throw sin capturar corta las siguientes llamadas del handler.
    try {
        initBackgroundScene(canvas);
    } catch (err) {
        console.warn("Fondo 3D no disponible (WebGL no soportado):", err);
    }
}

function initBackgroundScene(canvas: HTMLCanvasElement): void {
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(window.innerWidth, window.innerHeight);

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.z = 22;

    // Tema invertido (menu_administracion.html): fondo casi negro, así que
    // la malla de puntos/líneas pasa de azul a blanco para seguir siendo
    // legible sobre el nuevo fondo oscuro. Tema industrial (menu_mantenimiento.html,
    // ver theme-industrial en menu_principal.css): la malla pasa a naranja en vez
    // de azul/blanco. Tema producción (menu_produccion.html, theme-production):
    // pasa a morado. Tema almacén (menu_almacen.html, theme-warehouse): pasa a
    // azul acerado/metálico, más desaturado que el azul de acento por defecto.
    // Tema Sección Sur (menu_seccion_sur.html, theme-sur): pasa a rojo brillante,
    // a propósito distinto del morado de Producción para diferenciar ZC/ZS de
    // un vistazo. Tema calidad (menu_adm_calidad.html, theme-quality): pasa a
    // beige/café. Tema HSEQ (menu_hseq_adm.html, theme-hseq): pasa a
    // verde bosque. Cada variante de paleta tiene su propia
    // rama porque no es solo un cambio de contraste, es un cambio de
    // paleta completo. theme-industrial, theme-production, theme-warehouse,
    // theme-sur, theme-quality y theme-hseq además pueden combinarse SIN
    // theme-invert (menu_revisiones_mant.html, menu_revisiones_produccion.html,
    // menu_revisiones_almacen.html, menu_revisiones_calidad.html,
    // menu_revisiones_hseq.html: fondo claro del menú principal, para
    // diferenciar a simple vista "revisiones" de su hub oscuro) — sus tonos
    // de fondo oscuro se verían lavados sobre blanco, así que ahí usan
    // versiones más oscuras/saturadas en vez de los tonos casi-blancos
    // pensados para fondo negro.
    const inverted = document.body.classList.contains("theme-invert");
    const industrial = document.body.classList.contains("theme-industrial");
    const production = document.body.classList.contains("theme-production");
    const warehouse = document.body.classList.contains("theme-warehouse");
    const sur = document.body.classList.contains("theme-sur");
    const quality = document.body.classList.contains("theme-quality");
    const hseq = document.body.classList.contains("theme-hseq");

    const NODE_COUNT = 150;
    const BOUNDS = { x: 30, y: 18, z: 15 };
    const LINK_DISTANCE = 7.4;
    const MAX_LINKS = NODE_COUNT * 6;

    const positions = new Float32Array(NODE_COUNT * 3);
    const velocities = new Float32Array(NODE_COUNT * 3);

    for (let i = 0; i < NODE_COUNT; i++) {
        positions[i * 3] = (Math.random() - 0.5) * BOUNDS.x * 2;
        positions[i * 3 + 1] = (Math.random() - 0.5) * BOUNDS.y * 2;
        positions[i * 3 + 2] = (Math.random() - 0.5) * BOUNDS.z * 2;
        velocities[i * 3] = (Math.random() - 0.5) * 0.02;
        velocities[i * 3 + 1] = (Math.random() - 0.5) * 0.02;
        velocities[i * 3 + 2] = (Math.random() - 0.5) * 0.012;
    }

    const nodeGeometry = new THREE.BufferGeometry();
    nodeGeometry.setAttribute("position", new THREE.BufferAttribute(positions, 3));

    const nodeMaterial = new THREE.PointsMaterial({
        color: sur ? (inverted ? 0xff6b6b : 0xef4444) : industrial ? (inverted ? 0xff8a3d : 0xfb923c) : production ? (inverted ? 0xb388ff : 0xa855f7) : warehouse ? (inverted ? 0x6f95c4 : 0x5b84ac) : quality ? (inverted ? 0xa38c93 : 0x66404b) : hseq ? (inverted ? 0x77a389 : 0x1c663a) : inverted ? 0xf4f8fd : 0x2f7bff,
        size: 0.36,
        transparent: true,
        opacity: 0.9,
        sizeAttenuation: true,
    });
    const nodes = new THREE.Points(nodeGeometry, nodeMaterial);
    scene.add(nodes);

    // Líneas que conectan nodos cercanos — se recalculan periódicamente
    // a medida que los nodos se mueven, dando el efecto de "malla viva".
    const linePositions = new Float32Array(MAX_LINKS * 2 * 3);
    const lineGeometry = new THREE.BufferGeometry();
    lineGeometry.setAttribute("position", new THREE.BufferAttribute(linePositions, 3));
    lineGeometry.setDrawRange(0, 0);

    const lineMaterial = new THREE.LineBasicMaterial({
        color: sur ? (inverted ? 0xffb3b3 : 0xf87171) : industrial ? (inverted ? 0xffc999 : 0xfdba74) : production ? (inverted ? 0xd9c2ff : 0xc084fc) : warehouse ? (inverted ? 0xb0c4de : 0x8fa8c9) : quality ? (inverted ? 0xbaa9ae : 0x85666f) : hseq ? (inverted ? 0x99baa7 : 0x498561) : inverted ? 0xdbe8fb : 0x5fb3ff,
        transparent: true,
        opacity: 0.5,
    });
    const links = new THREE.LineSegments(lineGeometry, lineMaterial);
    scene.add(links);

    let currentLinkCount = 0;
    function rebuildLinks(): void {
        let linkCount = 0;
        for (let i = 0; i < NODE_COUNT && linkCount < MAX_LINKS; i++) {
            const ax = positions[i * 3], ay = positions[i * 3 + 1], az = positions[i * 3 + 2];
            for (let j = i + 1; j < NODE_COUNT && linkCount < MAX_LINKS; j++) {
                const bx = positions[j * 3], by = positions[j * 3 + 1], bz = positions[j * 3 + 2];
                const dx = ax - bx, dy = ay - by, dz = az - bz;
                if (dx * dx + dy * dy + dz * dz < LINK_DISTANCE * LINK_DISTANCE) {
                    const o = linkCount * 6;
                    linePositions[o] = ax; linePositions[o + 1] = ay; linePositions[o + 2] = az;
                    linePositions[o + 3] = bx; linePositions[o + 4] = by; linePositions[o + 5] = bz;
                    linkCount++;
                }
            }
        }
        (lineGeometry.attributes.position as THREE.BufferAttribute).needsUpdate = true;
        lineGeometry.setDrawRange(0, linkCount * 2);
        currentLinkCount = linkCount;
    }
    rebuildLinks();

    // Segunda capa de nodos, más tenue y lejana, solo para dar profundidad.
    const farGeometry = nodeGeometry.clone();
    const farMaterial = new THREE.PointsMaterial({
        color: sur ? (inverted ? 0xffc2c2 : 0xfecaca) : industrial ? (inverted ? 0xffb066 : 0xfed7aa) : production ? (inverted ? 0xc9a3ff : 0xe0cbfa) : warehouse ? (inverted ? 0x8fa8c9 : 0xb8c8dc) : quality ? (inverted ? 0xd1c6c9 : 0xe0d9db) : hseq ? (inverted ? 0xbbd1c4 : 0xd2e0d8) : 0xbfe0ff,
        size: 0.2,
        transparent: true,
        opacity: 0.45,
    });
    const farNodes = new THREE.Points(farGeometry, farMaterial);
    farNodes.position.z = -14;
    scene.add(farNodes);

    // Chispas — pequeños destellos que viajan sobre un enlace (línea) elegido
    // al azar, de un extremo al otro, y luego saltan a otro enlace tras una
    // pausa. Se disparan por tiempo, sin depender del mouse; son el elemento
    // principal de "actividad" de la malla.
    const SPARK_COUNT = 6;
    const SPARK_TRAVEL_MIN = 0.6;
    const SPARK_TRAVEL_MAX = 1.3;
    const SPARK_WAIT_MIN = 0.3;
    const SPARK_WAIT_MAX = 2.6;

    interface Spark {
        mesh: THREE.Mesh;
        material: THREE.MeshBasicMaterial;
        start: THREE.Vector3;
        end: THREE.Vector3;
        progress: number;
        duration: number;
        wait: number;
        traveling: boolean;
    }

    const sparkGeometry = new THREE.CircleGeometry(0.4, 16);
    const sparks: Spark[] = [];
    for (let i = 0; i < SPARK_COUNT; i++) {
        const material = new THREE.MeshBasicMaterial({
            color: sur ? (inverted ? 0xfee2e2 : 0xdc2626) : industrial ? (inverted ? 0xffedd5 : 0xea580c) : production ? (inverted ? 0xf3e8ff : 0x9333ea) : warehouse ? (inverted ? 0xe0e8f0 : 0x2c4a6e) : quality ? (inverted ? 0xf0eced : 0x472d35) : hseq ? (inverted ? 0xe8f0eb : 0x123d23) : 0xeaf6ff,
            transparent: true,
            opacity: 0,
            side: THREE.DoubleSide,
            blending: THREE.AdditiveBlending,
            depthWrite: false,
        });
        const mesh = new THREE.Mesh(sparkGeometry, material);
        scene.add(mesh);
        sparks.push({
            mesh,
            material,
            start: new THREE.Vector3(),
            end: new THREE.Vector3(),
            progress: 0,
            duration: 1,
            wait: Math.random() * SPARK_WAIT_MAX,
            traveling: false,
        });
    }

    function launchSpark(spark: Spark): void {
        const idx = Math.floor(Math.random() * currentLinkCount);
        const o = idx * 6;
        spark.start.set(linePositions[o], linePositions[o + 1], linePositions[o + 2]);
        spark.end.set(linePositions[o + 3], linePositions[o + 4], linePositions[o + 5]);
        spark.progress = 0;
        spark.duration = SPARK_TRAVEL_MIN + Math.random() * (SPARK_TRAVEL_MAX - SPARK_TRAVEL_MIN);
        spark.traveling = true;
    }

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
    let frameCount = 0;
    const clock = new THREE.Clock();
    function tick() {
        frameId = requestAnimationFrame(tick);
        frameCount++;
        const dt = clock.getDelta();

        for (let i = 0; i < NODE_COUNT; i++) {
            const ix = i * 3, iy = i * 3 + 1, iz = i * 3 + 2;
            let x = positions[ix] + velocities[ix];
            let y = positions[iy] + velocities[iy];
            let z = positions[iz] + velocities[iz];
            if (x > BOUNDS.x || x < -BOUNDS.x) velocities[ix] *= -1;
            if (y > BOUNDS.y || y < -BOUNDS.y) velocities[iy] *= -1;
            if (z > BOUNDS.z || z < -BOUNDS.z) velocities[iz] *= -1;
            positions[ix] = x; positions[iy] = y; positions[iz] = z;
        }
        (nodeGeometry.attributes.position as THREE.BufferAttribute).needsUpdate = true;

        // Recalcular la malla cada pocos cuadros — recomputar cada frame sería
        // O(n^2) constante e innecesario ya que los nodos se mueven despacio.
        if (frameCount % 12 === 0) rebuildLinks();

        farNodes.rotation.y += 0.0015;

        for (const spark of sparks) {
            if (!spark.traveling) {
                spark.wait -= dt;
                if (spark.wait <= 0 && currentLinkCount > 0) launchSpark(spark);
                continue;
            }
            spark.progress += dt / spark.duration;
            if (spark.progress >= 1) {
                spark.traveling = false;
                spark.wait = SPARK_WAIT_MIN + Math.random() * (SPARK_WAIT_MAX - SPARK_WAIT_MIN);
                spark.material.opacity = 0;
                continue;
            }
            spark.mesh.position.lerpVectors(spark.start, spark.end, spark.progress);
            spark.mesh.lookAt(camera.position);
            const fadeIn = Math.min(1, spark.progress * 6);
            const fadeOut = Math.min(1, (1 - spark.progress) * 6);
            const glow = fadeIn * fadeOut;
            spark.material.opacity = glow * 0.95;
            spark.mesh.scale.setScalar(0.7 + glow * 0.7);
        }

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

    // En formularios de varios pasos, el paso 2 arranca oculto
    // (.form-step--hidden) y anima su propia entrada al revelarse — aquí solo
    // se anima lo que ya es visible en la carga inicial.
    const revealTargets = Array.from(
        document.querySelectorAll<HTMLElement>(".field, .submit-row, .auth-foot, .form-section-title, .step-indicator")
    ).filter((el) => !el.closest(".form-step--hidden"));

    animate(
        revealTargets,
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
    const selects = document.querySelectorAll<HTMLSelectElement>("#cargo, #campo_sede, #campo_rol, #campo_Area");
    return Array.from(selects).map((select) => new CustomSelect(select));
}

/* ---------------------------------------------------------------------------
 * Transición de login exitoso — la tarjeta del formulario sale de pantalla
 * bajando mientras el logo crece, y solo entonces se navega al menú. Se
 * ejecuta después de que el fetch() de login confirma éxito.
 * ------------------------------------------------------------------------- */
function playLoginSuccessTransition(): Promise<void> {
    const card = document.querySelector<HTMLElement>(".auth-card");
    const brand = document.querySelector<HTMLElement>(".brand");
    const statusLine = document.querySelector<HTMLElement>(".status-line");

    document.body.style.overflow = "hidden";

    const animations: Promise<unknown>[] = [];

    if (card) {
        animations.push(
            animate(
                card,
                { opacity: [1, 0], transform: ["translateY(0vh) scale(1)", "translateY(120vh) scale(0.94)"] },
                { duration: 0.6, ease: EASE_OUT_EXPO }
            )
        );
    }
    if (statusLine) {
        animations.push(animate(statusLine, { opacity: [1, 0] }, { duration: 0.25, ease: "easeIn" }));
    }
    if (brand) {
        animations.push(
            animate(
                brand,
                { transform: ["scale(1)", "scale(2.8)"] },
                { duration: 0.85, delay: 0.2, ease: EASE_OUT_EXPO }
            )
        );
    }

    return Promise.all(animations).then(() => undefined);
}

/* ---------------------------------------------------------------------------
 * Envío del login — se intercepta con fetch() (header X-Requested-With para
 * que index.php responda JSON en vez de redirigir con header Location) así
 * hay un momento real de "éxito" donde reproducir la transición antes de
 * navegar al destino que decide el backend según área/rol.
 * ------------------------------------------------------------------------- */
function initLoginSubmit(form: HTMLFormElement, button: HTMLButtonElement, customSelects: CustomSelect[]): void {
    const originalText = button.textContent ?? "Iniciar sesión";

    function mostrarErrorLogin(mensaje: string): void {
        button.disabled = false;
        button.textContent = originalText;
        const Swal = (window as unknown as { Swal?: Record<string, (opts: Record<string, unknown>) => unknown> }).Swal;
        if (!Swal) return;
        Swal.fire({
            icon: "error",
            title: "Credenciales incorrectas",
            text: mensaje,
            background: "#ffffff",
            color: "#0b1b33",
            confirmButtonColor: "#2563eb",
        });
    }

    form.addEventListener("submit", (e) => {
        e.preventDefault();

        const invalidSelects = customSelects.filter((cs) => !cs.hasValue);
        if (invalidSelects.length > 0) {
            invalidSelects.forEach((cs) => cs.showError());
            return;
        }

        button.disabled = true;
        button.textContent = button.dataset.loadingText ?? "Verificando…";

        fetch(window.location.pathname, {
            method: "POST",
            headers: { "X-Requested-With": "fetch" },
            body: new FormData(form),
        })
            .then((r) => r.json())
            .then((data: { status?: string; redirect?: string; message?: string }) => {
                if (data.status === "success" && data.redirect) {
                    playLoginSuccessTransition().then(() => {
                        window.location.href = data.redirect as string;
                    });
                    return;
                }
                mostrarErrorLogin(data.message || "Por favor, verifica los datos.");
            })
            .catch(() => {
                mostrarErrorLogin("Ocurrió un error de conexión. Intenta de nuevo.");
            });
    });
}

/* ---------------------------------------------------------------------------
 * Envío del formulario — valida los selects premium (ya que perdieron el
 * "required" nativo al ocultarse). El login (un solo paso) se intercepta con
 * fetch para poder animar la transición de éxito; el registro (varios pasos)
 * sigue con el POST nativo y el backend PHP redirige con header Location.
 * ------------------------------------------------------------------------- */
function initFormSubmit(customSelects: CustomSelect[]): void {
    const form = document.querySelector<HTMLFormElement>(".auth-form");
    const button = form?.querySelector<HTMLButtonElement>('button[type="submit"]') ?? null;
    if (!form || !button) return;

    if (form.classList.contains("auth-form--steps")) {
        form.addEventListener("submit", (e) => {
            const invalidSelects = customSelects.filter((cs) => !cs.hasValue);
            if (invalidSelects.length > 0) {
                e.preventDefault();
                invalidSelects.forEach((cs) => cs.showError());
                return;
            }
            button.disabled = true;
            button.textContent = button.dataset.loadingText ?? "Verificando…";
        });
        return;
    }

    initLoginSubmit(form, button, customSelects);
}

/* ---------------------------------------------------------------------------
 * Formulario de registro en 3 pasos — separa los datos personales (nombre y
 * confirmación de cédula), los datos empresariales, y por último la
 * contraseña de registro junto con la confirmación de un administrador.
 * Reutiliza las mismas curvas/duraciones de entrada que el resto del auth,
 * con una barra de progreso que se llena al avanzar.
 * ------------------------------------------------------------------------- */
function initFormSteps(customSelects: CustomSelect[]): void {
    const formEl = document.querySelector<HTMLFormElement>(".auth-form--steps");
    if (!formEl) return;
    const form = formEl;

    const steps = Array.from(form.querySelectorAll<HTMLElement>(".form-step"));
    const circles = Array.from(form.querySelectorAll<HTMLElement>("[data-step-circle]"));
    const labels = Array.from(form.querySelectorAll<HTMLElement>("[data-step-label]"));
    const trackFills = Array.from(form.querySelectorAll<HTMLElement>(".step-track-fill"));
    let current = 1;

    function updateIndicator(step: number): void {
        circles.forEach((el) => {
            const n = Number(el.dataset.stepCircle);
            el.classList.toggle("step-circle--active", n === step);
            el.classList.toggle("step-circle--done", n < step);
        });
        labels.forEach((el) => {
            const n = Number(el.dataset.stepLabel);
            el.classList.toggle("step-label--active", n <= step);
        });
        trackFills.forEach((el, i) => {
            animate(el, { width: step > i + 1 ? "100%" : "0%" }, { duration: 0.4, ease: EASE_OUT_EXPO });
        });
    }

    function revealStep(el: HTMLElement): void {
        el.classList.remove("form-step--hidden");
        animate(
            el,
            { opacity: [0, 1], transform: ["translateY(10px)", "translateY(0px)"] },
            { duration: 0.4, ease: EASE_OUT_EXPO }
        );
        animate(
            el.querySelectorAll<HTMLElement>(".field, .submit-row, .form-section-title"),
            { opacity: [0, 1], transform: ["translateY(14px)", "translateY(0px)"] },
            { duration: 0.45, delay: stagger(0.07, { startDelay: 0.12 }), ease: EASE_OUT_EXPO }
        );
    }

    function goToStep(target: number): void {
        const fromEl = steps[current - 1];
        const toEl = steps[target - 1];
        if (target === current || !fromEl || !toEl) return;

        animate(fromEl, { opacity: [1, 0] }, { duration: 0.18, ease: "easeIn" }).then(() => {
            fromEl.classList.add("form-step--hidden");
        });
        revealStep(toEl);
        updateIndicator(target);
        current = target;
    }

    function showFieldError(input: HTMLInputElement | null): void {
        if (!input) return;
        input.classList.add("field-input--error");
        animate(
            input,
            { transform: ["translateX(0px)", "translateX(-6px)", "translateX(6px)", "translateX(-4px)", "translateX(4px)", "translateX(0px)"] },
            { duration: 0.4, ease: "easeOut" }
        );
    }

    function validateStep1(): boolean {
        const nombre = form.querySelector<HTMLInputElement>("#campo_nombre");
        const cedula = form.querySelector<HTMLInputElement>("#campo_cedula");
        const cedula2 = form.querySelector<HTMLInputElement>("#campo_cedula2");
        let valid = true;

        [nombre, cedula, cedula2].forEach((input) => {
            input?.classList.remove("field-input--error");
            if (!input?.value.trim()) {
                valid = false;
                showFieldError(input);
            }
        });

        if (cedula?.value.trim() && cedula2?.value.trim() && cedula.value.trim() !== cedula2.value.trim()) {
            valid = false;
            showFieldError(cedula2);
        }

        return valid;
    }

    // Paso 2 solo contiene los selects premium (cargo, rol, área, sede), que
    // perdieron el "required" nativo al ocultarse — se validan con el mismo
    // mecanismo que usa el envío final del formulario.
    function validateStep2(): boolean {
        const invalid = customSelects.filter((cs) => !cs.hasValue);
        invalid.forEach((cs) => cs.showError());
        return invalid.length === 0;
    }

    const validators: Record<number, () => boolean> = {
        1: validateStep1,
        2: validateStep2,
    };

    form.querySelectorAll<HTMLButtonElement>("[data-goto]").forEach((btn) => {
        const target = Number(btn.dataset.goto);
        btn.addEventListener("click", () => {
            if (target > current && !(validators[current]?.() ?? true)) return;
            goToStep(target);
        });
    });

    form.querySelector<HTMLInputElement>("#campo_cedula2")?.addEventListener("input", (e) => {
        (e.currentTarget as HTMLInputElement).classList.remove("field-input--error");
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
        background: "#ffffff",
        color: "#0b1b33",
        confirmButtonColor: "#2563eb",
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

/* ---------------------------------------------------------------------------
 * Onda del engranaje entre menu_adm.html y menu_administracion.html: un
 * disco circular nace en el centro del hub y crece (o se repliega) hasta
 * cubrir/despejar toda la pantalla, llevando el tono del destino de adentro
 * hacia afuera. El engranaje no se escala — sólo gira (ver el spin de 360°
 * ya disparado en el click, en initHubGearAndMagnets). Se escribe clip-path
 * directo por frame (mismo motivo que la rotación: necesitamos el radio
 * exacto en cada cuadro para sincronizar el final del crecimiento con la
 * navegación real) y se resuelve con una Promise para poder esperar antes
 * de navegar o de soltar trabajo pesado (el fondo 3D).
 * ------------------------------------------------------------------------- */
const GEAR_WAVE_TONE = "radial-gradient(circle, #0b0e14 0%, #05070b 70%)";

function tweenNumber(from: number, to: number, duration: number, onUpdate: (value: number) => void): Promise<void> {
    return new Promise((resolve) => {
        const start = performance.now();
        function step(now: number): void {
            const t = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - t, 3); // easeOutCubic
            onUpdate(from + (to - from) * eased);
            if (t < 1) {
                requestAnimationFrame(step);
            } else {
                resolve();
            }
        }
        requestAnimationFrame(step);
    });
}

function ensureGearWave(): HTMLElement {
    let wave = document.querySelector<HTMLElement>(".gear-wave");
    if (!wave) {
        wave = document.createElement("div");
        wave.className = "gear-wave";
        document.body.appendChild(wave);
    }
    wave.style.background = GEAR_WAVE_TONE;
    return wave;
}

function hubCenterOrigin(hub: HTMLElement): { x: number; y: number } {
    const center = hub.querySelector<HTMLElement>(".hub-center");
    const rect = (center ?? hub).getBoundingClientRect();
    return { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };
}

function maxRadiusFrom(x: number, y: number): number {
    return Math.hypot(Math.max(x, window.innerWidth - x), Math.max(y, window.innerHeight - y));
}

function playGearWaveExit(hub: HTMLElement, destino: string): void {
    const { x, y } = hubCenterOrigin(hub);
    const wave = ensureGearWave();
    wave.style.clipPath = `circle(0px at ${x}px ${y}px)`;

    const exitLinks = Array.from(document.querySelectorAll<HTMLElement>(".menu-exit"));
    if (exitLinks.length) animate(exitLinks, { opacity: [1, 0] }, { duration: 0.35, ease: "easeIn" });

    tweenNumber(0, maxRadiusFrom(x, y), 700, (r) => {
        wave.style.clipPath = `circle(${r}px at ${x}px ${y}px)`;
    }).then(() => {
        window.location.href = destino;
    });
}

/* Contraparte de entrada: sólo actúa si el HTML trae [data-wave-entrance] en
 * .hub-center-anchor (ver menu_administracion.html) — la onda arranca ya
 * cubriendo toda la pantalla (para no mostrar un flash del layout final
 * antes de replegarse) y se repliega hasta desaparecer en el centro del hub.
 * En cualquier otra página es un no-op.
 *
 * Devuelve una Promise que resuelve cuando el tween termina (o de inmediato
 * si no hay onda) para que quien la llame pueda posponer trabajo pesado
 * (el fondo 3D) hasta que el tween ya no compita por el hilo principal. */
function initGearWaveEntrance(): Promise<void> {
    const marker = document.querySelector<HTMLElement>(".hub-center-anchor[data-wave-entrance]");
    const hub = document.querySelector<HTMLElement>(".hub");
    if (!marker || !hub) return Promise.resolve();

    const { x, y } = hubCenterOrigin(hub);
    const radius = maxRadiusFrom(x, y);
    const wave = ensureGearWave();
    wave.style.clipPath = `circle(${radius}px at ${x}px ${y}px)`;

    return tweenNumber(radius, 0, 750, (r) => {
        wave.style.clipPath = `circle(${r}px at ${x}px ${y}px)`;
    }).then(() => {
        wave.remove();
    });
}

/* ---------------------------------------------------------------------------
 * Transición "recogida" del hub, para navegar entre dos hubs del mismo tono
 * (ej. menu_mantenimiento.html → menu_revisiones_mant.html: ambos
 * theme-industrial, no hace falta la onda de color porque no hay tono que
 * tapar). Los .hub-node se recogen hacia el centro del engranaje y se
 * encogen a la salida; en la página de entrada nacen ya recogidos en el
 * centro y se desenrollan hacia su posición final — mismo delta de
 * distancia, solo que animado en sentidos opuestos. El engranaje central es
 * el mismo elemento visual en ambas páginas (mismo trazado, mismo lugar),
 * así que no necesita su propia animación de transición: los nodos son lo
 * único que cambia de una página a otra.
 * ------------------------------------------------------------------------- */
function nodeDeltaToCenter(node: HTMLElement, center: { x: number; y: number }): { dx: number; dy: number } {
    const rect = node.getBoundingClientRect();
    const nodeCenterX = rect.left + rect.width / 2;
    const nodeCenterY = rect.top + rect.height / 2;
    return { dx: center.x - nodeCenterX, dy: center.y - nodeCenterY };
}

function playHubRetractExit(hub: HTMLElement, destino: string): void {
    const center = hubCenterOrigin(hub);
    const nodes = Array.from(hub.querySelectorAll<HTMLElement>(".hub-node"));
    const exitLinks = Array.from(document.querySelectorAll<HTMLElement>(".menu-exit"));

    const animations: Promise<unknown>[] = nodes.map((node) => {
        const { dx, dy } = nodeDeltaToCenter(node, center);
        return animate(
            node,
            { x: [0, dx], y: [0, dy], scale: [1, 0], opacity: [1, 0] },
            { duration: 0.5, ease: "easeIn" }
        );
    });
    if (exitLinks.length) animations.push(animate(exitLinks, { opacity: [1, 0] }, { duration: 0.35, ease: "easeIn" }));

    Promise.all(animations).then(() => {
        window.location.href = destino;
    });
}

/* Contraparte de entrada: sólo actúa si el HTML trae [data-unroll-entrance]
 * en .hub (ver menu_revisiones_mant.html) — los .hub-node arrancan ya
 * recogidos y encogidos en el centro (fijado por estilo directo antes del
 * primer paint, para no mostrar un flash en su posición final) y se
 * desenrollan hacia afuera. En cualquier otra página es un no-op. */
function initHubUnrollEntrance(): void {
    const hub = document.querySelector<HTMLElement>(".hub[data-unroll-entrance]");
    if (!hub) return;

    const center = hubCenterOrigin(hub);
    const nodes = Array.from(hub.querySelectorAll<HTMLElement>(".hub-node"));

    nodes.forEach((node, i) => {
        const { dx, dy } = nodeDeltaToCenter(node, center);
        node.style.opacity = "0";
        node.style.transform = `translate(${dx}px, ${dy}px) scale(0)`;
        animate(
            node,
            { x: [dx, 0], y: [dy, 0], scale: [0, 1], opacity: [0, 1] },
            { duration: 0.55, delay: 0.2 + i * 0.05, ease: EASE_OUT_EXPO }
        );
    });
}

/* ---------------------------------------------------------------------------
 * Menú principal (hub radial) — engranaje interactivo + botones magnéticos.
 * El engranaje central tiene capas SVG independientes (ver menu_adm.html):
 *   .gear-rotator     → gira para "mirar" hacia el mouse en toda la página
 *                       (ángulo objetivo suavizado por interpolación); el
 *                       salto +360° de click se sigue sumando encima del
 *                       ángulo base.
 *     .gear-teeth     → cuerpo + dientes (único hijo de .gear-rotator).
 *   .gear-ring        → aro azul (brillo en hover, onda de energía en click),
 *     .gear-dot-orbit → punto de las 4, ya no hijos de .gear-rotator: quedan
 *                       fijos igual que .gear-chart (barras + colina).
 * La rotación se escribe directamente por frame (no con motion.animate(),
 * que no aplica el transform de forma fiable sobre grupos <g> de SVG en este
 * bundle). Los 6 botones del hub (.hub-node) siguen con su efecto magnético
 * aparte.
 * ------------------------------------------------------------------------- */
function initHubGearAndMagnets(): void {
    const hub = document.querySelector<HTMLElement>(".hub");
    if (!hub) return;

    const gearRotator = hub.querySelector<SVGGElement>(".gear-rotator");
    const gearRing = hub.querySelector<SVGGElement>(".gear-ring");
    const gearCenter = hub.querySelector<HTMLElement>(".hub-center");
    const nodes = Array.from(hub.querySelectorAll<HTMLElement>(".hub-node"));

    // Respiración sutil del badge completo — vida propia, en un canal
    // (elemento) distinto al de la rotación para que no compitan.
    if (gearCenter) {
        animate(gearCenter, { scale: [1, 1.02, 1] }, { duration: 4, repeat: Infinity, ease: "easeInOut" });
    }

    const MAGNET_RADIUS = 170; // px — distancia desde donde un botón empieza a "sentir" el mouse
    const MAGNET_STRENGTH = 0.35; // fracción del vector mouse→botón que se traduce en desplazamiento
    const MAGNET_MAX = 16; // px — desplazamiento máximo permitido por eje

    let mouseX = window.innerWidth / 2;
    let mouseY = window.innerHeight / 2;
    let ticking = false;

    // Sólo .gear-teeth (vía .gear-rotator) gira para "mirar" hacia el mouse
    // en toda la página; el ángulo objetivo se suaviza por interpolación.
    const FOLLOW_LERP = 0.08;

    let currentAngle = 0;
    let targetAngle = 0;
    let clickSpinBase = 0;
    let clickAnim: { start: number; from: number; to: number; duration: number } | null = null;

    function shortestDelta(from: number, to: number): number {
        return ((((to - from) % 360) + 540) % 360) - 180;
    }

    function tickRotation(now: number): void {
        if (gearCenter) {
            const rect = gearCenter.getBoundingClientRect();
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;
            const dx = mouseX - cx;
            const dy = mouseY - cy;
            targetAngle = Math.atan2(dy, dx) * (180 / Math.PI);
        }

        currentAngle += shortestDelta(currentAngle, targetAngle) * FOLLOW_LERP;

        let clickAngle = clickSpinBase;
        if (clickAnim) {
            const t = Math.min(1, (now - clickAnim.start) / clickAnim.duration);
            const eased = 1 - Math.pow(1 - t, 3); // easeOutCubic
            clickAngle = clickAnim.from + (clickAnim.to - clickAnim.from) * eased;
            if (t >= 1) {
                clickSpinBase = clickAnim.to;
                clickAnim = null;
            }
        }

        if (gearRotator) {
            gearRotator.style.transform = `rotate(${currentAngle + clickAngle}deg)`;
        }

        requestAnimationFrame(tickRotation);
    }
    requestAnimationFrame(tickRotation);

    if (gearCenter) {
        gearCenter.addEventListener("mouseenter", () => {
            gearRing?.classList.add("is-hovering");
        });
        gearCenter.addEventListener("mouseleave", () => {
            gearRing?.classList.remove("is-hovering");
        });
        gearCenter.addEventListener("click", () => {
            clickAnim = { start: performance.now(), from: clickSpinBase, to: clickSpinBase + 360, duration: 900 };
            gearRing?.classList.remove("is-pulsing");
            void gearRing?.getBoundingClientRect(); // fuerza reflow para poder re-disparar la animación
            gearRing?.classList.add("is-pulsing");

            // El engranaje siempre gira al hacer click; sólo navega a un
            // submenú si data-gear-nav está presente. data-gear-check="admin"
            // exige además que la sesión sea 'adm' (verificado en el
            // servidor) antes de disparar la transición — si no es adm, no
            // pasa nada más que el giro, no se revela si el destino existe.
            // data-gear-transition elige la transición: "wave" (default,
            // onda de color — para saltar entre paletas distintas) o
            // "retract" (los nodos se recogen al centro — para saltar entre
            // dos hubs de la misma paleta, donde tapar el cambio de tono no
            // hace falta). Ver playGearWaveExit/playHubRetractExit arriba.
            const destino = gearCenter.dataset.gearNav;
            if (!destino) return;

            const runTransition = (): void => {
                if (gearCenter.dataset.gearTransition === "retract") {
                    playHubRetractExit(hub, destino);
                } else {
                    playGearWaveExit(hub, destino);
                }
            };

            if (gearCenter.dataset.gearCheck === "admin") {
                fetch("verificar_admin.php")
                    .then((res) => res.json())
                    .then((data: { esAdmin?: boolean }) => {
                        if (data.esAdmin) runTransition();
                    })
                    .catch(() => {});
            } else {
                runTransition();
            }
        });
    }

    function updateMagnets(): void {
        ticking = false;

        nodes.forEach((node) => {
            const rect = node.getBoundingClientRect();
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;
            const dx = mouseX - cx;
            const dy = mouseY - cy;
            const dist = Math.hypot(dx, dy);

            if (dist < MAGNET_RADIUS) {
                const pull = 1 - dist / MAGNET_RADIUS;
                const x = Math.max(-MAGNET_MAX, Math.min(MAGNET_MAX, dx * MAGNET_STRENGTH * pull));
                const y = Math.max(-MAGNET_MAX, Math.min(MAGNET_MAX, dy * MAGNET_STRENGTH * pull));
                animate(node, { x, y, scale: 1 + pull * 0.05 }, { duration: 0.4, ease: EASE_OUT_EXPO });
            } else {
                animate(node, { x: 0, y: 0, scale: 1 }, { duration: 0.5, ease: EASE_OUT_EXPO });
            }
        });
    }

    window.addEventListener("pointermove", (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(updateMagnets);
        }
    });

    document.addEventListener("pointerleave", () => {
        nodes.forEach((node) => {
            animate(node, { x: 0, y: 0, scale: 1 }, { duration: 0.5, ease: EASE_OUT_EXPO });
        });
    });
}

/* ---------------------------------------------------------------------------
 * Entrada del engranaje central: el aro de progreso (#progress-arc) y las
 * barras (.gear-chart-bar) son piezas trazadas (relleno, no stroke — ver
 * menu_adm.html), así que en vez de "dibujar" el trazo con dashoffset se
 * revelan con fade + scale, igual que dientes/nodo, cada uno a su ritmo.
 * ------------------------------------------------------------------------- */
function initHubGearMountAnimation(): void {
    const hub = document.querySelector<HTMLElement>(".hub");
    if (!hub) return;

    const progressArc = hub.querySelector<SVGPathElement>("#progress-arc");
    const progressNode = hub.querySelector<SVGPathElement>("#progress-node");
    const gearTeeth = hub.querySelector<SVGPathElement>(".gear-teeth");
    const chartHill = hub.querySelector<SVGPathElement>(".gear-chart-hill");
    const chartBars = Array.from(hub.querySelectorAll<SVGPathElement>(".gear-chart-bar"));
    const EASE_OUT_BACK = [0.34, 1.56, 0.64, 1] as const;

    if (gearTeeth) {
        animate(
            gearTeeth,
            { opacity: [0, 1], scale: [0.82, 1], rotate: [-24, 0] },
            { duration: 0.7, ease: EASE_OUT_EXPO }
        );
    }

    if (progressArc) {
        animate(
            progressArc,
            { opacity: [0, 1] },
            { duration: 0.9, delay: 0.25, ease: EASE_OUT_EXPO }
        );
    }

    if (progressNode) {
        animate(
            progressNode,
            { opacity: [0, 1], scale: [0.3, 1] },
            { duration: 0.45, delay: 1.05, ease: EASE_OUT_BACK }
        );
    }

    if (chartHill) {
        animate(chartHill, { opacity: [0, 1] }, { duration: 0.5, delay: 0.35, ease: EASE_OUT_EXPO });
    }

    if (chartBars.length) {
        animate(
            chartBars,
            { opacity: [0, 1], scaleY: [0, 1] },
            { duration: 0.5, delay: stagger(0.09, { startDelay: 0.45 }), ease: EASE_OUT_EXPO }
        );
    }
}

document.addEventListener("DOMContentLoaded", () => {
    initEntranceAnimations();
    initHoverEffects();
    const customSelects = initCustomSelects();
    initFormSubmit(customSelects);
    initFormSteps(customSelects);
    initSessionExpiredNotice();
    initScrollReveal();
    initHubGearAndMagnets();
    initHubGearMountAnimation();
    initHubUnrollEntrance();
    // El fondo 3D (Three.js) se construye recién cuando el tween de entrada
    // de la onda termina: si corren a la vez, la construcción de la escena
    // (renderer, geometrías, su propio loop de rAF) le roba frames al tween
    // justo cuando el motor JS todavía no está optimizado, y se ve entrecortado.
    initGearWaveEntrance().then(initBackground);
});
