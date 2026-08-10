<?php
// proyecto.php
$id_proyecto = $_GET['id'] ?? null;

if (!$id_proyecto) {
    header("Location: index.php");
    exit;
}

// Obtener todos los proyectos desde la API de Render
$api_url = "https://rzc-telegram-bot.onrender.com/api/proyectos";
$json_data = @file_get_contents($api_url);

$proyecto = null;
if ($json_data !== FALSE) {
    $data = json_decode($json_data, true);
    if (!isset($data['error']) && is_array($data)) {
        // Buscar el proyecto exacto que coincide con el ID
        foreach ($data as $p) {
            if ($p['id'] == $id_proyecto) {
                $proyecto = $p;
                break;
            }
        }
    }
}

// Si no se encuentra el proyecto
if (!$proyecto) {
    echo '<div style="text-align:center; margin-top:80px; font-family:sans-serif;">
            <h2>Proyecto no encontrado</h2>
            <p class="text-muted">El recurso que buscas ya no está disponible o el ID es incorrecto.</p>
            <br><a href="index.php" style="padding: 10px 20px; background: #212529; color: #fff; text-decoration: none; border-radius: 50px;">Volver al inicio</a>
          </div>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($proyecto['titulo']) ?> - Proyectos Premium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <a href="index.php" class="btn btn-outline-dark rounded-pill mb-4 fw-bold">&larr; Volver al catálogo</a>
        
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <img src="<?= htmlspecialchars($proyecto['imagen_url']) ?>" alt="Portada" class="img-fluid rounded-4 shadow-sm w-100" style="max-height: 400px; object-fit: cover;">
            </div>
            
            <div class="col-lg-6 d-flex flex-column justify-content-between">
                <div>
                    <span class="badge bg-dark rounded-pill mb-3"><?= htmlspecialchars($proyecto['categoria']) ?></span>
                    <h1 class="fw-bold mb-3"><?= htmlspecialchars($proyecto['titulo']) ?></h1>
                    <p class="text-muted small mb-4">Publicado el: <?= htmlspecialchars($proyecto['fecha_publicacion'] ?? 'Reciente') ?></p>
                    <p class="lead text-secondary" style="white-space: pre-line; font-size: 1.05rem;"><?= htmlspecialchars($proyecto['descripcion']) ?></p>
                </div>
                
                <div class="mt-4">
                    <?php if(!empty($proyecto['telegram_file_id'])): ?>
                        <a href="https://rzc-telegram-bot.onrender.com/api/bajar_archivo?file_id=<?= urlencode($proyecto['telegram_file_id']) ?>&nombre=<?= urlencode($proyecto['titulo']) ?>" class="btn btn-dark btn-lg w-100 rounded-pill fw-bold shadow-sm py-3">
                            📥 Descargar Archivo / Recurso
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg w-100 rounded-pill fw-bold py-3" disabled>Archivo no disponible</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>