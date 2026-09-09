/**
 * uploader_selective.js
 * ---------------------
 * Sube archivos específicos al SharePoint.
 * Uso: node uploader_selective.js archivo1.pdf archivo2.xlsx ...
 * Los archivos deben ser rutas absolutas dentro de /var/www/fmt/archivos/generados/
 */
require('dotenv').config({ path: __dirname + '/.env', quiet: true });
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
global.crypto = crypto;
// Usamos el fetch nativo de Node 20+

const { Client } = require('@microsoft/microsoft-graph-client');
const { ClientSecretCredential } = require('@azure/identity');

const LOCAL_FOLDER = '/var/www/fmt/archivos/generados/';
const LOG_FILE = '/var/www/fmt/archivos/generados/LOGS/uploader.log';

// Carpeta espejo oculta (los usuarios no la ven): misma jerarquía de
// mes/módulo/sede/archivo que la carpeta pública de PDFs, pero dedicada a
// que la plataforma (sp_reader.js / búsqueda unificada) la lea de vuelta.
// El JSON de cada registro se sube aquí, nunca a la carpeta pública.
const SHAREPOINT_CODIFICACION_PATH = 'Documentos compartidos/Codificación Documentos OMAS';

function logMessage(message) {
    const now = new Date().toISOString();
    fs.appendFileSync(LOG_FILE, `[${now}] ${message}\n`);
}

const logDir = path.dirname(LOG_FILE);
if (!fs.existsSync(logDir)) {
    fs.mkdirSync(logDir, { recursive: true });
}

async function getGraphClient() {
    const credential = new ClientSecretCredential(
        process.env.TENANT_ID,
        process.env.CLIENT_ID,
        process.env.CLIENT_SECRET
    );
    return Client.initWithMiddleware({
        authProvider: {
            getAccessToken: async () => {
                const token = await credential.getToken('https://graph.microsoft.com/.default');
                return token.token;
            }
        }
    });
}

// Mapa de carpetas locales → carpetas legibles en SharePoint
const folderMap = {
    'control_cantidad_pdf': 'Control de Cantidad Producto en Bulto',
    'control_cantidad': 'Control de Cantidad Producto en Bulto',
    'control_cantidad_zs': 'Control de Cantidad ZS',
    'control_cantidad_zs_pdf': 'Control de Cantidad ZS',
    'empaque_v2': 'Control de Empaque V2',
    'envasado_v2': 'Linea de Envasado V2',
    'empaque_pdf': 'Control de Empaque',
    'excelC_M': 'Control de Molienda',
    'excelC_MZS': 'Control de Molienda ZS',
    'excelS_M': 'Solicitudes de Mantenimiento',
    'excelS_MZS': 'Solicitudes de Mantenimiento ZS',
    'pdfsC_M': 'Control de Molienda',
    'pdfsS_M': 'Solicitudes de Mantenimiento',
    'pdfsS_MZS': 'Solicitudes de Mantenimiento ZS',
    'premezclas_v2': 'Premezclas y Harinas Especiales V2',
    'Purga De proceso': 'Purga del Proceso',
    'Purga del proceso_pdf': 'Purga del Proceso',
    'purga_v2': 'Purga del Proceso V2',
    'preparacion_mejorante': 'Preparación de Mejorante',
    'proceso_v2': 'Proceso de Molienda V2',
    'reprocesos_v2': 'Control de Reprocesos V2',
    'molienda': 'Molienda V2',
    'liberaciones_mant': 'Liberaciones Mantenimiento',
    'orden_mantenimiento': 'Orden de Mantenimiento',
    'maquinas_v2': 'Verificación de Máquinas V2',
    'bodegas_v2': 'Inspección de Bodegas V2',
    'Calidad': 'Calidad',
    'HSEQ': 'HSEQ',
    'PNC': 'PNC',
    'termohigrometros': 'Termohigrometros',
    'gestion_subproductos': 'Gestion de Subproductos',
    'tara_seca': 'Tara Seca',
    'cantidad_bulto': 'Control de Cantidad Producto en Bulto',
};

async function uploadFile(client, driveId, filePath, monthFolder) {
    const ext = path.extname(filePath).toLowerCase();
    // JSON => carpeta espejo oculta "Codificación" (la lee la plataforma).
    // PDF/Excel/otros => carpeta pública (la ven los usuarios).
    // .trim() defiende contra espacios accidentales al final del valor en .env,
    // que de otro modo crean una carpeta de destino distinta a la esperada.
    const sharepointFolder = (ext === '.json')
        ? SHAREPOINT_CODIFICACION_PATH
        : (process.env.SHAREPOINT_PDF_PATH || process.env.SHAREPOINT_UPLOAD_PATH).trim();
    let relativePath = path.relative(LOCAL_FOLDER, filePath).replace(/\\/g, '/');

    // Reemplazar el primer segmento de carpeta con el nombre legible
    const parts = relativePath.split('/');
    if (folderMap[parts[0]]) {
        parts[0] = folderMap[parts[0]];
        relativePath = parts.join('/');
    }

    const uploadPath = `${sharepointFolder}/${monthFolder}/${relativePath}`;
    const stats = fs.statSync(filePath);

    // Para archivos grandes (>4MB) usar sesión de subida
    if (stats.size > 4 * 1024 * 1024) {
        // Crear sesión de carga para archivos grandes
        const uploadSession = await client
            .api(`/drives/${driveId}/root:/${uploadPath}:/createUploadSession`)
            .post({
                item: {
                    "@microsoft.graph.conflictBehavior": "replace"
                }
            });

        const fileBuffer = fs.readFileSync(filePath);
        const CHUNK_SIZE = 3.25 * 1024 * 1024; // 3.25 MB
        let offset = 0;

        while (offset < fileBuffer.length) {
            const chunk = fileBuffer.slice(offset, Math.min(offset + CHUNK_SIZE, fileBuffer.length));
            const contentRange = `bytes ${offset}-${offset + chunk.length - 1}/${fileBuffer.length}`;

            await fetch(uploadSession.uploadUrl, {
                method: 'PUT',
                headers: {
                    'Content-Length': chunk.length.toString(),
                    'Content-Range': contentRange,
                },
                body: chunk,
            });

            offset += chunk.length;
        }
    } else {
        const fileBuffer = fs.readFileSync(filePath);
        await client
            .api(`/drives/${driveId}/root:/${uploadPath}:/content`)
            .put(fileBuffer);
    }

    return { path: uploadPath, size: stats.size };
}

