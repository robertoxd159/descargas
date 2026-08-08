<?php
// descargar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// [IMPORTANTE]: He comentado temporalmente la validación de base de datos de usuarios
// porque requiere acceso a la BD que InfinityFree bloquea. 
/*
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'models/User.php';
$userModel = new User($db);
if (!$userModel->isPremium($_SESSION['user_id'])) {
    header("Location: premium.php");
    exit;
}
*/

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

// 1. Obtener los detalles del proyecto consultando la API
$api_url = "https://rzc-telegram-bot.onrender.com/api/proyectos";
$json_data = @file_get_contents($api_url);
$proyectos = $json_data ? json_decode($json_data, true) : [];

$proyecto = null;
if (!isset($proyectos['error'])) {
    foreach ($proyectos as $p) {
        if ($p['id'] == $_GET['id']) {
            $proyecto = $p;
            break;
        }
    }
}

if (!$proyecto) {
    echo "<script>alert('Proyecto no encontrado.'); window.location.href='index.php';</script>";
    exit;
}

// 2. Construir la URL de descarga hacia el servidor de Render
// Usamos telegram_file_id (como lo definimos en TiDB)
$file_id = $proyecto['telegram_file_id']; 
$nombre_limpio = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $proyecto['titulo']);

// Enviamos al usuario a Render para que Python procese la descarga desde Telegram
$render_download_url = "https://rzc-telegram-bot.onrender.com/api/bajar_archivo?file_id=" . urlencode($file_id) . "&nombre=" . urlencode($nombre_limpio);

header("Location: " . $render_download_url);
exit;
?>