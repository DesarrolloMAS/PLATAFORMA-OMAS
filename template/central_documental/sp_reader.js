/**
 * sp_reader.js
 * ---------------------
 * Lee archivos JSON desde SharePoint vía Microsoft Graph API.
 * Uso:
 *   node sp_reader.js list "Documentos compartidos/Codificación Documentos OMAS/2026-05/Molienda V2"
 *   node sp_reader.js read "Documentos compartidos/Codificación Documentos OMAS/2026-05/Molienda V2/Molienda_ZC_2026-05-15.json"
 */
require('dotenv').config({ path: __dirname + '/../.env', quiet: true });
const crypto = require('crypto');
global.crypto = crypto;

const { Client } = require('@microsoft/microsoft-graph-client');
const { ClientSecretCredential } = require('@azure/identity');

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

async function listFolder(client, driveId, folderPath) {
    try {
        const response = await client
            .api(`/drives/${driveId}/root:/${folderPath}:/children`)
            .select('name,size,lastModifiedDateTime,file,folder')
            .get();

        const items = (response.value || []).map(item => ({
            name: item.name,
            size: item.size,
            lastModified: item.lastModifiedDateTime,
            isFolder: !!item.folder,
            isFile: !!item.file
        }));

        console.log(JSON.stringify({ success: true, items }));
    } catch (err) {
        // Si la carpeta no existe, devolver lista vacía en vez de error
        if (err.statusCode === 404) {
            console.log(JSON.stringify({ success: true, items: [] }));
        } else {
            console.log(JSON.stringify({ success: false, error: err.message }));
        }
    }
}

async function readFile(client, driveId, filePath) {
    try {
        // Obtener el contenido raw del archivo
        const response = await client
            .api(`/drives/${driveId}/root:/${filePath}:/content`)
            .getStream();

        // Leer el stream y concatenar
        const chunks = [];
        for await (const chunk of response) {
            chunks.push(chunk);
        }
        const content = Buffer.concat(chunks).toString('utf-8');

        // Parsear el JSON para validarlo
        const parsed = JSON.parse(content);
        console.log(JSON.stringify({ success: true, data: parsed }));
    } catch (err) {
        console.log(JSON.stringify({ success: false, error: err.message }));
    }
}

async function main() {
    const action = process.argv[2];
    const targetPath = process.argv[3];

    if (!action || !targetPath) {
        console.log(JSON.stringify({ success: false, error: 'Faltan argumentos: action y path son requeridos.' }));
        process.exit(1);
    }

    const driveId = process.env.SHAREPOINT_DRIVE_ID;
    if (!driveId) {
        console.log(JSON.stringify({ success: false, error: 'SHAREPOINT_DRIVE_ID no está configurado en .env' }));
        process.exit(1);
    }

    const client = await getGraphClient();

    if (action === 'list') {
        await listFolder(client, driveId, targetPath);
    } else if (action === 'read') {
        await readFile(client, driveId, targetPath);
    } else {
        console.log(JSON.stringify({ success: false, error: `Acción desconocida: "${action}". Use "list" o "read".` }));
        process.exit(1);
    }
}

main().catch(err => {
    console.log(JSON.stringify({ success: false, error: 'Error fatal: ' + err.message }));
    process.exit(1);
});
