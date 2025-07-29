<?php
class AuthController extends Controller {
    private $usuarioModel;
    protected $requireAuth = false; // No requiere autenticación

    public function __construct() {
        parent::__construct();
        
        // Log de inicialización del controlador
        error_log('[' . date('Y-m-d H:i:s') . '] AuthController inicializado');
        
        try {
            $this->usuarioModel = new UsuarioModel();
            error_log('[' . date('Y-m-d H:i:s') . '] UsuarioModel creado exitosamente');
        } catch (Exception $e) {
            error_log('[' . date('Y-m-d H:i:s') . '] Error al crear UsuarioModel: ' . $e->getMessage());
        }
        
        // Cargar modelo de roles para permisos
        require_once 'models/Rol.php';
    }

    public function loginAction() {
        // Si ya está autenticado, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->sanitizeInput($_POST['email']);
            $password = $_POST['password'];

            // Log del intento de login
            error_log('[' . date('Y-m-d H:i:s') . '] Intento de login - Email: ' . $email);

            try {
                $usuario = $this->usuarioModel->getUsuarioByEmail($email);

                // Log del resultado de búsqueda de usuario
                if ($usuario) {
                    error_log('[' . date('Y-m-d H:i:s') . '] Usuario encontrado - ID: ' . $usuario['id_usuario'] . ', Nombre: ' . $usuario['nombre']);
                    
                    // Log de la verificación de contraseña
                    error_log('[' . date('Y-m-d H:i:s') . '] Verificando contraseña para usuario: ' . $usuario['nombre']);
                    error_log('[' . date('Y-m-d H:i:s') . '] Hash en BD: ' . $usuario['password']);
                    error_log('[' . date('Y-m-d H:i:s') . '] Contraseña ingresada: ' . $password);
                    
                    $passwordValid = password_verify($password, $usuario['password']);
                    error_log('[' . date('Y-m-d H:i:s') . '] Resultado password_verify: ' . ($passwordValid ? 'TRUE' : 'FALSE'));
                    
                    if ($passwordValid) {
                        $_SESSION['user_id'] = $usuario['id_usuario'];
                        $_SESSION['user_name'] = $usuario['nombre'];
                        $_SESSION['user_role'] = $usuario['rol_id'];
                        $_SESSION['user'] = $usuario;
                        $_SESSION['rol_nombre'] = $this->getRolNombre($usuario['rol_id']);

                        // Establecer permisos reales desde la base de datos
                        $rolModel = new Rol();
                        $permisosRol = $rolModel->getPermisosPorRol($usuario['rol_id']);
                        
                        // Extraer solo los nombres de los permisos
                        $permisosNombres = [];
                        foreach ($permisosRol as $permiso) {
                            $permisosNombres[] = $permiso['nombre'];
                        }
                        
                        $_SESSION['permissions'] = $permisosNombres;

                        // Limpiar permisos temporales si existen
                        if (isset($_SESSION['temp_permissions'])) {
                            unset($_SESSION['temp_permissions']);
                        }

                        $this->usuarioModel->registrarInicioSesion($usuario['id_usuario']);

                        // Verificar si el usuario tiene permiso para ver dashboard
                        if (verificarPermiso('ver_dashboard')) {
                            $this->redirect('dashboard');
                        } else {
                            // Si no tiene permiso de dashboard, redirigir a monitor
                            header('Location: ' . APP_URL . '/monitor');
                            exit;
                        }
                    } else {
                        // Log detallado del fallo de autenticación
                        error_log('[' . date('Y-m-d H:i:s') . '] Contraseña incorrecta para usuario: ' . $usuario['nombre'] . ' (ID: ' . $usuario['id_usuario'] . ')');
                        error_log('[' . date('Y-m-d H:i:s') . '] Hash almacenado: ' . substr($usuario['password'], 0, 20) . '...');
                        throw new Exception('Credenciales inválidas');
                    }
                } else {
                    error_log('[' . date('Y-m-d H:i:s') . '] Usuario NO encontrado para email: ' . $email);
                    throw new Exception('Credenciales inválidas');
                }
            } catch (Exception $e) {
                // Log del error completo
                error_log('[' . date('Y-m-d H:i:s') . '] Error de login: ' . $e->getMessage());
                
                // Guardar error en sesión y redirigir para evitar reenvío
                $_SESSION['login_error'] = $e->getMessage();
                header('Location: ' . BASE_URL . 'auth/login');
                exit;
            }
        }

