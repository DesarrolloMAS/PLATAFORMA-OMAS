const cron = require('node-cron');
const { exec } = require('child_process');

// Ejecuta el script el primer día de cada mes a las 00:00
cron.schedule('0 0 1 * *', () => {
    exec('node /var/www/fmt/template/uploader.js', (error, stdout, stderr) => {
        if (error) {
            console.error(`Error: ${error.message}`);
            return;
        }
        if (stderr) {
            console.error(`stderr: ${stderr}`);
            return;
        }
        console.log(`stdout: ${stdout}`);
    });
});