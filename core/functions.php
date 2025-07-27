<?php
/**
 * Funciones de utilidad para el sistema
 */

/**
 * Verifica si el usuario actual tiene un permiso específico
 * @param string $permiso Nombre del permiso a verificar
 * @return bool True si tiene el permiso, False en caso contrario
 */
function verificarPermiso($permiso) {
    // Si no hay sesión activa, no tiene permisos
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['permissions'])) {
        return false;
    }
    
    // Verificar si el permiso está en la lista de permisos del usuario
    return in_array($permiso, $_SESSION['permissions']);
}

/**
 * Verifica si el usuario actual tiene al menos uno de los permisos especificados
 * @param array $permisos Array de permisos a verificar
 * @return bool True si tiene al menos uno de los permisos
 */
function verificarCualquierPermiso($permisos) {
    if (!is_array($permisos)) {
        return verificarPermiso($permisos);
    }
    
    foreach ($permisos as $permiso) {
        if (verificarPermiso($permiso)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Verifica si el usuario actual tiene todos los permisos especificados
 * @param array $permisos Array de permisos a verificar
 * @return bool True si tiene todos los permisos
 */
function verificarTodosPermisos($permisos) {
    if (!is_array($permisos)) {
        return verificarPermiso($permisos);
    }
    
    foreach ($permisos as $permiso) {
        if (!verificarPermiso($permiso)) {
            return false;
        }
    }
    
    return true;
}

/**
 * Obtiene el nombre del rol del usuario actual
 * @return string Nombre del rol o 'Sin rol' si no está definido
 */
function obtenerRolUsuario() {
    return $_SESSION['rol_nombre'] ?? 'Sin rol';
}

/**
 * Obtiene el ID del rol del usuario actual
 * @return int ID del rol o 0 si no está definido
 */
function obtenerRolId() {
    return $_SESSION['user_role'] ?? 0;
}

/**
 * Verifica si el usuario actual es administrador
 * @return bool True si es administrador
 */
function esAdministrador() {
    $rolId = obtenerRolId();
    return $rolId == 1 || $rolId == 3; // Administrador o Super Administrador
}

/**
 * Verifica si el usuario actual es super administrador
 * @return bool True si es super administrador
 */
function esSuperAdministrador() {
    return obtenerRolId() == 3;
}

/**
 * Formatea una fecha para mostrar
 * @param string $fecha Fecha en formato MySQL
 * @param string $formato Formato de salida (por defecto 'd/m/Y H:i')
 * @return string Fecha formateada
 */
function formatearFecha($fecha, $formato = 'd/m/Y H:i') {
    if (empty($fecha)) {
        return 'No disponible';
    }
    
    $timestamp = strtotime($fecha);
    if ($timestamp === false) {
        return 'Fecha inválida';
    }
    
    return date($formato, $timestamp);
}

/**
 * Genera un token CSRF
 * @return string Token generado
 */
function generarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica un token CSRF
 * @param string $token Token a verificar
 * @return bool True si el token es válido
 */
function verificarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitiza una cadena para mostrar en HTML
 * @param string $texto Texto a sanitizar
 * @return string Texto sanitizado
 */
function sanitizarTexto($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige a una URL
 * @param string $url URL a la que redirigir
 */
function redirigir($url) {
    header('Location: ' . BASE_URL . $url);
    exit;
}

/**
 * Muestra un mensaje de error y redirige
 * @param string $mensaje Mensaje de error
 * @param string $url URL de redirección (opcional)
 */
function mostrarError($mensaje, $url = null) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => $mensaje
    ];
    
    if ($url) {
        redirigir($url);
    }
}

/**
 * Muestra un mensaje de éxito y redirige
 * @param string $mensaje Mensaje de éxito
 * @param string $url URL de redirección (opcional)
 */
function mostrarExito($mensaje, $url = null) {
    $_SESSION['message'] = [
        'type' => 'success',
        'text' => $mensaje
    ];
    
    if ($url) {
        redirigir($url);
    }
}
?> 