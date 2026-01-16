require('dotenv').config();
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
global.crypto = crypto;
require('isomorphic-fetch');
const { Client } = require('@microsoft/microsoft-graph-client');
const { ClientSecretCredential } = require('@azure/identity');
//========================================//
// Configuracion la carpeta local a barrer
//========================================//
const LOCAL_FOLDER = '/var/www/fmt/archivos/generados/';
const LOG_FILE = '/var/www/fmt/archivos/generados/LOGS/uploader.log';
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
const now = new Date();
const monthFolder = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`; 
const folderMap = {
    'control_cantidad_pdf': 'Control de Cantidad Producto en Bulto',
    'empaque_pdf': 'Control de Empaque',
    'envasado_pdf': 'Linea de Envasado',
    'pdfsC_M': 'Control de Molienda',
    'pdfsINS': 'Inspecciones de Bodega',
    'pdfsS_M': 'Solicitude de Mantenimiento',
    'pdfsS_MZS': 'Solicitudes de Mantenimiento de ZS',
    'premezclas_pdfs': 'Premezclas y Harinas Especiales',
    'preoces_molienda_pdf': 'Proceso de Molienda',
    'Purga del proceso_pdf': 'Purga del Proceso',
    'reprocesos_zc_pdf': 'Control de Reprocesos',
    'verificaciones': 'Verificaciones de Maquinas',

}; 
async function uploadFile(client, driveId, filePath, monthFolder) {
    const sharepointFolder = process.env.SHAREPOINT_UPLOAD_PATH;
    // Calcula la ruta relativa del archivo respecto a la carpeta base
    let relativePath = path.relative(LOCAL_FOLDER, filePath).replace(/\\/g, '/');

    const parts = relativePath.split('/');
    if (folderMap[parts[0]]) {
        parts[0] = folderMap[parts[0]];
        relativePath = parts.join('/');
    }
    
    const uploadPath = `${sharepointFolder}/${monthFolder}/${relativePath}`;
    const fileStream = fs.createReadStream(filePath);
    const stats = fs.statSync(filePath);
    const fileBuffer = fs.readFileSync(filePath);

    

    await client
        .api(`/drives/${driveId}/root:/${uploadPath}:/content`)
        .put(fileBuffer);

    const msg = `✅ Subido: ${uploadPath} (${stats.size} bytes)`;
    console.log(msg);
    logMessage(msg);
}
function getAllFiles(dirPath, arrayOfFiles = []) {
    const files = fs.readdirSync(dirPath);
    files.forEach(file => {
        const filePath = path.join(dirPath, file);
        if (fs.statSync(filePath).isDirectory()) {
            getAllFiles(filePath, arrayOfFiles);
        } else {
            arrayOfFiles.push(filePath);
        }
    });
    return arrayOfFiles;
}

async function main() {
    const client = await getGraphClient();
    const driveId = process.env.SHAREPOINT_DRIVE_ID;
    const sharepointFolder = process.env.SHAREPOINT_UPLOAD_PATH;
    logMessage('--- Inicio de ejecución del uploader ---');
    //Filtro extensiones permitidas
    const allowedExtensions = ['.pdf'];

    // Obtiene todos los archivos, incluyendo subcarpetas
    const files = getAllFiles(LOCAL_FOLDER);

    const now = new Date();
    const monthFolder = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;


    for (const filePath of files) {
        const fileName = path.basename(filePath);
        const ext = path.extname(fileName).toLowerCase();

        if (!allowedExtensions.includes(ext)) {
            const msg = `⏩ Ignorado: ${fileName} (tipo no permitido)`;
            console.log(msg);
            logMessage(msg);
            continue;
        }
        
        const msg = `Procesando: ${fileName}`;
        console.log(msg);
        logMessage(msg);
        try {
            await uploadFile(client, driveId, filePath, monthFolder);
        } catch (err) {
            const errorMsg = `❌ Error subiendo ${fileName}: ${err.message}`;
            console.error(errorMsg);
            logMessage(errorMsg);
        }
    }
    const endMsg = '🎉 ¡Todos los archivos han sido procesados!';
    console.log(endMsg);
    logMessage(endMsg);
    logMessage('--- Fin de ejecución del uploader ---');
}

main();