<?php
// logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vaciar el arreglo de la sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, borramos también la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión final
session_destroy();

// Redirigir al inicio de sesión
header("Location: login.php");
exit;
?>