<?php
require 'admin_auth.php';
require 'conexion.php';

$mensaje = "";
$error = "";

// Agregar snack
if (isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock  = intval($_POST['stock'] ?? 0);

    if ($nombre && $precio > 0) {
        $stmt = $conn->prepare("INSERT INTO snacks (nombre, precio, stock) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $nombre, $precio, $stock);
        $stmt->execute();
        $mensaje = "Producto agregado correctamente.";
    } else {
        $error = "Completá nombre y precio.";
    }
}

// Actualizar stock / precio
if (isset($_POST['actualizar'])) {
    $id = intval($_POST['id_snack']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE snacks SET precio = ?, stock = ?, activo = ? WHERE id_snack = ?");
    $stmt->bind_param("diii", $precio, $stock, $activo, $id);
    $stmt->execute();
    $mensaje = "Producto actualizado.";
}

// Eliminar
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM snacks WHERE id_snack = $id");
    $mensaje = "Producto eliminado.";
}

$snacks = $conn->query("SELECT * FROM snacks ORDER BY nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Snacks - Admin</title>
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
        .form-grid { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 12px; align-items: end; }
        label { font-size: 0.85rem; color: #aaa; display: block; margin-bottom: 4px; }
        input {
            width: 100%; background: #111; border: 1px solid #333; border-radius: 8px;
            padding: 10px 12px; color: #fff; font-size: 1rem; outline: none;
        }
        input:focus { border-color: #e50914; }
        .btn {
            background: #e50914; color: #fff; border: none; padding: 11px 18px;
            border-radius: 8px; font-weight: 600; cursor: pointer;
        }
        .btn:hover { background: #f40612; }
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; }
        .btn-gray { background: #333; }
        .btn-gray:hover { background: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #2a2a2a; }
        th { color: #aaa; font-size: 0.9rem; }
        .mensaje { background: #0f3d1a; color: #86efac; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #3b0a0a; color: #fca5a5; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .stock-bajo { color: #fca5a5; font-weight: 700; }
        @media (max-width: 700px) { .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<header>
    <a href="admin_panel.php" class="logo">CineNex <span>| Admin</span></a>
    <a href="admin_panel.php" class="volver">← Volver</a>
</header>

<div class="container">
    <h1>Stock de comidas y bebidas</h1>
    <p class="subtitulo">Cargá productos y controlá el inventario</p>

    <?php if ($mensaje): ?><div class="mensaje"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="box">
        <h2 style="margin-bottom:15px; font-size:1.1rem;">Agregar producto</h2>
        <form method="POST">
            <div class="form-grid">
                <div>
                    <label>Nombre</label>
                    <input type="text" name="nombre" required placeholder="Ej: Pochoclo Grande">
                </div>
                <div>
                    <label>Precio</label>
                    <input type="number" name="precio" step="0.01" min="0" required placeholder="40">
                </div>
                <div>
                    <label>Stock</label>
                    <input type="number" name="stock" min="0" value="0" required>
                </div>
                <button type="submit" name="agregar" class="btn">Agregar</button>
            </div>
        </form>
    </div>

    <div class="box">
        <h2 style="margin-bottom:15px; font-size:1.1rem;">Inventario actual</h2>
        <?php if ($snacks && $snacks->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($s = $snacks->fetch_assoc()): ?>
                        <tr>
                            <form method="POST">
                                <td>
                                    <strong><?= htmlspecialchars($s['nombre']) ?></strong>
                                    <input type="hidden" name="id_snack" value="<?= $s['id_snack'] ?>">
                                </td>
                                <td>
                                    <input type="number" name="precio" value="<?= $s['precio'] ?>" step="0.01" style="width:90px;">
                                </td>
                                <td>
                                    <input type="number" name="stock" value="<?= $s['stock'] ?>" min="0" style="width:80px;"
                                           class="<?= $s['stock'] <= 10 ? 'stock-bajo' : '' ?>">
                                </td>
                                <td>
                                    <input type="checkbox" name="activo" <?= $s['activo'] ? 'checked' : '' ?>>
                                </td>
                                <td>
                                    <button type="submit" name="actualizar" class="btn btn-sm">Guardar</button>
                                    <a href="?eliminar=<?= $s['id_snack'] ?>" class="btn btn-sm btn-gray"
                                       onclick="return confirm('¿Eliminar este producto?')">Eliminar</a>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#666;">No hay productos cargados.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>