<!-- views/pages/premium.php -->
<div class="text-center mb-5">
    <h2 class="display-5 fw-bold">Desbloquea todo el poder</h2>
    <p class="lead text-muted">Accede a descargas ilimitadas de proyectos premium.</p>
</div>

<div class="row g-4 justify-content-center">
    <!-- Plan Mensual -->
    <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 rounded-4 text-center p-4 transition-hover"
            style="background: var(--glass-bg);">
            <h4 class="text-muted fw-bold">Mensual</h4>
            <h1 class="display-4 fw-bold my-3">S/
                <?= htmlspecialchars($configSite['precio_mensual']) ?>
            </h1>
            <ul class="list-unstyled mb-4">
                <li class="mb-2">✅ Acceso a todo el catálogo</li>
                <li class="mb-2">✅ Descargas directas (ZIP)</li>
                <li class="mb-2">✅ Soporte básico</li>
            </ul>
            <a href="checkout.php?plan=mensual" class="btn btn-outline-dark rounded-pill mt-auto fw-bold">Elegir
                Plan</a>
        </div>
    </div>

    <!-- Plan Semestral (Destacado) -->
    <div class="col-md-4">
        <div class="card h-100 shadow border-0 rounded-4 text-center p-4 position-relative transition-hover"
            style="background: var(--text-color); color: var(--bg-color);">
            <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-danger px-3 py-2">
                Más popular
            </span>
            <h4 class="fw-bold mt-2">Semestral</h4>
<h1 class="display-4 fw-bold my-3">S/<?= htmlspecialchars($configSite['precio_semestral']) ?></h1>            <ul class="list-unstyled mb-4">
                <li class="mb-2">⭐ Todo lo del plan mensual</li>
                <li class="mb-2">⭐ Ahorras S/15</li>
                <li class="mb-2">⭐ Acceso anticipado</li>
            </ul>
            <a href="checkout.php?plan=semestral" class="btn btn-light rounded-pill mt-auto fw-bold">Elegir Plan</a>
        </div>
    </div>

    <!-- Plan Anual -->
    <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 rounded-4 text-center p-4 transition-hover"
            style="background: var(--glass-bg);">
            <h4 class="text-muted fw-bold">Anual</h4>
<h1 class="display-4 fw-bold my-3">S/<?= htmlspecialchars($configSite['precio_anual']) ?></h1>            <ul class="list-unstyled mb-4">
                <li class="mb-2">🔥 Todo lo del plan semestral</li>
                <li class="mb-2">🔥 Ahorras S/60</li>
                <li class="mb-2">🔥 Soporte prioritario</li>
            </ul>
            <a href="checkout.php?plan=anual" class="btn btn-outline-dark rounded-pill mt-auto fw-bold">Elegir Plan</a>
        </div>
    </div>
</div>