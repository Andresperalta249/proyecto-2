<?php
echo "=== DEBUG VISTA MONITOR ===";
echo "<br>Usuario: " . ($_SESSION['user_name'] ?? 'No definido');
echo "<br>Rol: " . ($_SESSION['rol_nombre'] ?? 'No definido');
echo "<br>Permiso ver_monitor: " . (verificarPermiso('ver_monitor') ? 'SÍ' : 'NO');
echo "<br>BASE_URL: " . (BASE_URL ?? 'No definido');
echo "<br>APP_URL: " . (APP_URL ?? 'No definido');
echo "<br>=== FIN DEBUG ===";
?> 