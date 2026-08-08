<!-- views/pages/admin.php -->
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold">Súper Panel de Administración</h2>
        <p class="text-muted mb-0">Control total de tu plataforma desde un solo lugar.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="admin.php?accion=sincronizar" class="btn btn-dark rounded-pill fw-bold shadow-sm">
            🔄 Sincronizar Telegram
        </a>
    </div>
</div>

<!-- Alerta de éxito si se acaba de sincronizar -->
<?php if(isset($_SESSION['mensaje_admin'])): ?>
    <div class="alert alert-success border-0 rounded-3 text-center fw-bold shadow-sm">
        <?= $_SESSION['mensaje_admin'] ?>
    </div>
    <?php unset($_SESSION['mensaje_admin']); ?>
<?php endif; ?>

<!-- Tablero de Resumen Financiero -->
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4" style="background: var(--glass-bg); border-left: 5px solid #198754 !important;">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-success text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 60px; height: 60px; font-size: 1.5rem;">
                    💰
                </div>
                <div>
                    <h6 class="text-muted fw-bold text-uppercase mb-1">Ingresos Totales</h6>
                    <h3 class="fw-bold text-success mb-0">S/ <?= number_format($statsPagos['ganancias_totales'] ?? 0, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4" style="background: var(--glass-bg); border-left: 5px solid #0d6efd !important;">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 60px; height: 60px; font-size: 1.5rem;">
                    📈
                </div>
                <div>
                    <h6 class="text-muted fw-bold text-uppercase mb-1">Ventas Aprobadas</h6>
                    <h3 class="fw-bold text-primary mb-0"><?= $statsPagos['total_ventas'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4" style="background: var(--glass-bg); border-left: 5px solid #ffc107 !important;">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-warning text-dark rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 60px; height: 60px; font-size: 1.5rem;">
                    👑
                </div>
                <div>
                    <h6 class="text-muted fw-bold text-uppercase mb-1">Usuarios Premium</h6>
                    <h3 class="fw-bold text-dark mb-0"><?= $usuariosActivos ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navegación de Pestañas -->
<ul class="nav nav-pills mb-4 gap-2" id="adminTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill fw-bold px-4" id="usuarios-tab" data-bs-toggle="pill"
            data-bs-target="#usuarios" type="button" role="tab" aria-controls="usuarios" aria-selected="true">
            👥 Gestión de Usuarios
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill fw-bold px-4" id="pagos-tab" data-bs-toggle="pill" data-bs-target="#pagos"
            type="button" role="tab" aria-controls="pagos" aria-selected="false">
            💸 Pagos Yape Pendientes
        </button>
    </li>

    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill fw-bold px-4" id="config-tab" data-bs-toggle="pill" data-bs-target="#config" type="button" role="tab" aria-controls="config" aria-selected="false">
            ⚙️ Configuración del Sitio
        </button>
    </li>
</ul>

<!-- Contenido de las Pestañas -->
<div class="tab-content" id="adminTabsContent">

    <!-- PESTAÑA 1: USUARIOS -->
    <div class="tab-pane fade show active" id="usuarios" role="tabpanel" aria-labelledby="usuarios-tab">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden"
            style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Estado Premium</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td class="ps-4 fw-bold">#<?= $u['id'] ?></td>
                                    <td>
                                        <?= htmlspecialchars($u['nombre']) ?>
                                        <?php if ($u['rol'] === 'admin'): ?>
                                            <span class="badge bg-danger ms-1">Admin</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <?php if ($u['premium_hasta'] && strtotime($u['premium_hasta']) > time()): ?>
                                            <span class="badge bg-success">Activo hasta
                                                <?= date('d/m/Y', strtotime($u['premium_hasta'])) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="admin.php" method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <?php if ($u['premium_hasta'] && strtotime($u['premium_hasta']) > time()): ?>
                                                <input type="hidden" name="dias" value="0">
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-danger rounded-pill fw-bold">Revocar</button>
                                            <?php else: ?>
                                                <input type="hidden" name="dias" value="30">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill fw-bold">+30
                                                    Días</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- PESTAÑA 2: PAGOS YAPE -->
    <div class="tab-pane fade" id="pagos" role="tabpanel" aria-labelledby="pagos-tab">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden" style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Fecha</th>
                                <th>Usuario / Email</th>
                                <th>N° Referencia Yape</th>
                                <th>Estado</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($pagos_pendientes)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        No hay pagos pendientes de revisión.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($pagos_pendientes as $pago): ?>
                                <tr>
                                    <td class="ps-4"><?= date('d/m/Y H:i', strtotime($pago['fecha_registro'])) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($pago['nombre']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($pago['email']) ?></small><br>
                                        <!-- Etiqueta del plan corregida -->
                                        <span class="badge bg-info text-dark mt-1 text-uppercase fw-bold">Plan <?= htmlspecialchars($pago['plan'] ?? 'mensual') ?></span>
                                    </td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($pago['numero_referencia']) ?></td>
                                    <td><span class="badge bg-warning text-dark">Pendiente</span></td>
                                    <td class="text-end pe-4">
                                        <form action="admin.php" method="POST" class="d-inline">
                                            <input type="hidden" name="pago_id" value="<?= $pago['id'] ?>">
                                            <button type="submit" name="accion_pago" value="aprobar" class="btn btn-sm btn-success rounded-pill fw-bold mb-1">✅ Aprobar</button>
                                            <button type="submit" name="accion_pago" value="rechazar" class="btn btn-sm btn-outline-danger rounded-pill fw-bold mb-1">❌ Rechazar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- PESTAÑA 3: CONFIGURACIÓN -->
    <div class="tab-pane fade" id="config" role="tabpanel" aria-labelledby="config-tab">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden" style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
            <div class="card-body p-5">
                <form action="admin.php" method="POST">
                    <input type="hidden" name="accion_config" value="1">
                    
                    <h5 class="fw-bold mb-4">Apariencia del Sitio</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label text-muted">Nombre de la Página</label>
                            <input type="text" name="nombre_sitio" class="form-control" value="<?= htmlspecialchars($config['nombre_sitio'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">URL del Favicon</label>
                            <input type="text" name="icono_sitio" class="form-control" placeholder="Ej: assets/logo.png" value="<?= htmlspecialchars($config['icono_sitio'] ?? '') ?>" required>
                            <div class="form-text" style="font-size: 0.75rem;">Ruta local o link de la imagen.</div>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-4 mt-5">Precios y Contacto</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted">Precio Mensual (S/)</label>
                            <input type="number" step="0.01" name="precio_mensual" class="form-control" value="<?= htmlspecialchars($config['precio_mensual'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Precio Semestral (S/)</label>
                            <input type="number" step="0.01" name="precio_semestral" class="form-control" value="<?= htmlspecialchars($config['precio_semestral'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Precio Anual (S/)</label>
                            <input type="number" step="0.01" name="precio_anual" class="form-control" value="<?= htmlspecialchars($config['precio_anual'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-12 mt-3">
                            <label class="form-label text-muted">Número de WhatsApp (Código de país + Número)</label>
                            <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($config['whatsapp'] ?? '') ?>" placeholder="Ej: 51923481905" required>
                        </div>
                    </div>

                    <div class="text-end mt-5">
                        <button type="submit" class="btn btn-dark rounded-pill fw-bold px-5">💾 Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>