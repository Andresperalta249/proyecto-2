<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../config/database.php';

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        // Iniciar sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $database = new Database();
        $this->userModel = new User($database);
        
        // Manejar solicitud directa al controlador
        if (basename($_SERVER['PHP_SELF']) === 'AuthController.php') {
            if (isset($_GET['action']) && $_GET['action'] === 'logout') {
                $this->logout();
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handleRequest();
                exit;
            }
        }
    }

    public function handleRequest() {
        header('Content-Type: application/json');
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['action']) && $_POST['action'] === 'login') {
                    return $this->processLogin();
                }
            }
            
            throw new Exception('Solicitud inválida');
            
        } catch (Exception $e) {
            error_log('Error en AuthController: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function processLogin() {
        try {
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                throw new Exception('Por favor, complete todos los campos');
            }

            $user = $this->userModel->authenticate($email, $password);

            if (!$user) {
                throw new Exception('Credenciales inválidas');
            }

            // Cargar los permisos del usuario
            $database = new Database();
            $roleModel = new Role($database);
            $permisos = $roleModel->getPermissions($user['rol_id']);
            $permisosArray = array_map(function($permiso) {
                return $permiso['nombre'];
            }, $permisos);

            // Iniciar sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['rol_id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['permisos'] = $permisosArray;
            $_SESSION['last_activity'] = time();

            // Si es superadmin, agregar todos los permisos
            if ($user['rol_id'] == 1) {
                $allPermisos = $roleModel->getAllPermisos();
                $_SESSION['permisos'] = array_map(function($p) {
                    return $p['nombre'];
                }, $allPermisos);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'redirect' => '/Proyecto%202/views/dashboard/index.php'
            ]);

        } catch (Exception $e) {
            error_log('Error en login: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function showLogin() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/Proyecto%202/views/dashboard/index.php');
            return;
        }
        $this->view('auth/login');
    }

    public function showRegister() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/Proyecto%202/views/dashboard/index.php');
            return;
        }
        $this->view('auth/register');
    }

    public function logout() {
        // Destruir todas las variables de sesión
        $_SESSION = array();

        // Destruir la sesión
        session_destroy();

        // Redirigir al login
        header('Location: /Proyecto%202/views/auth/login.php');
        exit();
    }

    public function showDashboard() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/Proyecto%202/public/login');
            return;
        }

        // Verificar tiempo de inactividad (30 minutos)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            $this->logout();
            return;
        }

        $_SESSION['last_activity'] = time();
        $this->view('dashboard/index');
    }

    public function register() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => filter_var($_POST['nombre'] ?? '', FILTER_SANITIZE_STRING),
                'email' => filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
                'password' => $_POST['password'] ?? ''
            ];

            if (empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Por favor, complete todos los campos'
                ]);
                return;
            }

            $userModel = $this->model('User');
            
            if ($userModel->findByEmail($data['email'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El correo electrónico ya está registrado'
                ]);
                return;
            }

            if ($userModel->create($data)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente',
                    'redirect' => '/Proyecto%202/public/login'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear el usuario'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido'
            ]);
        }
    }

    public function showForgotPassword() {
        $this->view('auth/forgot-password');
    }

    public function forgotPassword() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

            if (empty($email)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Por favor, ingrese su correo electrónico'
                ]);
                return;
            }

            $userModel = $this->model('User');
            $user = $userModel->findByEmail($email);

            if ($user) {
                // Generar token y guardar en la base de datos
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                // Aquí deberías implementar la lógica para guardar el token
                // y enviar el correo electrónico con el enlace de recuperación
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Se ha enviado un enlace de recuperación a su correo electrónico'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró una cuenta con ese correo electrónico'
                ]);
            }
        }
    }

    private function setUserSession($user) {
        error_log("Iniciando setUserSession para usuario ID: " . $user['id']);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['rol_id'];
        
        // Obtener permisos del rol
        $roleModel = new Role($this->db);
        $permisos = $roleModel->getPermissions($user['rol_id']);
        
        error_log("Permisos obtenidos para rol ID " . $user['rol_id'] . ": " . print_r($permisos, true));
        
        // Si es superadmin (rol_id = 1), asignar todos los permisos
        if ($user['rol_id'] == 1) {
            error_log("Usuario es superadmin, asignando todos los permisos");
            $allPermisos = $roleModel->getAllPermisos();
            $_SESSION['permisos'] = array_map(function($p) {
                return $p['nombre'];
            }, $allPermisos);
        } else {
            $_SESSION['permisos'] = array_map(function($permiso) {
                return $permiso['nombre'];
            }, $permisos);
        }
        
        $_SESSION['last_activity'] = time();
    }
}

// Manejo directo de la solicitud
if (basename($_SERVER['PHP_SELF']) === 'AuthController.php') {
    $auth = new AuthController();
    $auth->handleRequest();
}
?> 