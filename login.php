<?php
// login.php
session_start();

// Si ya está logueado, lo enviamos al inicio
if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

require_once 'models/User.php';

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = $database->getConnection();
    $user = new User($db);

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $userData = $user->emailExists($email);

    // Validar contraseña
    if ($userData && password_verify($password, $userData['password'])) {
        // Guardar datos en la sesión
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_nombre'] = $userData['nombre'];
        $_SESSION['user_rol'] = $userData['rol'];
        
        header("Location: index.php"); // Redirigir al inicio tras el éxito
        exit;
    } else {
        $mensaje = '<div class="alert alert-danger rounded-3 border-0">Correo o contraseña incorrectos.</div>';
    }
}

// Incluir la interfaz
require_once 'views/layout/header.php';
require_once 'views/auth/login_form.php';
require_once 'views/layout/footer.php';
?>