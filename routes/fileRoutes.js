const express = require('express');
const router = express.Router();
const pool = require('../db');
const verificarToken = require('../middlewares/authMiddleware');

// Ruta para ver el historial en Drupal
router.get('/ver-historial', verificarToken, async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM api_logs ORDER BY fecha DESC');
    const contenido = result.rows.map(row => 
      `[${row.fecha.toLocaleString()}] - ${row.mensaje}`
    ).join('\n');
    
    res.header("Content-Type", "text/plain");
    res.send(contenido || "Historial vacío en la base de datos.");
  } catch (err) {
    console.error('❌ Error SQL:', err.message);
    res.status(500).send("Error al obtener datos de PostgreSQL");
  }
});

// Ruta de saludo
router.get('/saludar', verificarToken, (req, res) => {
    const hora = new Date().toLocaleTimeString();
    res.json({ 
        mensaje: `Hola ${req.user.user}, conexión exitosa`,
        hora: hora,
        foto: "https://images.unsplash.com/photo-1518770660439-4636190af475?w=500&q=80"
    });
});

module.exports = router;