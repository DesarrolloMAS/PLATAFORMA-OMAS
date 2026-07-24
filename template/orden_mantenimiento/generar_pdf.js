/**
 * generar_pdf.js
 * ---------------------
 * Genera PDFs reales (motor de impresión de Chrome vía Puppeteer) a partir
 * de las vistas HTML de visor.php, en vez de rasterizarlas con html2pdf.js.
 * Esto garantiza que la paginación del PDF sea idéntica a la de Ctrl+P,
 * ya que ambos usan el mismo motor de render/impresión.
 *
 * Un solo navegador se lanza para todo el lote (una pestaña por documento),
 * en vez de uno nuevo por archivo.
 *
 * Uso: node generar_pdf.js --batch <manifiesto.json>
 * Manifiesto: { sessionId, host, jobs: [{ id, url, pdfPath }, ...] }
 */
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

async function renderJob(browser, sessionId, domain, job) {
    const page = await browser.newPage();
    try {
        page.setDefaultNavigationTimeout(90000);
        page.setDefaultTimeout(90000);

        // Inyectar la sesión de PHP para que el visor no pida login
        await page.setCookie({
            name: 'PHPSESSID',
            value: sessionId,
            domain: domain,
            path: '/'
        });

        // Modo impresión: activa las mismas reglas @media print que usa Ctrl+P
        // (incluida la que oculta los controles flotantes con clase .no-print)
        await page.emulateMediaType('print');

        let navigationOk = true;
        try {
            await page.goto(job.url, { waitUntil: ['load', 'networkidle0'], timeout: 90000 });
        } catch (navErr) {
            navigationOk = false;
            const bodyContent = await page.evaluate(() => document.body?.innerHTML?.length || 0);
            if (bodyContent < 100) {
                throw new Error(`Navegación falló y la página está vacía: ${navErr.message}`);
            }
        }

        // Espera adaptativa: confirma que las fuentes ya pintaron y deja
        // asentar el layout un par de frames, en vez de un tiempo fijo.
        await page.evaluate(() => (document.fonts ? document.fonts.ready : Promise.resolve()));
        await page.evaluate(() => new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r))));

        await page.pdf({
            path: job.pdfPath,
            format: 'Letter',
            printBackground: true,
            margin: { top: '0.3in', right: '0.3in', bottom: '0.3in', left: '0.3in' },
            timeout: 60000
        });

        if (fs.existsSync(job.pdfPath)) {
            const stats = fs.statSync(job.pdfPath);
            if (stats.size < 500) {
                throw new Error(`PDF generado pero sospechosamente pequeño (${stats.size} bytes)`);
            }
            return { id: job.id, success: true, path: job.pdfPath, status: navigationOk ? 'completa' : 'parcial' };
        }
        throw new Error('El archivo PDF no se creó en disco');
    } catch (err) {
        return { id: job.id, success: false, error: err.message };
    } finally {
        await page.close().catch(() => {});
    }
}

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
            results.push(await renderJob(browser, sessionId, domain, job));
        }
    } catch (err) {
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

const manifestPath = process.argv[2] === '--batch' ? process.argv[3] : null;
if (!manifestPath) {
    console.error(JSON.stringify({ success: false, error: 'Uso: node generar_pdf.js --batch <manifiesto.json>' }));
    process.exit(1);
}
runBatch(manifestPath);
