<?php
// perfil.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


require_once 'models/User.php';
require_once 'models/Payment.php';

$userModel = new User($db);
$paymentModel = new Payment($db);

// PROCESAR CAMBIO DE CONTRASEÑA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $password_confirmar = $_POST['password_confirmar'];

    $hash_actual = $userModel->getPasswordHash($_SESSION['user_id']);

    if (!password_verify($password_actual, $hash_actual)) {
        $_SESSION['perfil_error'] = "La contraseña actual es incorrecta.";
    } elseif ($password_nueva !== $password_confirmar) {
        $_SESSION['perfil_error'] = "Las contraseñas nuevas no coinciden.";
    } elseif (strlen($password_nueva) < 6) {
        $_SESSION['perfil_error'] = "La nueva contraseña debe tener al menos 6 caracteres.";
    } else {
        $nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        $userModel->updatePassword($_SESSION['user_id'], $nuevo_hash);
        $_SESSION['perfil_exito'] = "¡Tu contraseña ha sido actualizada con éxito!";
    }
    
    // Recargamos la página para mostrar el mensaje
    header("Location: perfil.php");
    exit;
}

// Obtener datos del usuario logueado
$usuario = $userModel->getUserDetails($_SESSION['user_id']);
$es_premium = $userModel->isPremium($_SESSION['user_id']);

// Obtener su historial de pagos
$historial_pagos = $paymentModel->getPaymentsByUserId($_SESSION['user_id']);

require_once 'views/layout/header.php';
require_once 'views/pages/profile.php';
require_once 'views/layout/footer.php';
?>