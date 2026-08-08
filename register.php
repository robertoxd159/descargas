<?php
// register.php

require_once 'config/Database.php';
require_once 'models/User.php';

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($user->emailExists($email)) {
        $mensaje = '<div class="alert alert-warning rounded-3 border-0">El correo ya está registrado.</div>';
    } else {
        if ($user->register($nombre, $email, $password)) {
            $mensaje = '<div class="alert alert-success rounded-3 border-0">Registro exitoso. <a href="login.php" class="alert-link">Inicia sesión aquí</a>.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger rounded-3 border-0">Error al registrar. Inténtalo de nuevo.</div>';
        }
    }
}

// Incluir la interfaz
require_once 'views/layout/header.php';
require_once 'views/auth/register_form.php';
require_once 'views/layout/footer.php';
?>