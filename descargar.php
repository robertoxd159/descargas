<?php
// descargar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Validaciones de seguridad rigurosas
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/Database.php';
require_once 'models/Project.php';
require_once 'models/User.php';

$db = (new Database())->getConnection();
$userModel = new User($db);

// Si no es Premium, lo pateamos a la página de planes
if (!$userModel->isPremium($_SESSION['user_id'])) {
    header("Location: premium.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$projectModel = new Project($db);
$proyecto = $projectModel->getProjectById($_GET['id']);

if (!$proyecto) {
    header("Location: index.php");
    exit;
}

// 2. Preparar la carpeta temporal
$file_id = $proyecto['file_id'];
$temp_folder = __DIR__ . '/temp/';

if (!file_exists($temp_folder)) {
    mkdir($temp_folder, 0777, true);
}

// Generamos un nombre único por si 2 usuarios descargan al mismo tiempo
$temp_file = $temp_folder . uniqid('dl_') . '.zip';

// 3. Llamar a Python para traer el archivo
$python_script = __DIR__ . '/telegram/download.py';
$cmd = "python \"$python_script\" \"$file_id\" \"$temp_file\"";

// Ejecutamos Python y PHP espera a que termine
shell_exec($cmd);

// 4. Enviar el archivo al usuario y limpiar
if (file_exists($temp_file)) {
    // Forzar la descarga en el navegador
    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    
    // Limpiamos el nombre original para evitar errores en Windows
    $nombre_limpio = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $proyecto['titulo']);
    header('Content-Disposition: attachment; filename="' . $nombre_limpio . '.zip"');
    
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($temp_file));
    
    ob_clean();
    flush();
    
    readfile($temp_file);
    
    // Magia: Borramos el archivo del disco de XAMPP
    unlink($temp_file);
    exit;
} else {
    echo "<script>alert('Error: No se pudo descargar el archivo de Telegram.'); window.history.back();</script>";
}
?>