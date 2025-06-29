<?php
/**
 * Layout: layouts/header.php
 * --------------------------
 * Cabecera del sistema con navegación, menú de usuario y notificaciones.
 *
 * Variables recibidas:
 *   - $usuario: Datos del usuario actual logueado.
 *   - $permisos: Permisos del usuario actual.
 *   - $notificaciones: Lista de notificaciones pendientes.
 *
 * Uso:
 *   Este archivo es incluido por el layout principal para mostrar la cabecera.
 *   Contiene la navegación principal y el menú de usuario.
 */
?>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/tables.js"></script> 