const jwt = require('jsonwebtoken');

exports.login = (req, res) => {
    const { username, password } = req.body;

    // Credenciales de prueba
    if (username === 'admin' && password === '1234') {
        const user = { id: 1, name: "Admin-Drupal" };
        
        // Usamos la clave del .env
        const token = jwt.sign({ user }, process.env.JWT_SECRET, { expiresIn: '1h' });
        
        return res.json({ token });
    }

    return res.status(401).json({ message: "Credenciales incorrectas" });
};