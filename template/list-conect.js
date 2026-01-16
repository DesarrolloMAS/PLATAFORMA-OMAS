require('dotenv').config();

const crypto = require('crypto');
global.crypto = crypto;

require('isomorphic-fetch');
const { Client } = require('@microsoft/microsoft-graph-client');
const { ClientSecretCredential } = require('@azure/identity');

async function listSites() {
    console.log('🔍 Buscando TODOS los sitios de SharePoint...\n');

    try {
        const credential = new ClientSecretCredential(
            process.env.TENANT_ID,
            process.env.CLIENT_ID,
            process.env.CLIENT_SECRET
        );

        const client = Client. initWithMiddleware({
            authProvider: {
                getAccessToken: async () => {
                    const token = await credential.getToken(
                        'https://graph.microsoft.com/.default'
                    );
                    return token.token;
                }
            }
        });

        console.log('✅ Autenticación exitosa\n');
        
        // Método 1: Buscar por palabra clave "operaciones"
        console.log('📋 Método 1: Buscando sitios con "operaciones"...\n');
        try {
            const searchResults = await client
                .api('/sites? search=operaciones')
                .get();

            if (searchResults.value && searchResults.value.length > 0) {
                console.log(`✅ Encontrados ${searchResults.value.length} sitios:\n`);
                searchResults.value.forEach((site, index) => {
                    console.log(`${index + 1}. ${site.displayName || site.name}`);
                    console.log(`   URL:     ${site.webUrl}`);
                    console.log(`   Site ID: ${site.id}`);
                    console.log('');
                });
            } else {
                console.log('⚠️  No se encontraron sitios con "operaciones"\n');
            }
        } catch (err) {
            console.log('⚠️  Error buscando sitios:', err.message, '\n');
        }

        // Método 2: Buscar todos los sitios
        console.log('📋 Método 2: Listando TODOS los sitios disponibles...\n');
        try {
            const allSites = await client
                .api('/sites? search=*')
                .top(50)
                .get();

            if (allSites.value && allSites.value.length > 0) {
                console. log('='. repeat(80));
                console.log(`✅ Total de sitios encontrados: ${allSites.value.length}\n`);
                console.log('='.repeat(80));
                
                allSites.value.forEach((site, index) => {
                    console. log(`\n${index + 1}.  ${site.displayName || site. name}`);
                    console.log(`   URL:     ${site.webUrl}`);
                    console.log(`   Site ID: ${site.id}`);
                    
                    if (site.webUrl) {
                        const url = new URL(site.webUrl);
                        console.log(`   Path:    ${url.pathname}`);
                    }
                });
                
                console.log('\n' + '='.repeat(80));
            } else {
                console.log('⚠️  No se encontraron sitios\n');
            }
        } catch (err) {
            console.log('⚠️  Error listando todos los sitios:', err.message, '\n');
        }

        // Método 3: Intentar acceder directamente al sitio raíz
        console.log('\n📋 Método 3: Intentando acceder al sitio raíz de orgmas.. .\n');
        try {
            const rootSite = await client
                . api('/sites/orgmas.sharepoint.com')
                .get();
            
            console.log('✅ Sitio raíz accesible: ');
            console.log(`   Nombre:  ${rootSite.displayName}`);
            console.log(`   URL:     ${rootSite. webUrl}`);
            console.log(`   Site ID: ${rootSite.id}\n`);
        } catch (err) {
            console.log('⚠️  No se puede acceder al sitio raíz:', err.message, '\n');
        }

        // Método 4: Intentar con diferentes variaciones del nombre
        console.log('📋 Método 4: Probando variaciones del nombre...\n');
        
        const variations = [
            '/sites/jnoperaciones',
            '/sites/JNOperaciones',
            '/sites/JN-Operaciones',
            '/sites/jn-operaciones',
            '/sites/Operaciones',
            '/sites/operaciones'
        ];

        for (const path of variations) {
            try {
                const site = await client
                    .api(`/sites/orgmas.sharepoint.com:${path}`)
                    .get();
                
                console. log(`✅ ENCONTRADO: ${path}`);
                console.log(`   Nombre:  ${site. displayName}`);
                console.log(`   URL:     ${site.webUrl}`);
                console.log(`   Site ID: ${site.id}`);
                console.log('');
                console.log('🎯 USA ESTA URL EN TU . ENV:');
                console.log(`   SHAREPOINT_SITE_URL=${site.webUrl}\n`);
                break;
            } catch (err) {
                console.log(`❌ No existe: ${path}`);
            }
        }

    } catch (error) {
        console.error('\n❌ Error general:', error.message);
        
        if (error.statusCode === 403) {
            console.error('\n🔴 Error 403 - Permisos Insuficientes');
            console.error('\nLa aplicación no tiene permisos para listar sitios.');
            console. error('\nSOLUCIÓN: ');
            console.error('1. Ve a Azure Portal → OperacionesUploader');
            console.error('2. API Permissions (Permisos de API)');
            console.error('3. Verifica que tengas estos permisos: ');
            console.error('   - Sites.Read.All (Application)');
            console.error('   - Sites.ReadWrite.All (Application)');
            console.error('4. Clic en "Grant admin consent"');
            console.error('5. Espera 5-10 minutos y vuelve a intentar\n');
        }
    }
}

listSites();