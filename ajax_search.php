<?php
// ajax_search.php
require_once 'config/Database.php';
require_once 'models/Project.php';

$db = (new Database())->getConnection();
$project = new Project($db);

$categoria_actual = $_GET['categoria'] ?? null;
$busqueda_actual = $_GET['buscar'] ?? null;

// Paginación para AJAX (Siempre mostramos la página 1 al buscar)
$por_pagina = 6; 
$pagina_actual = 1;
$offset = 0;

$total_proyectos = $project->getTotalProjectsCount($categoria_actual, $busqueda_actual);
$total_paginas = ceil($total_proyectos / $por_pagina);
$proyectos = $project->getAllProjects($categoria_actual, $busqueda_actual, $por_pagina, $offset);

if (empty($proyectos)) {
    echo '<div class="col-12 text-center text-muted mt-5"><p class="fs-5">No se encontraron proyectos para tu búsqueda.</p></div>';
    exit;
}

// 1. Dibujamos las tarjetas (Grid)
echo '<div class="row g-4">';
foreach($proyectos as $p): 
?>
<div class="col-md-6 col-lg-4">
    <div class="card h-100 shadow-sm border-0 rounded-4 transition-hover overflow-hidden" style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
        <img src="<?= htmlspecialchars($p['imagen_url']) ?>" alt="Portada" style="height: 220px; object-fit: cover; width: 100%;">
        <div class="card-body p-4 d-flex flex-column">
            <span class="badge bg-dark rounded-pill mb-3 align-self-start"><?= htmlspecialchars($p['categoria']) ?></span>
            <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($p['titulo']) ?></h5>
            <p class="card-text text-muted small flex-grow-1">
                <?= htmlspecialchars(mb_strimwidth($p['descripcion'], 0, 90, '...')) ?>
            </p>
            <a href="proyecto.php?id=<?= $p['id'] ?>" class="btn btn-outline-dark w-100 rounded-pill fw-bold mt-3">Ver detalles</a>
        </div>
    </div>
</div>
<?php 
endforeach; 
echo '</div>'; // Cerramos el grid

// 2. Dibujamos la paginación si hay más de 1 página
if($total_paginas > 1): 
?>
<nav aria-label="Navegación" class="mt-5">
    <ul class="pagination justify-content-center mb-0">
        <?php for($i = 1; $i <= $total_paginas; $i++): ?>
            <li class="page-item <?= ($pagina_actual == $i) ? 'active' : '' ?>">
                <a class="page-link shadow-sm <?= ($pagina_actual == $i) ? 'bg-dark border-dark text-white' : '' ?>" 
                   href="?page=<?= $i ?><?= !empty($categoria_actual) ? '&categoria='.urlencode($categoria_actual) : '' ?><?= !empty($busqueda_actual) ? '&buscar='.urlencode($busqueda_actual) : '' ?>">
                   <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php 
endif; 
?>