<?php
// admin.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require_once 'config/Database.php';
require_once 'models/User.php';
require_once 'models/Payment.php';
require_once 'models/Setting.php';

$db = (new Database())->getConnection();
$userModel = new User($db);
$paymentModel = new Payment($db);
$settingModel = new Setting($db); // Instanciamos el modelo

// Acción 1: Procesar formulario manual de Usuarios (Pestaña 1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['dias'])) {
    $userModel->updatePremiumStatus($_POST['user_id'], $_POST['dias']);
    header("Location: admin.php");
    exit;
}

// Acción 2: Procesar aprobación/rechazo de pagos Yape (Pestaña 2)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_pago']) && isset($_POST['pago_id'])) {
    $pago = $paymentModel->getPaymentById($_POST['pago_id']);
    
    if ($pago && $pago['estado'] === 'pendiente') {
        if ($_POST['accion_pago'] === 'aprobar') {
            $paymentModel->updatePaymentStatus($pago['id'], 'aprobado');
            
            // Calculamos los días según el plan comprado
            $dias_a_dar = 30; // Por defecto mensual
            if ($pago['plan'] === 'semestral') $dias_a_dar = 180;
            if ($pago['plan'] === 'anual') $dias_a_dar = 365;
            
            // Otorgamos los días correctos
            $userModel->updatePremiumStatus($pago['user_id'], $dias_a_dar);
        } elseif ($_POST['accion_pago'] === 'rechazar') {
            $paymentModel->updatePaymentStatus($pago['id'], 'rechazado');
        }
    }
    header("Location: admin.php");
    exit;
}

// Acción 3: Sincronizar catálogo con Telegram
if (isset($_GET['accion']) && $_GET['accion'] === 'sincronizar') {
    $python_script = __DIR__ . '/telegram/sync.py';
    // Ejecutamos Python en segundo plano y capturamos la salida
    $output = shell_exec("python \"$python_script\" 2>&1");
    
    // Guardamos un mensaje de éxito en la sesión para mostrarlo en la vista
    $_SESSION['mensaje_admin'] = "¡Sincronización completada con éxito!";
    header("Location: admin.php");
    exit;
}

// Acción 4: Procesar actualización de configuraciones (NUEVO)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_config'])) {
    $settingModel->update('nombre_sitio', trim($_POST['nombre_sitio']));
    $settingModel->update('icono_sitio', trim($_POST['icono_sitio']));
    $settingModel->update('precio_mensual', trim($_POST['precio_mensual']));
    $settingModel->update('precio_semestral', trim($_POST['precio_semestral']));
    $settingModel->update('precio_anual', trim($_POST['precio_anual']));
    $settingModel->update('whatsapp', trim($_POST['whatsapp']));
    
    $_SESSION['mensaje_admin'] = "¡Configuración guardada exitosamente!";
    header("Location: admin.php");
    exit;
}

// Obtener datos para las vistas
$usuarios = $userModel->getAllUsers();
$pagos_pendientes = $paymentModel->getPendingPayments();

// NUEVAS VARIABLES PARA EL TABLERO
$statsPagos = $paymentModel->getDashboardStats();
$usuariosActivos = $userModel->getActivePremiumCount();

// Leer la configuración para enviarla a la vista
$config = $settingModel->getAll();

require_once 'views/layout/header.php';
require_once 'views/pages/admin.php';
require_once 'views/layout/footer.php';
?>