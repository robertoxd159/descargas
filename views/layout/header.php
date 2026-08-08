<?php
// views/layout/header.php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Setting.php';

// Verificamos si la conexión ya existe en el controlador actual, si no, la creamos
if (!isset($db)) {
    $db = (new Database())->getConnection();
}
$settingModelGlobal = new Setting($db);
$configSite = $settingModelGlobal->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Título y Favicon Dinámicos -->
    <title><?= htmlspecialchars($configSite['nombre_sitio'] ?? 'Premium Downloads') ?></title>
    <link rel="icon" href="<?= htmlspecialchars($configSite['icono_sitio'] ?? '') ?>">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top glass-nav">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
    <?= htmlspecialchars($configSite['nombre_sitio'] ?? 'Premium') ?>
</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link text-reset" href="index.php">Catálogo</a></li>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-reset fw-bold" href="#" data-bs-toggle="dropdown">
                                Hola, <?= htmlspecialchars($_SESSION['user_nombre']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="background: var(--glass-bg); backdrop-filter: blur(12px);">
                                <li><a class="dropdown-item text-reset" href="perfil.php">Mi Panel</a></li>
                                <?php if($_SESSION['user_rol'] === 'admin'): ?>
                                    <li><a class="dropdown-item text-reset" href="admin.php">Panel Admin</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Cerrar sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link text-reset" href="login.php">Ingresar</a></li>
                        <li class="nav-item"><a class="nav-link text-reset" href="register.php">Registro</a></li>
                    <?php endif; ?>
                    
                    <li class="nav-item"><a class="btn btn-dark rounded-pill px-4 ms-2 mt-2 mt-lg-0" href="premium.php">Premium</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container py-5">