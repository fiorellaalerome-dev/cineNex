<?php
// Conexión a la base de datos
$host = "localhost";
$usuario = "root";          // Cambia si tenés otro usuario
$clave = "";                // Cambia si tenés contraseña
$base_datos = "cine";

$conn = new mysqli($host, $usuario, $clave, $base_datos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener el ID de la película
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Consulta de la película + género + precio más bajo de sus funciones
$sql = "
    SELECT 
        p.id_pelicula,
        p.titulo,
        p.descripcion,
        p.duracion_minutos,
        p.imagen_url,
        g.nombre AS genero,
        MIN(f.precio) AS precio
    FROM peliculas p
    INNER JOIN generos g ON p.id_genero = g.id_genero
    LEFT JOIN funciones f ON p.id_pelicula = f.id_pelicula
    WHERE p.id_pelicula = ? AND p.activa = 1
    GROUP BY p.id_pelicula
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$pelicula = $resultado->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pelicula['titulo']) ?> - CineNex</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #0f0f0f;
            color: #e5e5e5;
            min-height: 100vh;
        }

        /* ENCABEZADO */
        header {
            position: sticky;
            top: 0;
            height: 70px;
            background: #141414;
            display: flex;
            align-items: center;
            padding: 0 30px;
            border-bottom: 1px solid #222;
            z-index: 100;
        }

        .logo {
            font-size: 1.7rem;
            font-weight: 800;
            color: #e50914;
            text-decoration: none;
        }

        .logo span {
            color: #fff;
            font-weight: 400;
            font-size: 1rem;
        }

        .volver {
            margin-left: auto;
            color: #aaa;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.2s;
        }

        .volver:hover {
            color: #fff;
        }

        /* CONTENIDO */
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 50px 20px;
        }

        .pelicula-detalle {
            display: flex;
            gap: 50px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .poster {
            flex: 0 0 320px;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.6);
            border: 1px solid #2a2a2a;
        }

        .poster img {
            width: 100%;
            height: auto;
            display: block;
            background: #1a1a1a;
        }

        .info {
            flex: 1;
            min-width: 300px;
        }

        .titulo {
            font-size: 2.4rem;
            font-weight: 800;
            margin-bottom: 12px;
            line-height: 1.2;
            color: #fff;
        }

        .genero {
            display: inline-block;
            background: #e50914;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }

        .meta {
            display: flex;
            gap: 25px;
            margin-bottom: 25px;
            color: #aaa;
            font-size: 1rem;
        }

        .meta strong {
            color: #fff;
        }

        .descripcion {
            font-size: 1.1rem;
            line-height: 1.7;
            color: #ccc;
            margin-bottom: 35px;
            max-width: 600px;
        }

        .precio-box {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 20px 25px;
            display: inline-block;
            margin-bottom: 30px;
        }

        .precio-label {
            font-size: 0.9rem;
            color: #888;
            margin-bottom: 5px;
        }

        .precio {
            font-size: 2rem;
            font-weight: 700;
            color: #e50914;
        }

        .botones {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-comprar {
            background: #e50914;
            color: #fff;
        }

        .btn-comprar:hover {
            background: #f40612;
            transform: translateY(-2px);
        }

        .btn-volver {
            background: #2a2a2a;
            color: #ddd;
        }

        .btn-volver:hover {
            background: #3a3a3a;
        }

        /* RESPONSIVE */
        @media (max-width: 800px) {
            .pelicula-detalle {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .poster {
                flex: 0 0 auto;
                max-width: 280px;
            }

            .info {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .descripcion {
                text-align: center;
            }

            .meta {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">CineNex <span>| Detalle</span></a>
    <a href="index.php" class="volver">← Volver a cartelera</a>
</header>

<div class="container">
    <div class="pelicula-detalle">
        
        <!-- POSTER -->
        <div class="poster">
            <img 
                src="<?= htmlspecialchars($pelicula['imagen_url'] ?? 'https://via.placeholder.com/500x750/1a1a1a/666?text=Sin+Imagen') ?>" 
                alt="<?= htmlspecialchars($pelicula['titulo']) ?>"
                onerror="this.src='https://via.placeholder.com/500x750/1a1a1a/666?text=Sin+Imagen'"
            >
        </div>

        <!-- INFORMACIÓN -->
        <div class="info">
            <h1 class="titulo"><?= htmlspecialchars($pelicula['titulo']) ?></h1>
            
            <span class="genero"><?= htmlspecialchars($pelicula['genero']) ?></span>

            <div class="meta">
                <div>
                    <strong>Duración:</strong> <?= (int)$pelicula['duracion_minutos'] ?> min
                </div>
            </div>

            <p class="descripcion">
                <?= nl2br(htmlspecialchars($pelicula['descripcion'] ?? 'Sin descripción disponible.')) ?>
            </p>

            <div class="precio-box">
                <div class="precio-label">Precio desde</div>
                <div class="precio">
                    $<?= number_format($pelicula['precio'] ?? 0, 2) ?>
                </div>
            </div>

            <div class="botones">
                <a href="compra.php?id_pelicula=<?= (int)$pelicula['id_pelicula'] ?>" class="btn btn-comprar">
                    Comprar boletos
                </a>
                <a href="index.php" class="btn btn-volver">
                    Volver a cartelera
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>