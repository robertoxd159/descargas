<?php
// aprobar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteger ruta
if(!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'admin'){
    header("Location: index.php");
    exit;
}

if (isset($_GET['id'])) {
    
    require_once 'models/Admin.php';

    $admin = new Admin($db);
    
    $id_pago = intval($_GET['id']);
    $admin->aprobarPago($id_pago);
}

// Regresar al panel
header("Location: admin.php");
exit;
?>