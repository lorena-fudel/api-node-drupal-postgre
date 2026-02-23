v2
🚀 Drupal 10 & Node.js API Gateway IntegrationEste proyecto es un ecosistema de microservicios orquestado con Docker, que integra un CMS Drupal 10, una API REST en Node.js, una base de datos PostgreSQL y un cliente externo de gestión de activos (GLPI).🏗️ Arquitectura del SistemaEl proyecto se divide en cuatro contenedores principales interconectados en una red aislada:Drupal 10 (Frontend/CMS): Actúa como interfaz principal y hub de información. Consumo de APIs internas y externas mediante Guzzle.Node.js API (Backend): Gestiona la lógica de negocio, autenticación JWT y lectura de archivos de sistema.PostgreSQL 15: Motor de persistencia para los datos de Drupal y logs de la API.pgAdmin 4: Interfaz gráfica para la administración y monitorización de la base de datos.🛠️ Funcionalidades Principales🔐 Seguridad y AutenticaciónJWT (JSON Web Tokens): Flujo de autenticación completo para proteger rutas sensibles en la API de Node.js.Variables de Entorno: Gestión segura de credenciales (App-Tokens, User-Tokens, Secret Keys) mediante archivos .env inyectados a través de Docker Compose.🔌 Integraciones Externas (GLPI API)Handshake de Sesión: Implementación del ciclo initSession -> Request -> killSession.Búsqueda Parametrizada: Localización de usuarios mediante email y recuperación de tareas técnicas (TicketTask) por ID de usuario.Mapeo Dinámico: Uso de metadatos (listSearchOptions) para sincronizar IDs de campos entre sistemas.📂 Gestión de ArchivosSincronización en tiempo real entre archivos planos del servidor (.txt) y la interfaz de Drupal mediante peticiones internas.🚀 Instalación y DespliegueClonar el repositorio:Bashgit clone https://github.com/tu-usuario/tu-proyecto.git
cd tu-proyecto
Configurar variables de entorno:Crea un archivo .env en la raíz con la siguiente estructura:Fragmento de código# API & Auth
JWT_SECRET=tu_clave_secreta

# Database
POSTGRES_DB=nombre de la base de datos
POSTGRES_USER=usuario
POSTGRES_PASSWORD=contraseña

# GLPI Integration
GLPI_BASE_URL=direccion url
GLPI_APP_TOKEN=tu_app_token
GLPI_USER_TOKEN=tu_user_token
Levantar el stack con Docker:Bashdocker-compose up -d

📍 Endpoints de la APIInternos (Node.js)RutaMétodoDescripción/auth/loginPOSTGeneración de Token JWT./api/ver-historialGETLectura de introducir-texto.txt.Externos (Drupal ↔ GLPI)RutaDescripción/api/glpi/buscar/{email}Busca un perfil de usuario en el servidor GLPI./api/glpi/tareas/{id}Lista las tareas activas asignadas a un técnico.🛠️ Tecnologías UtilizadasLenguajes: PHP 8.4, JavaScript (Node.js), SQL.Frameworks: Drupal 10, Express.js.Herramientas: Docker, Guzzle HTTP, Postman, pgAdmin, JWT.📝 AutorLorena Fumero - Desarrollo e Integración - TuGitHub