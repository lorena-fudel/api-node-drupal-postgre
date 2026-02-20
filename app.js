require('dotenv').config();
const express = require('express');
const fs = require('fs');
const path = require('path');
const chokidar = require('chokidar');

const app = express();

// Importar rutas
const authRoutes = require('./routes/authRoutes');
const fileRoutes = require('./routes/fileRoutes');

app.use(express.json());

// --- LÓGICA DE SINCRONIZACIÓN AUTOMÁTICA ---
const origen = path.resolve(__dirname, 'introducir-texto.txt');
const destino = path.resolve(__dirname, 'hola.txt');

// Vigilante de archivos
chokidar.watch(origen, { 
    usePolling: true, 
    interval: 100 
}).on('change', () => {
    console.log('📝 Cambio detectado en introducir-texto.txt...');
    try {
        const contenido = fs.readFileSync(origen, 'utf8');
        // Escribimos en hola.txt lo que acabas de guardar
        fs.writeFileSync(destino, contenido, 'utf8');
        console.log('✅ hola.txt actualizado automáticamente');
    } catch (err) {
        console.error('❌ Error al sincronizar:', err);
    }
});

// Uso de rutas con prefijos
app.use('/auth', authRoutes);
app.use('/files', fileRoutes);

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`🚀 API corriendo en puerto ${PORT}`);
    console.log(`👀 Vigilando: ${origen}`);
});