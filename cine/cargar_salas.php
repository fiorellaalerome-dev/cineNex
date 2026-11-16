<?php
require 'admin_auth.php';
require 'conexion.php';

$mensaje = "";
$error = "";

// Procesar formulario de nueva sala
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre'] ?? '');
    $capacidad = intval($_POST['capacidad'] ?? 0);
    $filas = trim($_POST['filas'] ?? 'A,B,C,D,E,F,G');
    $asientos_por_fila = intval($_POST['asientos_por_fila'] ?? 10);

    if (empty($nombre) || $capacidad <= 0) {
        $error = "Completá el nombre y la capacidad de la sala.";
    } else {
        // Verificar si ya existe una sala con ese nombre
        $check = $conn->prepare("SELECT id_sala FROM salas WHERE nombre = ?");
        $check->bind_param("s", $nombre);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Ya existe una sala con ese nombre.";
        } else {
            // Insertar la sala
            $stmt = $conn->prepare("INSERT INTO salas (nombre, capacidad) VALUES (?, ?)");
            $stmt->bind_param("si", $nombre, $capacidad);

            if ($stmt->execute()) {
                $id_sala = $conn->insert_id;

                // Generar asientos automáticamente
                $lista_filas = array_map('trim', explode(',', strtoupper($filas)));
                $asientos_creados = 0;

                foreach ($lista_filas as $fila) {
                    if (empty($fila)) continue;

                    for ($num = 1; $num <= $asientos_por_fila; $num++) {
                        $codigo = $fila . $num;

                        $stmt2 = $conn->prepare("
                            INSERT INTO asientos (id_sala, fila, numero, codigo)
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt2->bind_param("isis", $id_sala, $fila, $num, $codigo);
                        if ($stmt2->execute()) {
                            $asientos_creados++;
                        }
                        $stmt2->close();
                    }
                }

                $mensaje = "Sala <strong>$nombre</strong> creada correctamente. Se generaron $asientos_creados asientos.";
            } else {
                $error = "Error al crear la sala: " . $conn->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}

// Obtener salas existentes
$salas = $conn->query("
    SELECT s.id_sala, s.nombre, s.capacidad,
           (SELECT COUNT(*) FROM asientos a WHERE a.id_sala = s.id_sala) AS total_asientos
    FROM salas s
    ORDER BY s.nombre
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Salas - Admin CineNex</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0f0f0f;
            color: #e5e5e5;
            min-height: 100vh;
        }
        header {
            background: #141414;
            padding: 16px 30px;
            border-bottom: 1px solid #222;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: #e50914;
            text-decoration: none;
        }
        .logo span { color: #fff; font-weight: 400; font-size: 0.95rem; }
        .volver {
            color: #aaa;
            text-decoration: none;
            font-size: 0.95rem;
        }
        .volver:hover { color: #fff; }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
        }
        .subtitulo {
            color: #888;
            margin-bottom: 30px;
        }

        .form-box, .lista-box {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 14px;
            padding: 28px;
            margin-bottom: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group.full {
            grid-column: 1 / -1;
        }
        label {
            font-size: 0.9rem;
            color: #aaa;
        }
        input {
            background: #111;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 11px 13px;
            color: #fff;
            font-size: 1rem;
            outline: none;
        }
        input:focus {
            border-color: #e50914;
        }

        .btn-guardar {
            background: #e50914;
            color: #fff;
            border: none;
            padding: 13px 28px;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
        }
        .btn-guardar:hover {
            background: #f40612;
        }

        .mensaje {
            background: #0f3d1a;
            border: 1px solid #22c55e;
            color: #86efac;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error {
            background: #3b0a0a;
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #2a2a2a;
        }
        th {
            background: #222;
            color: #aaa;
            font-weight: 600;
            font-size: 0.9rem;
        }
        tr:hover {
            background: #222;
        }
        .badge {
            background: #333;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<header>
    <a href="admin_panel.php" class="logo">CineNex <span>| Admin</span></a>
    <a href="admin_panel.php" class="volver">← Volver al panel</a>
</header>

<div class="container">
    <h1>Cargar Salas</h1>
    <p class="subtitulo">Creá nuevas salas y generá automáticamente sus asientos</p>

    <?php if ($mensaje): ?>
        <div class="mensaje"><?= $mensaje ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- FORMULARIO NUEVA SALA -->
    <div class="form-box">
        <h2 style="margin-bottom:20px; font-size:1.2rem;">Nueva Sala</h2>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre de la sala *</label>
                    <input type="text" name="nombre" required placeholder="Ej: Sala 1, Sala VIP, Sala 3D">
                </div>

                <div class="form-group">
                    <label>Capacidad (cantidad de asientos) *</label>
                    <input type="number" name="capacidad" required min="1" placeholder="Ej: 70">
                </div>

                <div class="form-group">
                    <label>Filas (separadas por coma)</label>
                    <input type="text" name="filas" value="A,B,C,D,E,F,G" placeholder="A,B,C,D,E,F,G">
                </div>

                <div class="form-group">
                    <label>Asientos por fila</label>
                    <input type="number" name="asientos_por_fila" value="10" min="1" max="20">
                </div>
            </div>

            <button type="submit" class="btn-guardar">Crear sala y generar asientos</button>
        </form>
    </div>

    <!-- LISTA DE SALAS EXISTENTES -->
    <div class="lista-box">
        <h2 style="margin-bottom:20px; font-size:1.2rem;">Salas existentes</h2>

        <?php if ($salas && $salas->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Capacidad</th>
                        <th>Asientos generados</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($s = $salas->fetch_assoc()): ?>
                        <tr>
                            <td><?= $s['id_sala'] ?></td>
                            <td><strong><?= htmlspecialchars($s['nombre']) ?></strong></td>
                            <td><?= $s['capacidad'] ?></td>
                            <td><span class="badge"><?= $s['total_asientos'] ?> asientos</span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#666;">Todavía no hay salas cargadas.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>