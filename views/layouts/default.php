<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->title ?? 'Monitor de Dispositivos' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/device-monitor.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/components/typography.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/layout/sidebar.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Main Content -->
    <main class="container py-4">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <!-- <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">
                &copy; <?= date('Y') ?> PetTracker. Todos los derechos reservados.
            </span>
        </div>
    </footer> -->

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html> 