<!-- views/pages/checkout_form.php -->
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 rounded-4 p-4" style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
            <h3 class="text-center mb-4 fw-bold">Pagar Plan <?= ucfirst($plan) ?></h3>
            
            <div class="alert alert-info rounded-3 border-0 text-center">
                Monto a pagar: <strong>S/<?= $precio ?></strong>
            </div>

            <div class="text-center mb-4">
                <p class="mb-1 fw-bold">1. Escanea o envía por Yape / Plin al:</p>
                <h4 class="fw-bold text-success">📱 923 481 905</h4>
                <small class="text-muted">A nombre de: Roberto (PremiumDev)</small>
            </div>

            <?= $mensaje ?? '' ?>

            <form action="checkout.php?plan=<?= $plan ?>" method="POST">
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">2. Ingresa el número de operación / referencia</label>
                    <input type="text" name="referencia" class="form-control form-control-lg bg-transparent text-reset" placeholder="Ej: 12345678" required>
                </div>
                <button type="submit" class="btn btn-dark w-100 btn-lg rounded-pill">Enviar pago y notificar</button>
            </form>
        </div>
    </div>
</div>