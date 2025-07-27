<?php

class MonitorController extends Controller {
    private $dispositivoModel;

    public function __construct() {
        parent::__construct();
        $this->dispositivoModel = new DispositivoModel();
    }

    public function indexAction() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        if (function_exists('verificarPermiso') && !verificarPermiso('ver_monitor')) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        // PRUEBA DIRECTA - Si esto funciona, el problema está en la vista
        echo "<h1 style='color: red;'>🎯 PRUEBA DIRECTA DEL CONTROLADOR 🎯</h1>";
        echo "<p>Si ves esto, el controlador funciona pero la vista no.</p>";
        
        // Obtener dispositivos según permisos con datos de ubicación
        if (function_exists('verificarPermiso') && verificarPermiso('ver_todos_dispositivos')) {
            $dispositivos = $this->dispositivoModel->getDispositivosFiltrados($_SESSION['user_id']);
        } else {
            $dispositivos = $this->dispositivoModel->getDispositivosFiltrados($_SESSION['user_id']);
        }

        echo "<p>Dispositivos encontrados: " . count($dispositivos) . "</p>";
        
        $this->view->setTitle('Monitor de Dispositivos');
        $this->view->setData('dispositivos', $dispositivos);
        $this->view->setData('menuActivo', 'monitor');
        $this->view->render('monitor/index');
    }

    public function testSimpleAction() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $this->view->setTitle('Test Simple');
        $this->view->setData('menuActivo', 'monitor');
        $this->view->render('monitor/test-simple');
    }
} 