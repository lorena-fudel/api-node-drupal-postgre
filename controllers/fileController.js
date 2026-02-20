const fs = require('fs');
const path = require('path');

exports.getHistory = (req, res) => {
    try {
        // USO DE REQ: Verificamos si el middleware nos pasó los datos del usuario
        console.log(`📋 Historial solicitado por el usuario: ${req.user ? req.user.user : 'Desconocido'}`);

        const rutaArchivo = path.resolve(__dirname, '../hola.txt');

        if (fs.existsSync(rutaArchivo)) {
            const contenido = fs.readFileSync(rutaArchivo, 'utf8');
            res.header("Content-Type", "text/plain");
            return res.send(contenido);
        } else {
            return res.send("El historial está vacío o el archivo no existe.");
        }
    } catch (err) {
        console.error('❌ Error en getHistory:', err.message);
        return res.status(500).send("Error interno al procesar el historial.");
    }
};

exports.getSaludar = (req, res) => {
    // USO DE REQ: Saludamos de forma personalizada usando los datos del JWT
    const nombreUsuario = req.user ? req.user.user : "Invitado";
    
    const horaActual = new Date().toLocaleTimeString('es-ES', { 
        hour: '2-digit', minute: '2-digit', second: '2-digit' 
    });

    res.json({ 
        mensaje: `¡Hola ${nombreUsuario}, bienvenido a la API!`,
        hora: horaActual,
        foto: "https://images.unsplash.com/photo-1518770660439-4636190af475?w=500&q=80" 
    });
};