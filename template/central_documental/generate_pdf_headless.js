const puppeteer = require('puppeteer');
const fs = require('fs');

const CHROME_ARGS = [
    '--no-sandbox',
    '--disable-setuid-sandbox',
    '--ignore-certificate-errors',
    '--disable-dev-shm-usage',
    '--disable-gpu',
    '--disable-extensions',
    '--disable-background-networking'
];

const BLOCKED_DOMAINS = [
    'google-analytics.com',
    'googletagmanager.com',
    'facebook.net',
    'doubleclick.net'
];

/**
 * Renderiza un único job {id, url, pdfPath, landscape} en una pestaña nueva
 * del navegador recibido (el navegador se reutiliza entre jobs; aquí solo se
 * abre y cierra la pestaña). Devuelve { id, success, path|error, status }.
 */
async function renderJob(browser, sessionId, domain, job) {
    const page = await browser.newPage();
    try {
        page.setDefaultNavigationTimeout(90000);
        page.setDefaultTimeout(90000);

        // Bloquear analíticas, rastreadores y recursos externos no esenciales
        await page.setRequestInterception(true);
        page.on('request', (req) => {
            const reqUrl = req.url();
            const isBlocked = BLOCKED_DOMAINS.some(d => reqUrl.includes(d));
            if (isBlocked) req.abort();
            else req.continue();
        });

        // Inyectar la sesión de PHP para que el visor no pida login
        await page.setCookie({
            name: 'PHPSESSID',
            value: sessionId,
            domain: domain,
            path: '/'
        });

        // Simular modo impresión
        await page.emulateMediaType('print');

        // Navegar con tolerancia: si hay timeout, intentar generar PDF con lo que cargó.
        // waitUntil combinado (load + networkidle0) espera una condición real de
        // "página lista" en vez de adivinar un tiempo fijo.
        let navigationOk = true;
        try {
            await page.goto(job.url, { waitUntil: ['load', 'networkidle0'], timeout: 90000 });
        } catch (navErr) {
            navigationOk = false;
            const bodyContent = await page.evaluate(() => document.body?.innerHTML?.length || 0);
            if (bodyContent < 100) {
                throw new Error(`Navegación falló y la página está vacía: ${navErr.message}`);
            }
            // Si hay contenido suficiente, seguimos adelante con lo que cargó
        }

        // Espera adaptativa (reemplaza el sleep fijo de 2s): confirma que las
        // fuentes web ya pintaron y deja asentar el layout un par de frames,
        // en vez de esperar un tiempo arbitrario que podía sobrar o faltar.
        await page.evaluate(() => (document.fonts ? document.fonts.ready : Promise.resolve()));
        await page.evaluate(() => new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r))));

        // Configurar y generar el PDF
        await page.pdf({
            path: job.pdfPath,
            format: 'A4',
            landscape: !!job.landscape,
            printBackground: true,
            margin: {
                top: '10mm',
                right: '10mm',
                bottom: '10mm',
                left: '10mm'
            },
            timeout: 60000 // Timeout propio para la generación del PDF
        });

        // Verificar que el archivo se creó correctamente
        if (fs.existsSync(job.pdfPath)) {
            const stats = fs.statSync(job.pdfPath);
            if (stats.size < 500) {
                throw new Error(`PDF generado pero sospechosamente pequeño (${stats.size} bytes)`);
            }
            const status = navigationOk ? 'completa' : 'parcial (timeout en navegación, PDF generado con contenido disponible)';
            return { id: job.id, success: true, path: job.pdfPath, status };
        }
        throw new Error('El archivo PDF no se creó en disco');
    } catch (err) {
        return { id: job.id, success: false, error: err.message };
    } finally {
        await page.close().catch(() => {});
    }
}

/**
 * Modo batch: un solo navegador para todo el lote, una pestaña por job.
 * Manifiesto esperado: { sessionId, host, jobs: [{id, url, pdfPath, landscape}] }
 */
async function runBatch(manifestPath) {
    const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
    const { sessionId, host, jobs } = manifest;
    const domain = String(host).split(':')[0];

    const results = [];
    let browser;
    try {
        browser = await puppeteer.launch({
            executablePath: '/var/www/fmt/chrome-linux64/chrome',
            args: CHROME_ARGS
        });

        for (const job of jobs) {
            const r = await renderJob(browser, sessionId, domain, job);
            results.push(r);
        }
    } catch (err) {
        // Fallo global (p.ej. no se pudo lanzar el navegador): marcar como
        // fallidos los jobs que no alcanzaron a procesarse.
        const doneIds = new Set(results.map(r => r.id));
        for (const job of jobs) {
            if (!doneIds.has(job.id)) {
                results.push({ id: job.id, success: false, error: `Error global de Puppeteer: ${err.message}` });
            }
        }
    } finally {
        if (browser) await browser.close().catch(() => {});
    }

    console.log(JSON.stringify({ success: true, results }));
}

/**
 * Modo legado: un solo archivo por invocación (navegador propio, igual que
 * antes). Se conserva para compatibilidad con llamadas existentes fuera del
 * flujo de migración masiva.
 */
async function runSingle(url, outputPath, sessionId, host, landscape) {
    const domain = String(host).split(':')[0];
    let browser;
    try {
        browser = await puppeteer.launch({
            executablePath: '/var/www/fmt/chrome-linux64/chrome',
            args: CHROME_ARGS
        });
        const r = await renderJob(browser, sessionId, domain, { id: 0, url, pdfPath: outputPath, landscape });
        if (r.success) {
            console.log(JSON.stringify({ success: true, path: r.path, status: r.status }));
        } else {
            console.error(JSON.stringify({ success: false, error: r.error }));
        }
    } finally {
        if (browser) await browser.close().catch(() => {});
    }
}

async function main() {
    if (process.argv[2] === '--batch') {
        const manifestPath = process.argv[3];
        if (!manifestPath) {
            console.error(JSON.stringify({ success: false, error: 'Falta la ruta del manifiesto (--batch <archivo.json>)' }));
            process.exit(1);
        }
        await runBatch(manifestPath);
        return;
    }

    if (process.argv.length < 6) {
        console.error(JSON.stringify({ success: false, error: 'Faltan argumentos' }));
        process.exit(1);
    }

    const url = process.argv[2];
    const outputPath = process.argv[3];
    const sessionId = process.argv[4];
    const host = process.argv[5];
    const landscape = process.argv[6] === 'true';
    await runSingle(url, outputPath, sessionId, host, landscape);
}

main();