        // Mostrar error desde sesión si existe
        if (isset($_SESSION['login_error'])) {
            $this->view->setData('error', $_SESSION['login_error']);
            unset($_SESSION['login_error']); // Limpiar después de mostrar
        }

        // Mostrar mensaje de éxito desde registro si existe
        if (isset($_SESSION['register_success'])) {
            error_log('[' . date('Y-m-d H:i:s') . '] Mostrando mensaje de éxito en login: ' . $_SESSION['register_success']);
            $this->view->setData('register_success', $_SESSION['register_success']);
            unset($_SESSION['register_success']); // Limpiar después de mostrar
        }

        $this->view->setTitle('Iniciar Sesión');
        // Para páginas de autenticación, renderizar directamente sin layout
        $this->view->setLayout(null);
        $this->view->render('auth/login');
    }

    public function registerAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log('[' . date('Y-m-d H:i:s') . '] Registro iniciado - Datos recibidos: ' . json_encode($_POST));
            
            $nombre = $_POST['nombre'] ?? '';
            $email = $_POST['email'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $direccion = $_POST['direccion'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            try {
                // Validar datos
                if (empty($nombre) || empty($email) || empty($password) || empty($telefono) || empty($direccion)) {
                    throw new Exception('Todos los campos son requeridos');
                }

                if ($password !== $confirmPassword) {
                    throw new Exception('Las contraseñas no coinciden');
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Email inválido');
                }

                // Verificar si el email ya existe
                if ($this->usuarioModel->getUsuarioByEmail($email)) {
                    throw new Exception('El email ya está registrado');
                }

                // Crear usuario
                $usuarioId = $this->usuarioModel->crearUsuario([
                    'nombre' => $nombre,
                    'email' => $email,
                    'telefono' => $telefono,
                    'direccion' => $direccion,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'rol_id' => 2 // Rol por defecto (usuario)
                ]);

                if ($usuarioId) {
                    // Log del éxito del registro
                    error_log('[' . date('Y-m-d H:i:s') . '] Usuario registrado exitosamente - ID: ' . $usuarioId . ', Email: ' . $email);
                    
                    // Establecer permisos por defecto para usuarios nuevos (rol_id = 2)
                    $permisosPorDefecto = [
                        'ver_mascotas',
                        'crear_mascotas',
                        'editar_mascotas',
                        'ver_dispositivos',
                        'crear_dispositivos',
                        'editar_dispositivos',
                        'ver_monitor'
                    ];
                    
                    // Guardar permisos en sesión temporal para el login
                    $_SESSION['temp_permissions'] = $permisosPorDefecto;
                    
                    // Redirigir al login con mensaje de éxito
                    $_SESSION['register_success'] = 'Usuario registrado exitosamente. Por favor, inicia sesión.';
                    error_log('[' . date('Y-m-d H:i:s') . '] Redirigiendo a login con mensaje de éxito');
                    header('Location: ' . APP_URL . '/auth/login');
                    exit;
                } else {
                    error_log('[' . date('Y-m-d H:i:s') . '] Error al crear usuario - Email: ' . $email);
                    throw new Exception('Error al crear el usuario');
                }
            } catch (Exception $e) {
                $this->view->setData('error', $e->getMessage());
            }
        }

        $this->view->setTitle('Registro');
        $this->view->render('auth/register');
    }

    public function resetPasswordAction($token = null) {
        // Si ya está autenticado, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            redirect('dashboard');
        }
        
        // Permitir recibir el token por GET si no llega por parámetro
        if (!$token) {
            $token = $_GET['token'] ?? null;
        }
        
        // Extraer el token del segmento de la URL si sigue sin estar definido
        if (!$token && isset($_SERVER['REQUEST_URI'])) {
            $parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
            $key = array_search('reset-password', $parts);
            if ($key !== false && isset($parts[$key + 1])) {
                $token = $parts[$key + 1];
            }
        }
        
        if (!$token) {
            redirect('auth/login');
        }
        
        // Verificar token
        $reset = $this->usuarioModel->getPasswordReset($token);
        if (!$reset || strtotime($reset['expires_at']) < time()) {
            $title = 'Enlace Inválido';
            $content = '<div class="container d-flex align-items-center justify-content-center min-vh-100"><div class="card shadow-lg border-0 rounded-4 p-4" style="max-width: 400px; width: 100%;"><div class="text-center mb-4"><img src="https://cdn-icons-png.flaticon.com/512/616/616408.png" alt="Logo mascota" style="width: 80px;"><h2 class="mt-3 mb-1" style="font-weight: 700; color: #0D47A1;">Enlace inválido o expirado</h2><p class="text-muted mb-0">El enlace de recuperación no es válido o ha expirado.</p></div><div class="text-center"><a href="' . APP_URL . '/auth/forgot-password" class="btn btn-primary">Solicitar nuevo enlace</a></div></div></div>';
            $GLOBALS['content'] = $content;
            $GLOBALS['title'] = $title;
            require_once 'views/layouts/main.php';
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateRequest(['password', 'confirm_password']);
            if ($data) {
                if ($data['password'] !== $data['confirm_password']) {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'Las contraseñas no coinciden'
                    ], 400);
                }
                
                // Validar contraseña
                if (!$this->validatePassword($data['password'])) {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'La contraseña no cumple con los requisitos de seguridad'
                    ], 400);
                }
                
                $userData = [
                    'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST])
                ];
                
                if ($this->usuarioModel->update($reset['user_id'], $userData)) {
                    $this->usuarioModel->deletePasswordReset($token);
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Contraseña actualizada correctamente',
                        'redirect' => APP_URL . '/auth/login'
                    ]);
                } else {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'Error al actualizar la contraseña'
                    ], 500);
                }
            }
        }
        
        // Mostrar vista moderna de restablecimiento
        $title = 'Restablecer Contraseña';
        ob_start();
        include __DIR__ . '/../views/auth/reset-password.php';
        $content = ob_get_clean();
        $GLOBALS['content'] = $content;
        $GLOBALS['title'] = $title;
        require_once 'views/layouts/main.php';
    }

    public function logoutAction() {
        session_destroy();
        $this->redirect('auth/login');
    }

    public function forgotPasswordAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            if (!$email) {
                $this->jsonResponse(['success' => false, 'error' => 'Correo inválido'], 400);
            }
            
            $user = $this->usuarioModel->getUsuarioByEmail($email);
            if (!$user) {
                $this->jsonResponse(['success' => false, 'error' => 'No existe una cuenta con ese correo'], 404);
            }
            
            // Generar token y guardar
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $this->usuarioModel->createPasswordReset($user['id_usuario'], $token, $expiresAt);
            
            // Enviar correo
            $resetUrl = APP_URL . "/auth/reset-password/" . $token;
            $subject = "Recupera tu contraseña - VitalPet Monitor";
            $body = "<p>Hola <b>{$user['nombre']}</b>,</p>"
                . "<p>Recibimos una solicitud para restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:</p>"
                . "<p><a href='$resetUrl'>$resetUrl</a></p>"
                . "<p>Si no solicitaste este cambio, ignora este correo.</p>";
            
            // Usa tu función real de envío de correo aquí:
            if (function_exists('enviarCorreo')) {
                $ok = enviarCorreo($user['email'], $subject, $body);
            } else {
                // Simulación: guardar el enlace en el log
                $logMsg = '['.date('Y-m-d H:i:s')."] Enlace de recuperación generado para {$user['email']}: $resetUrl\n";
                file_put_contents(dirname(__DIR__) . '/logs/error.log', $logMsg, FILE_APPEND);
                $ok = true;
            }
            
            if ($ok) {
                $this->jsonResponse(['success' => true, 'message' => 'Te hemos enviado las instrucciones a tu correo.']);
            } else {
                $this->jsonResponse(['success' => false, 'error' => 'No se pudo enviar el correo. Intenta más tarde.'], 500);
            }
        }
        
        // GET: mostrar vista
        $title = 'Recuperar Contraseña';
        ob_start();
        include __DIR__ . '/../views/auth/forgot-password.php';
        $content = ob_get_clean();
        $GLOBALS['content'] = $content;
        $GLOBALS['title'] = $title;
        require_once 'views/layouts/main.php';
    }

    private function getRolNombre($rolId) {
        $roles = [
            1 => 'Administrador',
            2 => 'Usuario',
            3 => 'Super Administrador'
        ];
        return $roles[$rolId] ?? 'Usuario';
    }

    private function validatePassword($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[@$!%*?&])[A-Za-z\\d@$!%*?&]{8,}$/', $password);
    }
}
?> 