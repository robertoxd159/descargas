<?php
// proyecto.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'models/Project.php';
require_once 'models/User.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$projectModel = new Project($db);
$userModel = new User($db);

$proyecto = $projectModel->getProjectById($_GET['id']);

if (!$proyecto) {
    header("Location: index.php");
    exit;
}

$es_premium = false;
if (isset($_SESSION['user_id'])) {
    $es_premium = $userModel->isPremium($_SESSION['user_id']);
}

require_once 'views/layout/header.php';
require_once 'views/pages/project_detail.php';
require_once 'views/layout/footer.php';
?>