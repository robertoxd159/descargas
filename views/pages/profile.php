<!-- views/pages/profile.php -->
<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-10 col-lg-8">
        <h2 class="fw-bold text-center mb-4">Mi Perfil</h2>
        
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4" style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <div class="bg-dark text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">
                        <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
                    </div>
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($usuario['nombre']) ?></h4>
                    <p class="text-muted"><?= htmlspecialchars($usuario['email']) ?></p>
                </div>

                <hr style="border-color: var(--glass-border);">

                <div class="mt-4">
                    <h5 class="fw-bold mb-3">Estado de Membresía</h5>
                    
                    <?php if($es_premium): ?>
                        <div class="alert alert-success border-0 rounded-3 d-flex align-items-center shadow-sm">
                            <span class="fs-1 me-3">👑</span>
                            <div>
                                <strong>¡Eres usuario Premium!</strong><br>
                                Tu acceso ilimitado vence el: <br>
                                <span class="badge bg-success mt-1 fs-6">
                                    <?= date('d/m/Y H:i', strtotime($usuario['premium_hasta'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary border-0 rounded-3 d-flex align-items-center shadow-sm">
                            <span class="fs-1 me-3">🔒</span>
                            <div>
                                <strong>Cuenta Estándar</strong><br>
                                No tienes un plan Premium activo.
                            </div>
                        </div>
                        <a href="premium.php" class="btn btn-dark w-100 rounded-pill fw-bold mt-2 shadow-sm">Mejorar a Premium</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Nueva sección: Historial de Pagos -->
        <h4 class="fw-bold mb-3 mt-5">Historial de Pagos</h4>
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden" style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Plan</th>
                                <th>N° Referencia</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($historial_pagos)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Aún no has realizado ningún pago.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($historial_pagos as $pago): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($pago['fecha_registro'])) ?></td>
                                    <td class="text-uppercase fw-bold text-primary"><?= htmlspecialchars($pago['plan']) ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($pago['numero_referencia']) ?></td>
                                    <td>
                                        <?php if($pago['estado'] === 'aprobado'): ?>
                                            <span class="badge bg-success text-white px-3 py-2 rounded-pill">Aprobado</span>
                                        <?php elseif($pago['estado'] === 'rechazado'): ?>
                                            <span class="badge bg-danger text-white px-3 py-2 rounded-pill">Rechazado</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">En Revisión</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Alertas de Cambio de Contraseña -->
        <?php if(isset($_SESSION['perfil_error'])): ?>
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mt-4 text-center fw-bold">
                <?= $_SESSION['perfil_error'] ?>
            </div>
            <?php unset($_SESSION['perfil_error']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['perfil_exito'])): ?>
            <div class="alert alert-success border-0 rounded-3 shadow-sm mt-4 text-center fw-bold">
                <?= $_SESSION['perfil_exito'] ?>
            </div>
            <?php unset($_SESSION['perfil_exito']); ?>
        <?php endif; ?>

        <!-- Sección: Cambiar Contraseña -->
        <h4 class="fw-bold mb-3 mt-5">Seguridad de la Cuenta</h4>
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-5" style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
            <div class="card-body p-4">
                <form action="perfil.php" method="POST">
                    <input type="hidden" name="cambiar_password" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Contraseña Actual</label>
                        <input type="password" name="password_actual" class="form-control" placeholder="Ingresa tu contraseña actual" required>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Nueva Contraseña</label>
                            <input type="password" name="password_nueva" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Confirmar Nueva Contraseña</label>
                            <input type="password" name="password_confirmar" class="form-control" placeholder="Repite la nueva contraseña" required minlength="6">
                        </div>
                    </div>
                    
                    <div class="text-end mt-2">
                        <button type="submit" class="btn btn-dark rounded-pill fw-bold px-4 shadow-sm">🔒 Actualizar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mb-5">
            <a href="logout.php" class="btn btn-outline-danger rounded-pill fw-bold px-4">Cerrar Sesión</a>
        </div>
    </div>
</div>