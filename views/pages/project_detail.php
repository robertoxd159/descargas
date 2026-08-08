<!-- views/pages/project_detail.php -->
<div class="row mb-4">
    <div class="col-md-2">
        <a href="index.php" class="btn btn-outline-dark rounded-pill mb-3">← Volver</a>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-5" style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
    <div class="row g-0">
        <div class="col-md-5">
            <img src="<?= htmlspecialchars($proyecto['imagen_url']) ?>" alt="Portada" style="width: 100%; height: 100%; object-fit: cover; min-height: 300px;">
        </div>
        <div class="col-md-7">
            <div class="card-body p-5 h-100 d-flex flex-column">
                <span class="badge bg-dark rounded-pill mb-3 align-self-start fs-6 px-3 py-2"><?= htmlspecialchars($proyecto['categoria']) ?></span>
                
                <h2 class="fw-bold mb-3"><?= htmlspecialchars($proyecto['titulo']) ?></h2>
                <p class="text-muted mb-4" style="white-space: pre-line; line-height: 1.8;">
                    <?= htmlspecialchars($proyecto['descripcion']) ?>
                </p>
                
                <div class="mt-auto pt-4 border-top" style="border-color: var(--glass-border) !important;">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <div class="alert alert-warning border-0 rounded-3 text-center mb-0">
                            Debes <a href="login.php" class="alert-link">iniciar sesión</a> para descargar.
                        </div>
                    <?php elseif(!$es_premium): ?>
                        <div class="alert alert-info border-0 rounded-3 text-center mb-0">
                            <strong>⭐ Este es un recurso Premium.</strong><br>
                            Adquiere un plan para desbloquear la descarga.
                            <a href="premium.php" class="btn btn-dark w-100 rounded-pill mt-3">Ver planes Premium</a>
                        </div>
                    <?php else: ?>
                        <!-- El script de descarga lo haremos en el próximo paso -->
                        <a href="descargar.php?id=<?= $proyecto['id'] ?>" class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm">
                            ⬇️ Descargar Proyecto
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>