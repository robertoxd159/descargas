<?php
// checkout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

require_once 'config/Database.php';
require_once 'models/Payment.php';
require_once 'models/Setting.php'; // Agregamos el modelo de config

$db = (new Database())->getConnection();
$payment = new Payment($db);
$settingModel = new Setting($db);
$configSite = $settingModel->getAll(); // Traemos las variables

// Usamos los precios dinámicos de la base de datos
$planes = [
    'mensual' => $configSite['precio_mensual'], 
    'semestral' => $configSite['precio_semestral'], 
    'anual' => $configSite['precio_anual']
];

$plan = $_GET['plan'] ?? '';

if(!array_key_exists($plan, $planes)){
    header("Location: premium.php");
    exit;
}

$precio = $planes[$plan];
$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $referencia = trim($_POST['referencia']);

    if($payment->registrarPago($_SESSION['user_id'], $referencia, $plan)) {
        // Usamos el número de WhatsApp dinámico
        $telefono = $configSite['whatsapp'];
        
        $texto = urlencode("Hola, acabo de pagar S/{$precio} por el plan {$plan}. Mi referencia es: {$referencia}. Mi correo es: {$_SESSION['user_email']}");
        header("Location: https://wa.me/{$telefono}?text={$texto}");
        exit;
    } else {
        $mensaje = '<div class="alert alert-danger rounded-3 border-0">Error al registrar el pago.</div>';
    }
}

require_once 'views/layout/header.php';
require_once 'views/pages/checkout_form.php';
require_once 'views/layout/footer.php';
?>