<?php
// Definir la ruta raíz del proyecto
define('ROOT_PATH', dirname(__DIR__));

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'iot_pets');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Monitoreo de Mascotas');
define('SITE_NAME', 'Sistema de Monitoreo de Mascotas');
define('APP_URL', '/proyecto-2');
define('APP_VERSION', '1.0.0');
define('APP_EMAIL', 'noreply@petcare.com');

// Configuración de correo electrónico
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'tu_correo@gmail.com');
define('MAIL_PASSWORD', 'tu_contraseña');
define('MAIL_FROM_ADDRESS', 'tu_correo@gmail.com');
define('MAIL_FROM_NAME', APP_NAME);

// Configuración de archivos
define('UPLOAD_DIR', ROOT_PATH . '/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);

// Configuración de sesión
define('SESSION_NAME', 'mascotas_iot_session');
define('SESSION_LIFETIME', 7200); // 2 horas
define('SESSION_PATH', '/');
define('SESSION_DOMAIN', '');
define('SESSION_SECURE', false);
define('SESSION_HTTP_ONLY', true);

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Configuración de seguridad
define('HASH_COST', 12); // Costo para password_hash
define('TOKEN_EXPIRY', 3600); // 1 hora para tokens de recuperación
define('JWT_SECRET', 'tu_clave_secreta_muy_segura_aqui'); // Clave secreta para JWT

// Iniciar sesión con configuración personalizada
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params(
        SESSION_LIFETIME,
        SESSION_PATH,
        SESSION_DOMAIN,
        SESSION_SECURE,
        SESSION_HTTP_ONLY
    );
    session_start();
}

// Funciones de utilidad
function redirect($path) {
    $path = ltrim($path, '/');
    header('Location: ' . APP_URL . '/' . $path);
    exit;
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function requireAuth() {
    if (!isAuthenticated()) {
        redirect('/auth/login');
    }
}

function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

function checkPermission($permission) {
    if (!isset($_SESSION['permissions'])) {
        return false;
    }
    return in_array($permission, $_SESSION['permissions']);
}

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Función para validar contraseñas
function validatePassword($password) {
    // Mínimo 8 caracteres, al menos una letra mayúscula, una minúscula y un número
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/proyecto-2/');
}

/**
 * Verifica si el usuario actual tiene un permiso específico
 * @param string $permiso_codigo Código del permiso a verificar
 * @return bool True si el usuario tiene el permiso, False en caso contrario
 */
function verificarPermiso($permiso_codigo) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    // Validar solo por permisos en sesión
    if (isset($_SESSION['permissions']) && is_array($_SESSION['permissions'])) {
        return in_array($permiso_codigo, $_SESSION['permissions']);
    }
    // Fallback: si no hay permisos en sesión, consultar BD (caso de compatibilidad)
    try {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as tiene_permiso 
                FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id_rol 
                JOIN roles_permisos rp ON r.id_rol = rp.rol_id 
                JOIN permisos p ON rp.permiso_id = p.id_permiso 
                WHERE u.id_usuario = ? AND p.codigo = ? AND p.estado = 'activo'";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $permiso_codigo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['tiene_permiso'] > 0;
    } catch (Exception $e) {
        error_log("Error al verificar permiso: " . $e->getMessage());
        return false;
    }
}
?> 