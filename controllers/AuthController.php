<?php
// Validación de contraseña en PHP
if (!function_exists('validatePassword')) {
    function validatePassword($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[@$!%*?&])[A-Za-z\\d@$!%*?&]{8,}$/', $password);
    }
}

class AuthController extends Controller {
    private $usuarioModel;
    private $logModel;
    protected $requireAuth = false; // No requiere autenticación

    public function __construct() {
        parent::__construct();
        $this->usuarioModel = $this->loadModel('UsuarioModel');
        $this->logModel = $this->loadModel('Log');
    }

    private function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function loginAction() {
        if ($this->isLoggedIn()) {
            $paginaInicial = obtenerPaginaInicial();
            header('Location: ' . $paginaInicial);
            exit;
        }

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        
        if ($this->isPostRequest()) {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];

            if ($usuario = $this->usuarioModel->getUsuarioByEmail($email)) {
                if (password_verify($password, $usuario['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $usuario['id_usuario'];
                    $_SESSION['user_nombre'] = $usuario['nombre'];
                    $_SESSION['user_rol_id'] = $usuario['rol_id'];
                    $_SESSION['permissions'] = $this->usuarioModel->getPermisosDeUsuario($usuario['id_usuario']);

                    // Usar el método correcto para registrar el acceso
                    $this->usuarioModel->registrarUltimoAcceso($usuario['id_usuario']);

                    // Determinar página inicial según permisos
                    $paginaInicial = obtenerPaginaInicial();

                    if ($isAjax) {
                        return $this->jsonResponse(['success' => true, 'redirect' => $paginaInicial]);
                    }
                    header('Location: ' . $paginaInicial);
                    exit;
                }
            }

            if ($isAjax) {
                return $this->jsonResponse(['success' => false, 'message' => 'Credenciales incorrectas'], 401);
            }
            $this->view->setData('error', 'Credenciales incorrectas');
        }

        $this->view->setLayout('auth');
        $this->view->render('auth/login');
    }

    public function registerAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            try {
                // Validar datos
                if (empty($nombre) || empty($email) || empty($password)) {
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
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'rol' => 'usuario' // Rol por defecto
                ]);

                if ($usuarioId) {
                    // Iniciar sesión automáticamente
                    $_SESSION['user_id'] = $usuarioId;
                    $_SESSION['user_name'] = $nombre;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = 'usuario';

                    // Redirigir al dashboard
                    header('Location: ' . BASE_URL . 'dashboard');
                    exit;
                } else {
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
                if (!validatePassword($data['password'])) {
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
        redirect('/login');
    }

    public function testdbAction() {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=iot_pets', 'root', '');
            echo '<div style="padding:2rem;font-size:1.5rem;color:green;">Conexión exitosa a la base de datos.</div>';
        } catch (PDOException $e) {
            echo '<div style="padding:2rem;font-size:1.5rem;color:red;">Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit;
    }

    public function register() {
        try {
            $userData = [
                'nombre' => $_POST['nombre'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'rol_id' => $_POST['rol_id'] ?? null,
                'estado' => 'activo'
            ];

            $userId = $this->usuarioModel->insertUsuario($userData);
            
            if ($userId) {
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $userData['nombre'];
                $_SESSION['user_role'] = $userData['rol_id'];
                
                header('Location: ' . BASE_URL . 'dashboard');
                exit;
            } else {
                throw new Exception('Error al crear el usuario');
            }
        } catch (Exception $e) {
            $errorMsg = "Error al registrar usuario: " . $e->getMessage();
            $this->view->set('error', $errorMsg);
            $this->view->render('auth/register');
        }
    }

    public function forgotPasswordAction() {
        if ($this->isPostRequest()) {
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
            date_default_timezone_set('America/Mexico_City'); // Ajusta a tu zona horaria real
            $createdAt = date('Y-m-d H:i:s');
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $this->usuarioModel->createPasswordReset($user['id_usuario'], $token, $expiresAt, $createdAt);
            // Enviar correo
            $resetUrl = APP_URL . "/auth/reset-password/" . $token;
            $subject = "Recupera tu contraseña - PetMonitoring IoT";
            $body = "<p>Hola <b>{$user['nombre']}</b>,</p>"
                . "<p>Recibimos una solicitud para restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:</p>"
                . "<p><a href='$resetUrl'>$resetUrl</a></p>"
                . "<p>Si no solicitaste este cambio, ignora este correo.</p>";
            $ok = false;
            if (function_exists('enviarCorreo')) {
                $ok = enviarCorreo($user['email'], $subject, $body);
            } else {
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

    public function newPasswordAction() {
        $token = $_GET['token'] ?? '';
        $reset = $this->usuarioModel->getPasswordReset($token);

        if (!$reset) {
            $this->view->set('error', 'Token inválido o expirado');
            $this->view->render('auth/reset-password');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if ($password !== $confirmPassword) {
                $this->view->set('error', 'Las contraseñas no coinciden');
                $this->view->render('auth/new-password');
                return;
            }

            $data = [
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ];

            if ($this->usuarioModel->update($reset['user_id'], $data)) {
                $this->usuarioModel->deletePasswordReset($token);
                $this->view->set('success', 'Contraseña actualizada exitosamente');
                $this->view->render('auth/login');
            } else {
                $this->view->set('error', 'Error al actualizar la contraseña');
                $this->view->render('auth/new-password');
            }
        } else {
            $this->view->render('auth/new-password');
        }
    }

    protected function render($view, $data = []) {
        $this->view->render($view, $data);
    }

    private function isPostRequest() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}
?> 