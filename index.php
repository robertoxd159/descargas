<?php
// index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/Database.php';
require_once 'models/Project.php';

$db = (new Database())->getConnection();
$project = new Project($db);

$categorias = $project->getCategories();

// 1. Capturar filtros de la URL
$categoria_actual = $_GET['categoria'] ?? null;
$busqueda_actual = $_GET['buscar'] ?? null;

// 2. Configurar la paginación
$por_pagina = 6; // Cuántos proyectos mostrar por página
$pagina_actual = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// 3. Obtener totales y los proyectos de la página actual
$total_proyectos = $project->getTotalProjectsCount($categoria_actual, $busqueda_actual);
$total_paginas = ceil($total_proyectos / $por_pagina);

$proyectos = $project->getAllProjects($categoria_actual, $busqueda_actual, $por_pagina, $offset);

require_once 'views/layout/header.php';
require_once 'views/pages/catalog.php';
require_once 'views/layout/footer.php';
?>