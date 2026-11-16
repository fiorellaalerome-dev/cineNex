<?php
require 'admin_auth.php';
require 'conexion.php';

// Obtener todas las funciones de HOY
$sql = "
    SELECT 
        f.id_funcion,
        f.fecha_hora,
        f.precio,
        p.titulo,
        s.nombre AS sala,
        (SELECT COUNT(*) 
         FROM detalle_reserva dr 
         JOIN reservas r ON dr.id_reserva = r.id_reserva 
         WHERE r.id_funcion = f.id_funcion 
           AND r.estado = 'confirmada') AS entradas_vendidas
    FROM funciones f
    JOIN peliculas p ON f.id_pelicula = p.id_pelicula
    JOIN salas s ON f.id_sala = s.id_sala
    WHERE DATE(f.fecha_hora) = CURDATE()
    ORDER BY f.fecha_hora ASC
";

$funciones = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Entradas - Admin CineNex</title>
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
            max-width: 1100px;
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

        .leyenda {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .leyenda-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #aaa;
        }
        .cuadro {
            width: 18px;
            height: 18px;
            border-radius: 4px;
        }
        .cuadro.normal { background: #1a1a1a; border: 1px solid #333; }
        .cuadro.urgente { background: #3b0a0a; border: 1px solid #e50914; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #1a1a1a;
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 14px 16px;
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

        /* Fila en ROJO cuando falta menos de 1 hora */
        tr.urgente {
            background: #3b0a0a !important;
        }
        tr.urgente td {
            color: #fca5a5;
        }
        tr.urgente:hover {
            background: #4a1010 !important;
        }

        .badge-rojo {
            background: #e50914;
            color: #fff;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .badge-normal {
            background: #333;
            color: #ccc;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .btn-ver {
            background: #e50914;
            color: #fff;
            padding: 7px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-block;
        }
        .btn-ver:hover {
            background: #f40612;
        }

        .sin-datos {
            text-align: center;
            padding: 50px 20px;
            color: #666;
            font-size: 1.1rem;
        }

        /* Modal / detalle de asientos */
        .detalle-box {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
            display: none;
        }
        .detalle-box.activo {
            display: block;
        }
        .detalle-box h2 {
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        .asientos-lista {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
        }
        .asiento-tag {
            background: #e50914;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .info-funcion {
            color: #aaa;
            margin-bottom: 10px;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<header>
    <a href="admin_panel.php" class="logo">CineNex <span>| Admin</span></a>
    <a href="admin_panel.php" class="volver">← Volver al panel</a>
</header>

<div class="container">
    <h1>Ver Entradas del Día</h1>
    <p class="subtitulo">Funciones de hoy · Las que empiezan en menos de 1 hora se marcan en rojo</p>

    <div class="leyenda">
        <div class="leyenda-item">
            <div class="cuadro normal"></div>
            Función normal
        </div>
        <div class="leyenda-item">
            <div class="cuadro urgente"></div>
            Empieza en menos de 1 hora (urgente)
        </div>
    </div>

    <?php if ($funciones && $funciones->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Horario</th>
                    <th>Película</th>
                    <th>Sala</th>
                    <th>Entradas vendidas</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $ahora = new DateTime();
                while ($f = $funciones->fetch_assoc()): 
                    $hora_funcion = new DateTime($f['fecha_hora']);
                    $diferencia = $ahora->diff($hora_funcion);
                    
                    // Calcular minutos restantes (puede ser negativo si ya pasó)
                    $minutos_restantes = ($hora_funcion->getTimestamp() - $ahora->getTimestamp()) / 60;
                    
                    $es_urgente = ($minutos_restantes <= 60 && $minutos_restantes >= -30);
                ?>
                    <tr class="<?= $es_urgente ? 'urgente' : '' ?>">
                        <td>
                            <strong><?= date('H:i', strtotime($f['fecha_hora'])) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($f['titulo']) ?></td>
                        <td><?= htmlspecialchars($f['sala']) ?></td>
                        <td>
                            <strong><?= $f['entradas_vendidas'] ?></strong> entradas
                        </td>
                        <td>
                            <?php if ($es_urgente): ?>
                                <span class="badge-rojo">¡PRÓXIMA!</span>
                            <?php elseif ($minutos_restantes < -30): ?>
                                <span class="badge-normal">Finalizada</span>
                            <?php else: ?>
                                <span class="badge-normal">Programada</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="admin_ver_funcion.php?id=<?= $f['id_funcion'] ?>" class="btn-ver">
                                Ver asientos
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="sin-datos">
            No hay funciones programadas para hoy.
        </div>
    <?php endif; ?>
</div>

</body>
</html>