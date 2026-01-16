require('dotenv').config();

const crypto = require('crypto');
global.crypto = crypto;

require('isomorphic-fetch');
const { Client } = require('@microsoft/microsoft-graph-client');
const { ClientSecretCredential } = require('@azure/identity');

async function testConnection() {
    console.log('\n' + '='.repeat(70));
    console.log('🚀 PRUEBA DE CONEXIÓN:  Servidor → Microsoft Graph → SharePoint');
    console.log('='.repeat(70) + '\n');

    console.log('🔧 Configuración detectada:');
    console.log(`   Tenant ID: ${process. env.TENANT_ID?. substring(0, 8)}...`);
    console.log(`   Client ID: ${process.env.CLIENT_ID?.substring(0, 8)}...`);
    console.log(`   Secret:  ${process.env.CLIENT_SECRET ?  '✅ Configurado' : '❌ Falta'}`);
    console.log(`   Site URL: ${process.env. SHAREPOINT_SITE_URL}\n`);

    try {
        console.log('🔄 Paso 1: Autenticando con Azure AD...');
        
        const credential = new ClientSecretCredential(
            process.env.TENANT_ID,
            process.env.CLIENT_ID,
            process.env.CLIENT_SECRET
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
        
        console.log('🔄 Paso 2: Obteniendo información del sitio SharePoint...');

        // Usar el mismo método que funcionó en check_permisos.js
        const siteUrl = process.env.SHAREPOINT_SITE_URL;
        const url = new URL(siteUrl);
        const hostname = url.hostname;
        const sitePath = url.pathname;
        
        console.log(`   Hostname: ${hostname}`);
        console.log(`   Path: ${sitePath}`);
        console.log(`   API:  /sites/${hostname}: ${sitePath}\n`);

        const site = await client
            .api(`/sites/${hostname}:${sitePath}`)
            .get();

        console.log('='.repeat(70));
        console.log('✅ ¡CONEXIÓN EXITOSA!  ✅');
        console.log('='.repeat(70));
        console.log('\n📊 Información del Sitio SharePoint:\n');
        console.log(`   Nombre:           ${site.displayName}`);
        console.log(`   Descripción:     ${site.description || 'Sin descripción'}`);
        console.log(`   URL Web:         ${site.webUrl}`);
        console.log(`   Site ID:         ${site.id}`);
        console.log(`   Creado:          ${new Date(site.createdDateTime).toLocaleDateString('es-ES')}`);
        
        console.log('\n' + '='.repeat(70));
        console.log('💾 Site ID guardado en . env ✅');
        console.log('='.repeat(70));

        console.log('\n🔄 Paso 3: Obteniendo bibliotecas de documentos...\n');
        
        try {
            const drives = await client
                .api(`/sites/${site.id}/drives`)
                .get();

            if (drives.value && drives.value.length > 0) {
                console.log(`📁 Se encontraron ${drives.value. length} bibliotecas:\n`);
                drives.value.forEach((drive, index) => {
                    console.log(`   ${index + 1}.  ${drive.name}`);
                    console.log(`      ID:    ${drive.id}`);
                    console.log(`      Tipo: ${drive.driveType}`);
                    console.log(`      URL:  ${drive.webUrl}\n`);
                });
            } else {
                console.log('⚠️  No se encontraron bibliotecas');
            }
        } catch (driveError) {
            console. log('⚠️  No se pudieron obtener las bibliotecas');
            console. log(`   Error: ${driveError.message}`);
        }

        console.log('🔄 Paso 4: Obteniendo listas disponibles...\n');
        
        try {
            const lists = await client
                .api(`/sites/${site.id}/lists`)
                .select('displayName,id,list')
                .top(10)
                .get();

            if (lists.value && lists.value.length > 0) {
                console.log(`📋 Se encontraron ${lists.value.length} listas:\n`);
                lists.value.forEach((list, index) => {
                    console.log(`   ${index + 1}. ${list.displayName}`);
                    console.log(`      ID: ${list.id}`);
                    console.log(`      Template: ${list.list?. template || 'N/A'}\n`);
                });
            } else {
                console.log('⚠️  No se encontraron listas en este sitio');
            }
        } catch (listError) {
            console.log('⚠️  No se pudieron obtener las listas');
            console. log(`   Error: ${listError.message}`);
        }

        console.log('\n' + '='.repeat(70));
        console.log('🎉 PRUEBA COMPLETADA EXITOSAMENTE');
        console.log('='. repeat(70));
        console.log('\n✅ Tu servidor está correctamente conectado a SharePoint');
        console.log('✅ Puedes proceder a implementar las funcionalidades\n');
        
        console.log('📝 SIGUIENTE PASO: ');
        console.log('   Ahora puedes: ');
        console.log('   - Subir archivos a las bibliotecas');
        console.log('   - Leer y escribir en las listas');
        console.log('   - Crear carpetas y organizar documentos\n');

        return site;

    } catch (error) {
        console.error('\n' + '='.repeat(70));
        console.error('❌ ERROR EN LA CONEXIÓN');
        console.error('='. repeat(70) + '\n');
        
        console.error('Detalles del error:');
        console.error(`  Mensaje: ${error.message}`);
        console.error(`  Código:   ${error.statusCode || error.code}`);
        
        if (error.body) {
            console.error(`  Body:    ${error.body}`);
        }
        
        process.exit(1);
    }
}

testConnection();