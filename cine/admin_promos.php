<?php
require 'admin_auth.php';
require 'conexion.php';

$mensaje = "";
$error = "";

if (isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $desc_porc = floatval($_POST['descuento_porcentaje'] ?? 0);
    $desc_fijo = floatval($_POST['descuento_fijo'] ?? 0);
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;

    if ($nombre) {
        $stmt = $conn->prepare("
            INSERT INTO promos (nombre, descripcion, descuento_porcentaje, descuento_fijo, fecha_inicio, fecha_fin)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssddss", $nombre, $descripcion, $desc_porc, $desc_fijo, $fecha_inicio, $fecha_fin);
        $stmt->execute();
        $mensaje = "Promo creada correctamente.";
    } else {
        $error = "El nombre es obligatorio.";
    }
}

if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE promos SET activa = IF(activa=1, 0, 1) WHERE id_promo = $id");
    $mensaje = "Estado de la promo actualizado.";
}

if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM promos WHERE id_promo = $id");
    $mensaje = "Promo eliminada.";
}

$promos = $conn->query("SELECT * FROM promos ORDER BY fecha_creacion DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promos - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #0f0f0f; color: #e5e5e5; min-height: 100vh; }
        header {
            background: #141414; padding: 16px 30px; border-bottom: 1px solid #222;
            display: flex; justify-content: space-between; align-items: center;
        }
        .logo { font-size: 1.5rem; font-weight: 800; color: #e50914; text-decoration: none; }
        .logo span { color: #fff; font-weight: 400; font-size: 0.95rem; }
        .volver { color: #aaa; text-decoration: none; }
        .volver:hover { color: #fff; }
        .container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
        h1 { margin-bottom: 8px; }
        .subtitulo { color: #888; margin-bottom: 30px; }
        .box {
            background: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 14px;
            padding: 24px; margin-bottom: 25px;
        }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group.full { grid-column: 1 / -1; }
        label { font-size: 0.85rem; color: #aaa; }
        input, textarea {
            background: #111; border: 1px solid #333; border-radius: 8px;
            padding: 10px 12px; color: #fff; font-size: 1rem; outline: none;
        }
        input:focus, textarea:focus { border-color: #e50914; }
        textarea { min-height: 70px; resize: vertical; }
        .btn {
            background: #e50914; color: #fff; border: none; padding: 12px 20px;
            border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px;
        }
        .btn:hover { background: #f40612; }
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; text-decoration: none; display: inline-block; }
        .btn-gray { background: #333; color: #ddd; }
        .btn-gray:hover { background: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #2a2a2a; }
        th { color: #aaa; font-size: 0.9rem; }
        .badge {
            padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;
        }
        .badge.on { background: #0f3d1a; color: #86efac; }
        .badge.off { background: #333; color: #999; }
        .mensaje { background: #0f3d1a; color: #86efac; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #3b0a0a; color: #fca5a5; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<header>
    <a href="admin_panel.php" class="logo">CineNex <span>| Admin</span></a>
    <a href="admin_panel.php" class="volver">← Volver</a>
</header>

<div class="container">
    <h1>Crear promos</h1>
    <p class="subtitulo">Descuentos y promociones del cine</p>

    <?php if ($mensaje): ?><div class="mensaje"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="box">
        <h2 style="margin-bottom:15px; font-size:1.1rem;">Nueva promo</h2>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Nombre de la promo *</label>
                    <input type="text" name="nombre" required placeholder="Ej: 2x1 Martes">
                </div>
                <div class="form-group full">
                    <label>Descripción</label>
                    <textarea name="descripcion" placeholder="Detalle de la promoción..."></textarea>
                </div>
                <div class="form-group">
                    <label>Descuento (%)</label>
                    <input type="number" name="descuento_porcentaje" step="0.01" min="0" max="100" value="0">
                </div>
                <div class="form-group">
                    <label>Descuento fijo ($)</label>
                    <input type="number" name="descuento_fijo" step="0.01" min="0" value="0">
                </div>
                <div class="form-group">
                    <label>Fecha inicio</label>
                    <input type="date" name="fecha_inicio">
                </div>
                <div class="form-group">
                    <label>Fecha fin</label>
                    <input type="date" name="fecha_fin">
                </div>
            </div>
            <button type="submit" name="crear" class="btn">Crear promo</button>
        </form>
    </div>

    <div class="box">
        <h2 style="margin-bottom:15px; font-size:1.1rem;">Promos existentes</h2>
        <?php if ($promos && $promos->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descuento</th>
                        <th>Vigencia</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = $promos->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($p['nombre']) ?></strong><br>
                                <small style="color:#888;"><?= htmlspecialchars($p['descripcion'] ?? '') ?></small>
                            </td>
                            <td>
                                <?php if ($p['descuento_porcentaje'] > 0): ?>
                                    <?= $p['descuento_porcentaje'] ?>%
                                <?php endif; ?>
                                <?php if ($p['descuento_fijo'] > 0): ?>
                                    $<?= number_format($p['descuento_fijo'], 2) ?>
                                <?php endif; ?>
                                <?php if ($p['descuento_porcentaje'] == 0 && $p['descuento_fijo'] == 0): ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $p['fecha_inicio'] ? date('d/m/Y', strtotime($p['fecha_inicio'])) : '—' ?>
                                —
                                <?= $p['fecha_fin'] ? date('d/m/Y', strtotime($p['fecha_fin'])) : '—' ?>
                            </td>
                            <td>
                                <span class="badge <?= $p['activa'] ? 'on' : 'off' ?>">
                                    <?= $p['activa'] ? 'Activa' : 'Inactiva' ?>
                                </span>
                            </td>
                            <td>
                                <a href="?toggle=<?= $p['id_promo'] ?>" class="btn btn-sm btn-gray">
                                    <?= $p['activa'] ? 'Desactivar' : 'Activar' ?>
                                </a>
                                <a href="?eliminar=<?= $p['id_promo'] ?>" class="btn btn-sm btn-gray"
                                   onclick="return confirm('¿Eliminar esta promo?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#666;">No hay promos creadas.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>