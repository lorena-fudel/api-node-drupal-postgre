const fs = require('fs');
const path = require('path');

exports.getHistory = (req, res) => {
    try {
        // Buscamos hola.txt en la raíz (un nivel arriba de controllers)
        const rutaArchivo = path.resolve(__dirname, '../hola.txt');

        if (fs.existsSync(rutaArchivo)) {
            const contenido = fs.readFileSync(rutaArchivo, 'utf8');
            res.header("Content-Type", "text/plain");
            return res.send(contenido);
        } else {
            return res.send("El archivo hola.txt aún no ha sido creado.");
        }
    } catch (err) {
        console.error(err);
        return res.status(500).send("Error al leer el historial.");
    }
};

// Esta función ya no es necesaria para la sincronización, 
// pero la mantenemos por si tu routing la llama.
exports.handleFileAppend = (req, res) => {
    res.send("La sincronización ahora es automática al guardar el archivo introducir-texto.txt");
};

exports.getSaludar = (req, res) => {
    const horaActual = new Date().toLocaleTimeString('es-ES', { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    });

    res.json({ 
        mensaje: "¡Hola Mundo desde la API!",
        hora: horaActual,
        // Imagen aleatoria de tecnología
        foto: "https://images.unsplash.com/photo-1518770660439-4636190af475?w=500&q=80" 
    });
};