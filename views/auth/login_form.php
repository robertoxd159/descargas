<!-- views/auth/login_form.php -->
<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 rounded-4 p-4" style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
            <h3 class="text-center mb-4 fw-bold">Iniciar Sesión</h3>
            
            <?= $mensaje ?? '' ?>
            
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Correo electrónico</label>
                    <input type="email" name="email" class="form-control form-control-lg bg-transparent text-reset" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">Contraseña</label>
                    <input type="password" name="password" class="form-control form-control-lg bg-transparent text-reset" required>
                </div>
                <button type="submit" class="btn btn-dark w-100 btn-lg rounded-pill mb-3">Entrar</button>
            </form>
            
            <div class="text-center">
                <small class="text-muted">¿No tienes cuenta? <a href="register.php" class="text-reset fw-bold">Regístrate</a></small>
            </div>
        </div>
    </div>
</div>