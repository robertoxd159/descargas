<?php
// index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Obtener todos los datos de la nueva API en Render
$api_url = "https://rzc-telegram-bot.onrender.com/api/proyectos";
$json_data = @file_get_contents($api_url);

$todos_los_proyectos = [];
if ($json_data !== FALSE) {
    $data = json_decode($json_data, true);
    if (!isset($data['error'])) {
        $todos_los_proyectos = $data;
    }
}

// 2. Extraer categorías únicas para el menú de filtros
$categorias_array = array_unique(array_column($todos_los_proyectos, 'categoria'));
$categorias = [];
foreach ($categorias_array as $cat) {
    if (!empty($cat)) {
        // Lo guardamos como array para mantener compatibilidad con tu vista
        $categorias[] = ['categoria' => $cat];
    }
}

// 3. Capturar filtros de la URL (búsqueda y categorías)
$categoria_actual = $_GET['categoria'] ?? null;
$busqueda_actual = $_GET['buscar'] ?? null;

// 4. Filtrar los proyectos localmente en PHP
$proyectos_filtrados = array_filter($todos_los_proyectos, function($p) use ($categoria_actual, $busqueda_actual) {
    $cumple_categoria = true;
    $cumple_busqueda = true;

    if ($categoria_actual) {
        $cumple_categoria = (strtolower($p['categoria']) == strtolower($categoria_actual));
    }

    if ($busqueda_actual) {
        $busqueda = strtolower($busqueda_actual);
        $titulo = strtolower($p['titulo']);
        $desc = strtolower($p['descripcion']);
        // Buscamos coincidencias en el título o descripción
        $cumple_busqueda = (strpos($titulo, $busqueda) !== false || strpos($desc, $busqueda) !== false);
    }

    return $cumple_categoria && $cumple_busqueda;
});

// Reindexar el array para evitar saltos en las posiciones
$proyectos_filtrados = array_values($proyectos_filtrados);

// 5. Configurar la paginación usando los datos filtrados
$por_pagina = 6; 
$pagina_actual = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$total_proyectos = count($proyectos_filtrados);
$total_paginas = ceil($total_proyectos / $por_pagina);
$offset = ($pagina_actual - 1) * $por_pagina;

// 6. Extraer solo los proyectos que corresponden a la página actual
$proyectos = array_slice($proyectos_filtrados, $offset, $por_pagina);

// 7. Cargar las vistas exactamente igual que antes
require_once 'views/layout/header.php';
require_once 'views/pages/catalog.php';
require_once 'views/layout/footer.php';
?>