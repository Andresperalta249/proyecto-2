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

        // Obtener dispositivos según permisos
        if (function_exists('verificarPermiso') && verificarPermiso('ver_todos_dispositivos')) {
            $dispositivos = $this->dispositivoModel->getTodosDispositivosConMascotas();
        } else {
            $dispositivos = $this->dispositivoModel->getDispositivosWithMascotas($_SESSION['user_id']);
        }

        $this->view->setTitle('Monitor de Dispositivos');
        $this->view->setData('dispositivos', $dispositivos);
        $this->view->setData('menuActivo', 'monitor');
        $this->view->render('monitor/index');
    }
} 