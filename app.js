require('dotenv').config();
const express = require('express');
const app = express();
const pool = require('./db');

app.use(express.json());

const authRoutes = require('./routes/authRoutes');
const fileRoutes = require('./routes/fileRoutes');

// Inicialización de DB
const crearTabla = async () => {
  try {
    await pool.query(`
      CREATE TABLE IF NOT EXISTS api_logs (
        id SERIAL PRIMARY KEY,
        mensaje TEXT NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      );
    `);
    console.log("✅ Base de datos conectada y tabla lista");
  } catch (err) {
    console.error("❌ Error inicializando DB:", err.message);
  }
};
crearTabla();

// Montaje de rutas
app.use('/auth', authRoutes);
app.use('/files', fileRoutes);

const PORT = process.env.PORT || 3000;
app.listen(PORT, '0.0.0.0', () => {
    console.log(`🚀 API estable en puerto ${PORT}`);
});