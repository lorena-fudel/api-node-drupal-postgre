const express = require('express');
const router = express.Router();
const fileController = require('../controllers/fileController'); // Sube un nivel a controllers 
const verifyToken = require('../middlewares/authMiddleware');  // Sube un nivel a middlewares 

// RUTA 1: Añadir texto al historial
// URL en Postman: GET http://localhost:3000/files/hola
router.get('/hola', verifyToken, fileController.handleFileAppend);

// RUTA 2: Solo ver el contenido del archivo
// URL en Postman: GET http://localhost:3000/files/ver-historial
router.get('/ver-historial', verifyToken, fileController.getHistory);

//el hola mundo
router.get('/saludar', verifyToken, fileController.getSaludar);

module.exports = router;

