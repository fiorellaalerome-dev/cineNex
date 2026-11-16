<?php
require 'admin_auth.php';
require 'conexion.php';

$mensaje = "";
$error = "";

// Obtener géneros y salas para los selects
$generos = $conn->query("SELECT id_genero, nombre FROM generos ORDER BY nombre");
$salas = $conn->query("SELECT id_sala, nombre FROM salas ORDER BY nombre");

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $id_genero = intval($_POST['id_genero'] ?? 0);
    $duracion = intval($_POST['duracion'] ?? 0);
    $imagen_url = trim($_POST['imagen_url'] ?? '');
    $fecha_estreno = $_POST['fecha_estreno'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $precio = floatval($_POST['precio'] ?? 0);

    // Arrays de salas y horarios
    $salas_seleccionadas = $_POST['salas'] ?? [];
    $horarios = $_POST['horarios'] ?? [];

    if (empty($titulo) || $id_genero <= 0 || $duracion <= 0 || empty($fecha_estreno) || empty($fecha_fin) || $precio <= 0) {
        $error = "Por favor completá todos los campos obligatorios.";
    } elseif (empty($salas_seleccionadas) || empty($horarios)) {
        $error = "Debés seleccionar al menos una sala y un horario.";
    } else {

        // 1. Insertar la película
        $stmt = $conn->prepare("
            INSERT INTO peliculas (titulo, descripcion, duracion_minutos, id_genero, imagen_url, activa)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt->bind_param("ssiis", $titulo, $descripcion, $duracion, $id_genero, $imagen_url);

        if ($stmt->execute()) {
            $id_pelicula = $conn->insert_id;

            // 2. Crear las funciones (combinación de salas + horarios entre estreno y fin)
            $fecha_actual = new DateTime($fecha_estreno);
            $fecha_final = new DateTime($fecha_fin);

            $funciones_creadas = 0;

            while ($fecha_actual <= $fecha_final) {
                $fecha_str = $fecha_actual->format('Y-m-d');

                foreach ($salas_seleccionadas as $id_sala) {
                    foreach ($horarios as $hora) {
                        if (empty($hora)) continue;

                        $fecha_hora = $fecha_str . ' ' . $hora . ':00';

                        $stmt2 = $conn->prepare("
                            INSERT INTO funciones (id_pelicula, id_sala, fecha_hora, precio)
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt2->bind_param("iisd", $id_pelicula, $id_sala, $fecha_hora, $precio);
                        
                        if ($stmt2->execute()) {
                            $funciones_creadas++;
                        }
                        $stmt2->close();
                    }
                }

                $fecha_actual->modify('+1 day');
            }

            $mensaje = "Película cargada correctamente. Se crearon $funciones_creadas funciones.";
        } else {
            $error = "Error al guardar la película: " . $conn->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Películas - Admin CineNex</title>
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
            max-width: 800px;
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

        .form-box {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 14px;
            padding: 30px;
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
        input, select, textarea {
            background: #111;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 11px 13px;
            color: #fff;
            font-size: 1rem;
            outline: none;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #e50914;
        }
        textarea {
            resize: vertical;
            min-height: 90px;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 5px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #222;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
        }
        .checkbox-item input {
            width: auto;
        }

        .horarios-box {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 8px;
        }
        .horario-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .horario-item input[type="time"] {
            width: 130px;
        }

        .btn-agregar-horario {
            background: #333;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn-agregar-horario:hover {
            background: #444;
        }

        .btn-guardar {
            background: #e50914;
            color: #fff;
            border: none;
            padding: 14px 30px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 25px;
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
    <h1>Cargar Película</h1>
    <p class="subtitulo">Completá los datos de la película y sus funciones</p>

    <?php if ($mensaje): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="form-box">
        <form method="POST">

            <div class="form-grid">
                <!-- Nombre -->
                <div class="form-group full">
                    <label>Nombre de la película *</label>
                    <input type="text" name="titulo" required placeholder="Ej: Dune: Parte Dos">
                </div>

                <!-- Descripción -->
                <div class="form-group full">
                    <label>Descripción / Resumen</label>
                    <textarea name="descripcion" placeholder="Breve resumen de la película..."></textarea>
                </div>

                <!-- Género -->
                <div class="form-group">
                    <label>Género *</label>
                    <select name="id_genero" required>
                        <option value="">Seleccionar género</option>
                        <?php while ($g = $generos->fetch_assoc()): ?>
                            <option value="<?= $g['id_genero'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Duración -->
                <div class="form-group">
                    <label>Duración (minutos) *</label>
                    <input type="number" name="duracion" required min="1" placeholder="Ej: 150">
                </div>

                <!-- Imagen -->
                <div class="form-group full">
                    <label>URL de la imagen (póster)</label>
                    <input type="url" name="imagen_url" placeholder="https://...">
                </div>

                <!-- Fecha estreno -->
                <div class="form-group">
                    <label>Día de estreno *</label>
                    <input type="date" name="fecha_estreno" required>
                </div>

                <!-- Fecha fin -->
                <div class="form-group">
                    <label>Día en que se saca la película *</label>
                    <input type="date" name="fecha_fin" required>
                </div>

                <!-- Precio -->
                <div class="form-group">
                    <label>Precio del boleto *</label>
                    <input type="number" name="precio" required min="1" step="0.01" placeholder="Ej: 1200">
                </div>

                <!-- Salas -->
                <div class="form-group full">
                    <label>Salas en las que estará *</label>
                    <div class="checkbox-group">
                        <?php 
                        $salas->data_seek(0); // Reiniciar puntero
                        while ($s = $salas->fetch_assoc()): 
                        ?>
                            <label class="checkbox-item">
                                <input type="checkbox" name="salas[]" value="<?= $s['id_sala'] ?>">
                                <?= htmlspecialchars($s['nombre']) ?>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Horarios -->
                <div class="form-group full">
                    <label>Horarios *</label>
                    <div class="horarios-box" id="horarios-container">
                        <div class="horario-item">
                            <input type="time" name="horarios[]" required>
                        </div>
                    </div>
                    <button type="button" class="btn-agregar-horario" onclick="agregarHorario()" style="margin-top:10px;">
                        + Agregar otro horario
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-guardar">Guardar película y funciones</button>
        </form>
    </div>
</div>

<script>
function agregarHorario() {
    const container = document.getElementById('horarios-container');
    const div = document.createElement('div');
    div.className = 'horario-item';
    div.innerHTML = `<input type="time" name="horarios[]" required>`;
    container.appendChild(div);
}
</script>

</body>
</html>