require('dotenv').config();

const crypto = require('crypto');
global.crypto = crypto;

require('isomorphic-fetch');
const { Client } = require('@microsoft/microsoft-graph-client');
const { ClientSecretCredential } = require('@azure/identity');

async function checkPermissions() {
    console.log('🔐 VERIFICACIÓN DE PERMISOS DE LA APLICACIÓN\n');
    console.log('='.repeat(70));

    try {
        const credential = new ClientSecretCredential(
            process.env. TENANT_ID,
            process. env.CLIENT_ID,
            process.env. CLIENT_SECRET
        );

        const client = Client.initWithMiddleware({
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
        console.log('='.repeat(70));
        console.log('PRUEBA 1: Listar sitios (requiere Sites.Read.All)');
        console.log('='.repeat(70));

        try {
            const sites = await client
                .api('/sites?search=*')
                .top(5)
                .get();
            
            console.log(`✅ ÉXITO - Puede listar sitios (${sites.value?. length || 0} encontrados)`);
            if (sites.value && sites.value.length > 0) {
                sites.value.forEach((site, i) => {
                    console. log(`   ${i+1}. ${site.displayName} - ${site.webUrl}`);
                });
            }
        } catch (err) {
            console.log(`❌ FALLO - No puede listar sitios`);
            console.log(`   Error: ${err.message}`);
            console.log(`   Código: ${err.statusCode}`);
        }

        console.log('\n' + '='.repeat(70));
        console.log('PRUEBA 2: Buscar sitio específico "jnoperaciones"');
        console.log('='.repeat(70));

        try {
            const searchResults = await client
                .api('/sites?search=jnoperaciones')
                .get();
            
            if (searchResults.value && searchResults.value.length > 0) {
                console.log(`✅ ÉXITO - Sitio encontrado en búsqueda`);
                searchResults.value.forEach((site, i) => {
                    console.log(`   ${i+1}. ${site.displayName}`);
                    console.log(`      URL: ${site.webUrl}`);
                    console.log(`      ID:  ${site.id}`);
                });
                
                // Guardar el ID para pruebas siguientes
                window.testSiteId = searchResults.value[0].id;
                window.testSiteUrl = searchResults.value[0].webUrl;
            } else {
                console.log(`⚠️  Búsqueda exitosa pero sitio no encontrado`);
            }
        } catch (err) {
            console.log(`❌ FALLO - No puede buscar sitios`);
            console.log(`   Error: ${err.message}`);
            console.log(`   Código: ${err.statusCode}`);
        }

        console.log('\n' + '='.repeat(70));
        console.log('PRUEBA 3: Acceder al sitio por ruta (orgmas.sharepoint.com:/sites/jnoperaciones)');
        console.log('='. repeat(70));

        try {
            const site = await client
                .api('/sites/orgmas.sharepoint.com:/sites/jnoperaciones')
                .get();
            
            console.log(`✅ ÉXITO - Puede acceder al sitio por ruta`);
            console.log(`   Nombre: ${site.displayName}`);
            console.log(`   ID:  ${site.id}`);
        } catch (err) {
            console.log(`❌ FALLO - No puede acceder por ruta`);
            console.log(`   Error: ${err.message}`);
            console.log(`   Código: ${err.statusCode}`);
            
            if (err.statusCode === 404) {
                console.log(`   💡 ERROR 404 = El sitio existe pero la app NO tiene permiso`);
            } else if (err.statusCode === 403) {
                console. log(`   💡 ERROR 403 = Permisos denegados explícitamente`);
            }
        }

        console.log('\n' + '='.repeat(70));
        console.log('PRUEBA 4: Acceder al sitio raíz (orgmas.sharepoint.com)');
        console.log('='.repeat(70));

        try {
            const rootSite = await client
                . api('/sites/orgmas.sharepoint.com')
                .get();
            
            console.log(`✅ ÉXITO - Puede acceder al sitio raíz`);
            console.log(`   Nombre: ${rootSite.displayName}`);
        } catch (err) {
            console.log(`❌ FALLO - No puede acceder al sitio raíz`);
            console.log(`   Error: ${err.message}`);
            console.log(`   Código: ${err.statusCode}`);
        }

        console.log('\n' + '='.repeat(70));
        console.log('PRUEBA 5: Buscar y acceder por Site ID directamente');
        console.log('='.repeat(70));

        try {
            // Primero buscar
            const searchResults = await client
                .api('/sites?search=jnoperaciones')
                .get();

            if (searchResults.value && searchResults.value.length > 0) {
                const siteId = searchResults.value[0].id;
                console.log(`   Intentando acceder con ID: ${siteId}`);
                
                // Intentar acceder con el ID
                const siteById = await client
                    .api(`/sites/${siteId}`)
                    .get();
                
                console.log(`✅ ÉXITO - Puede acceder por Site ID`);
                console.log(`   Nombre: ${siteById.displayName}`);
                console.log(`   URL: ${siteById.webUrl}`);
                
                // Intentar acceder a drives
                console.log(`\n   Probando acceso a bibliotecas... `);
                try {
                    const drives = await client
                        .api(`/sites/${siteId}/drives`)
                        .get();
                    console.log(`   ✅ Puede acceder a bibliotecas (${drives.value?.length || 0} encontradas)`);
                } catch (driveErr) {
                    console.log(`   ❌ NO puede acceder a bibliotecas`);
                    console.log(`      Error: ${driveErr.message}`);
                }
                
                // Intentar acceder a listas
                console.log(`\n   Probando acceso a listas...`);
                try {
                    const lists = await client
                        .api(`/sites/${siteId}/lists`)
                        .get();
                    console.log(`   ✅ Puede acceder a listas (${lists. value?.length || 0} encontradas)`);
                } catch (listErr) {
                    console.log(`   ❌ NO puede acceder a listas`);
                    console.log(`      Error: ${listErr.message}`);
                }
                
            } else {
                console.log(`⚠️  No se encontró el sitio en búsqueda`);
            }
        } catch (err) {
            console.log(`❌ FALLO general en prueba`);
            console.log(`   Error: ${err.message}`);
        }

        console.log('\n' + '='.repeat(70));
        console.log('📊 DIAGNÓSTICO FINAL');
        console.log('='. repeat(70));
        console.log('\nSi puedes BUSCAR pero NO ACCEDER al sitio: ');
        console.log('  → El problema ES de permisos');
        console.log('  → La app tiene Sites.Read.All pero no acceso al sitio específico');
        console.log('\nSoluciones posibles:');
        console.log('  1. Usar Sites.Selected y dar permiso explícito al sitio');
        console.log('  2. Verificar que Admin Consent está otorgado');
        console.log('  3. El sitio puede tener restricciones adicionales');
        console.log('='.repeat(70));

    } catch (error) {
        console.error('\n❌ Error general:', error.message);
    }
}

checkPermissions();