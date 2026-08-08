<!-- views/admin/dashboard.php -->
<div class="row mb-4">
    <div class="col">
        <h2 class="fw-bold">Panel de Administración</h2>
        <p class="text-muted">Gestiona las activaciones manuales de los planes Premium.</p>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4" style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
    <div class="card-body p-4">
        <h4 class="fw-bold mb-4">Pagos Pendientes</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle bg-transparent">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Plan</th>
                        <th>Referencia</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($pagos)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay pagos pendientes.</td></tr>
                    <?php else: ?>
                        <?php foreach($pagos as $pago): ?>
                        <tr>
                            <td><?= htmlspecialchars($pago['nombre']) ?></td>
                            <td><span class="badge bg-dark rounded-pill"><?= ucfirst($pago['plan']) ?></span></td>
                            <td class="fw-bold text-success"><?= htmlspecialchars($pago['comprobante_ref']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($pago['fecha_solicitud'])) ?></td>
                            <td>
                                <!-- En el próximo paso crearemos este archivo -->
                                <a href="aprobar.php?id=<?= $pago['id'] ?>" class="btn btn-sm btn-success rounded-pill px-3">Aprobar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>