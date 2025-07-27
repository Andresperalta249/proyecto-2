<h1 style="color: red; text-align: center; margin: 50px;">🎯 VISTA DE PRUEBA SIMPLE 🎯</h1>
<p style="text-align: center; font-size: 18px;">Si ves esto, el sistema de vistas funciona correctamente.</p>
<div style="background: yellow; padding: 20px; margin: 20px; text-align: center;">
    <strong>Usuario:</strong> <?= $_SESSION['user_name'] ?? 'No definido' ?><br>
    <strong>Rol:</strong> <?= $_SESSION['rol_nombre'] ?? 'No definido' ?><br>
    <strong>BASE_URL:</strong> <?= BASE_URL ?? 'No definido' ?>
</div> 