// Nº de subidas simultáneas a Graph API. Cada archivo es independiente
// (sesión/URL propia), así que esto es puro paralelismo de red, no de CPU.
// Ajustable vía .env sin tocar código si hace falta afinar.
const UPLOAD_CONCURRENCY = Math.max(1, parseInt(process.env.SHAREPOINT_UPLOAD_CONCURRENCY || '5', 10) || 5);

async function main() {
    // Leer archivos desde argumentos de línea de comandos
    const filePaths = process.argv.slice(2);

    if (filePaths.length === 0) {
        const result = { success: false, error: 'No se especificaron archivos para subir.' };
        console.log(JSON.stringify(result));
        process.exit(1);
    }

    const results = { success: true, uploaded: [], errors: [], total: filePaths.length };

    try {
        const client = await getGraphClient();
        const driveId = process.env.SHAREPOINT_DRIVE_ID;

        // Fallback: mes actual (solo si no se puede extraer del nombre del archivo)
        const now = new Date();
        const defaultMonthFolder = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;

        const concurrency = Math.min(UPLOAD_CONCURRENCY, filePaths.length);
        logMessage(`--- Subida selectiva: ${filePaths.length} archivos (concurrencia=${concurrency}) ---`);

        // Pool de workers: cada worker toma el siguiente índice disponible de
        // la cola compartida hasta agotarla. Reemplaza el "for...await" que
        // subía un archivo a la vez por N subidas en vuelo simultáneamente.
        let nextIndex = 0;
        async function worker() {
            while (nextIndex < filePaths.length) {
                const i = nextIndex++;
                const filePath = filePaths[i];
                const fileName = path.basename(filePath);

                // Validar que el archivo existe
                if (!fs.existsSync(filePath)) {
                    const err = `Archivo no encontrado: ${fileName}`;
                    results.errors.push({ file: fileName, error: err });
                    logMessage(`❌ ${err}`);
                    continue;
                }

                // Determinar la carpeta de mes correcta basándose en la fecha del archivo.
                // 1) Patrón YYYY-MM-DD (ej: Molienda_ZC_2026-04-08.json) → usa ese día.
                // 2) Si no hay día, patrón YYYY-MM suelto (ej: ENV_MOGOLLA_2026-08.json,
                //    EMPAQUE_LOTE_X_2026-08.json) → usa ese mes. Sin este fallback, los
                //    acumuladores mensuales (envasado_v2, empaque_v2, bodegas_v2, bulto)
                //    subidos después de que cambia el mes caían en la carpeta del mes de
                //    subida en vez del mes real del archivo, rompiendo el rastreo por fecha.
                // 3) Si el nombre no trae ninguna fecha, cae al mes actual (comportamiento previo).
                const dateMatch = fileName.match(/(\d{4}-\d{2})-\d{2}/);
                const monthOnlyMatch = !dateMatch ? fileName.match(/(\d{4}-\d{2})/) : null;
                const monthFolder = dateMatch ? dateMatch[1] : (monthOnlyMatch ? monthOnlyMatch[1] : defaultMonthFolder);

                try {
                    const uploadResult = await uploadFile(client, driveId, filePath, monthFolder);
                    results.uploaded.push({ file: fileName, path: uploadResult.path, size: uploadResult.size });
                    logMessage(`✅ Subido: ${uploadResult.path} (${uploadResult.size} bytes) [carpeta: ${monthFolder}]`);
                } catch (err) {
                    const errorMsg = `Error subiendo ${fileName}: ${err.message}`;
                    results.errors.push({ file: fileName, error: errorMsg });
                    logMessage(`❌ ${errorMsg}`);
                }
            }
        }

        await Promise.all(Array.from({ length: concurrency }, () => worker()));

        if (results.errors.length > 0) {
            results.success = results.errors.length < results.total;
        }

        logMessage(`--- Fin subida selectiva: ${results.uploaded.length}/${results.total} exitosos ---`);
    } catch (err) {
        results.success = false;
        results.errors.push({ file: 'GLOBAL', error: `Error de conexión: ${err.message}` });
        logMessage(`❌ Error global: ${err.message}`);
    }

    console.log(JSON.stringify(results));
}

main();
