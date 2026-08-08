<!-- views/pages/catalog.php -->
<div class="row mb-5 text-center">
    <div class="col">
        <h1 class="display-4 fw-bold">Proyectos Premium</h1>
        <p class="lead text-muted">Recursos exclusivos sincronizados al instante.</p>
        
        <!-- Barra de búsqueda con ID "formBusqueda" e "inputBuscar" -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-6 col-lg-5">
                <form id="formBusqueda" class="d-flex shadow-sm rounded-pill overflow-hidden" style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
                    <?php if(!empty($categoria_actual)): ?>
                        <input type="hidden" id="inputCategoria" value="<?= htmlspecialchars($categoria_actual) ?>">
                    <?php endif; ?>
                    
                    <!-- Le quitamos el botón de submit para que sea 100% en tiempo real -->
                    <input type="text" id="inputBuscar" class="form-control border-0 bg-transparent px-4 py-3 focus-ring focus-ring-light" placeholder="Escribe para buscar en tiempo real..." value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-center flex-wrap gap-2 mt-4">
            <a href="index.php" class="btn btn-<?= empty($categoria_actual) ? 'dark' : 'outline-dark' ?> rounded-pill px-4 fw-bold">
                Todos
            </a>
            <?php if(!empty($categorias)): ?>
                <?php foreach($categorias as $cat): ?>
                    <a href="index.php?categoria=<?= urlencode($cat) ?>" class="btn btn-<?= ($categoria_actual === $cat) ? 'dark' : 'outline-dark' ?> rounded-pill px-4 fw-bold">
                        <?= htmlspecialchars($cat) ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Contenedor dinámico para Tarjetas Y Paginación -->
<div id="contenedorResultados">
    
    <div class="row g-4">
        <?php if(empty($proyectos)): ?>
            <div class="col-12 text-center text-muted mt-5">
                <p class="fs-5">No se encontraron proyectos.</p>
            </div>
        <?php else: ?>
            <?php foreach($proyectos as $p): ?>
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
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Paginación PHP Clásica -->
    <?php if($total_paginas > 1): ?>
        <nav aria-label="Navegación" class="mt-5">
            <ul class="pagination justify-content-center mb-0">
                <!-- Anterior -->
                <li class="page-item <?= ($pagina_actual <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link shadow-sm" href="?page=<?= $pagina_actual - 1 ?><?= !empty($categoria_actual) ? '&categoria='.urlencode($categoria_actual) : '' ?><?= !empty($busqueda_actual) ? '&buscar='.urlencode($busqueda_actual) : '' ?>">&laquo;</a>
                </li>
                
                <!-- Números -->
                <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?= ($pagina_actual == $i) ? 'active' : '' ?>">
                        <a class="page-link shadow-sm <?= ($pagina_actual == $i) ? 'bg-dark border-dark text-white' : '' ?>" href="?page=<?= $i ?><?= !empty($categoria_actual) ? '&categoria='.urlencode($categoria_actual) : '' ?><?= !empty($busqueda_actual) ? '&buscar='.urlencode($busqueda_actual) : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <!-- Siguiente -->
                <li class="page-item <?= ($pagina_actual >= $total_paginas) ? 'disabled' : '' ?>">
                    <a class="page-link shadow-sm" href="?page=<?= $pagina_actual + 1 ?><?= !empty($categoria_actual) ? '&categoria='.urlencode($categoria_actual) : '' ?><?= !empty($busqueda_actual) ? '&buscar='.urlencode($busqueda_actual) : '' ?>">&raquo;</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<!-- Script de Magia en Tiempo Real -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const inputBuscar = document.getElementById('inputBuscar');
    const formBusqueda = document.getElementById('formBusqueda');
    const inputCategoria = document.getElementById('inputCategoria');
    const contenedorResultados = document.getElementById('contenedorResultados'); // Cambiamos el target aquí
    
    let timeout = null;

    formBusqueda.addEventListener('submit', function(e) {
        e.preventDefault();
    });

    inputBuscar.addEventListener('input', function() {
        clearTimeout(timeout);
        
        timeout = setTimeout(() => {
            const busqueda = this.value;
            const categoria = inputCategoria ? inputCategoria.value : '';
            
            fetch(`ajax_search.php?buscar=${encodeURIComponent(busqueda)}&categoria=${encodeURIComponent(categoria)}`)
                .then(response => response.text())
                .then(html => {
                    // Reemplazamos tanto las tarjetas como la paginación de golpe
                    contenedorResultados.innerHTML = html;
                });
        }, 300);
    });
});
</script>