<?php
// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$clave = "";
$base_datos = "cine";

$conn = new mysqli($host, $usuario, $clave, $base_datos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener películas activas con su género y precio más bajo
$sql = "
    SELECT 
        p.id_pelicula,
        p.titulo,
        p.descripcion,
        p.imagen_url,
        g.nombre AS genero,
        MIN(f.precio) AS precio
    FROM peliculas p
    INNER JOIN generos g ON p.id_genero = g.id_genero
    LEFT JOIN funciones f ON p.id_pelicula = f.id_pelicula
    WHERE p.activa = 1
    GROUP BY p.id_pelicula
    ORDER BY p.titulo
";

$resultado = $conn->query($sql);
$peliculas = [];

if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $peliculas[] = $fila;
    }
}

// Obtener todos los géneros para el menú lateral
$generos = [];
$res_generos = $conn->query("SELECT nombre FROM generos ORDER BY nombre");
if ($res_generos) {
    while ($g = $res_generos->fetch_assoc()) {
        $generos[] = $g['nombre'];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineNex - Cartelera</title>
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
            display: flex;
            min-height: 100vh;
        }

        /* ENCABEZADO */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: linear-gradient(180deg, #141414 0%, rgba(20,20,20,0.95) 100%);
            display: flex;
            align-items: center;
            padding: 0 30px;
            z-index: 100;
            border-bottom: 1px solid #222;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: #e50914;
            letter-spacing: -1px;
            text-decoration: none;
        }

        .logo span {
            color: #fff;
            font-weight: 400;
            font-size: 1.1rem;
        }

        .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-right a {
            color: #aaa;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.2s;
        }

        .header-right a:hover {
            color: #fff;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 70px;
            left: 0;
            width: 240px;
            height: calc(100vh - 70px);
            background: #141414;
            border-right: 1px solid #222;
            padding: 25px 0;
            overflow-y: auto;
            z-index: 90;
        }

        .sidebar h3 {
            padding: 0 20px 15px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            font-weight: 600;
        }

        .genero-btn {
            display: block;
            width: 100%;
            text-align: left;
            padding: 12px 20px;
            background: none;
            border: none;
            color: #ccc;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .genero-btn:hover {
            background: #1f1f1f;
            color: #fff;
        }

        .genero-btn.active {
            background: #1f1f1f;
            color: #e50914;
            border-left-color: #e50914;
            font-weight: 600;
        }

        /* CONTENIDO PRINCIPAL */
        .main {
            margin-left: 240px;
            margin-top: 70px;
            padding: 30px 40px;
            flex: 1;
            width: calc(100% - 240px);
        }

        .main h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .subtitulo {
            color: #888;
            margin-bottom: 30px;
            font-size: 1rem;
        }

        /* GRID DE PELÍCULAS */
        .peliculas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .pelicula-card {
            background: #1a1a1a;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.25s, box-shadow 0.25s;
            border: 1px solid #252525;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .pelicula-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px rgba(229, 9, 20, 0.15);
            border-color: #333;
        }

        .pelicula-img {
            width: 100%;
            height: 380px;
            object-fit: cover;
            display: block;
            background: #222;
        }

        .pelicula-info {
            padding: 18px;
        }

        .pelicula-titulo {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: #fff;
            line-height: 1.3;
        }

        .pelicula-genero {
            display: inline-block;
            background: #e50914;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .pelicula-desc {
            font-size: 0.9rem;
            color: #aaa;
            line-height: 1.45;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .pelicula-precio {
            font-size: 1.1rem;
            font-weight: 700;
            color: #e50914;
        }

        .sin-resultados {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 1.1rem;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .sidebar {
                width: 200px;
            }
            .main {
                margin-left: 200px;
                width: calc(100% - 200px);
                padding: 25px;
            }
        }

        @media (max-width: 700px) {
            .sidebar {
                position: fixed;
                left: -240px;
                transition: left 0.3s;
                width: 240px;
                z-index: 200;
            }
            .sidebar.open {
                left: 0;
            }
            .main {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
            .menu-toggle {
                display: block !important;
            }
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            margin-right: 15px;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <header>
        <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
        <a href="index.php" class="logo">
            CineNex <span>| Cartelera</span>
        </a>
        <div class="header-right">
            <a href="index.php">Inicio</a>
            <a href="#">Próximamente</a>
            <a href="#">Contacto</a>
        </div>
    </header>

    <!-- MENÚ LATERAL -->
    <aside class="sidebar" id="sidebar">
        <h3>Géneros</h3>
        <button class="genero-btn active" data-genero="todos" onclick="filtrar('todos')">
            Todas las películas
        </button>
        <?php foreach ($generos as $genero): ?>
            <button class="genero-btn" data-genero="<?= htmlspecialchars($genero) ?>" onclick="filtrar('<?= htmlspecialchars($genero) ?>')">
                <?= htmlspecialchars($genero) ?>
            </button>
        <?php endforeach; ?>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main">
        <h1>Cartelera actual</h1>
        <p class="subtitulo">Selecciona una película para ver más detalles</p>

        <div class="peliculas-grid" id="peliculas-container">
            <?php if (empty($peliculas)): ?>
                <div class="sin-resultados">No hay películas disponibles en este momento.</div>
            <?php else: ?>
                <?php foreach ($peliculas as $peli): ?>
                    <a href="pelicula.php?id=<?= (int)$peli['id_pelicula'] ?>" 
                       class="pelicula-card" 
                       data-genero="<?= htmlspecialchars($peli['genero']) ?>">
                        
                        <img 
                            class="pelicula-img" 
                            src="<?= htmlspecialchars($peli['imagen_url'] ?? '') ?>" 
                            alt="<?= htmlspecialchars($peli['titulo']) ?>"
                            onerror="this.src='https://via.placeholder.com/500x750/1a1a1a/666?text=Sin+Imagen'"
                        >
                        <div class="pelicula-info">
                            <h2 class="pelicula-titulo"><?= htmlspecialchars($peli['titulo']) ?></h2>
                            <span class="pelicula-genero"><?= htmlspecialchars($peli['genero']) ?></span>
                            <p class="pelicula-desc"><?= htmlspecialchars($peli['descripcion'] ?? '') ?></p>
                            <div class="pelicula-precio">
                                Desde $<?= number_format($peli['precio'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function filtrar(genero) {
            document.querySelectorAll('.genero-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.genero === genero) {
                    btn.classList.add('active');
                }
            });

            const cards = document.querySelectorAll('.pelicula-card');
            let visibles = 0;

            cards.forEach(card => {
                if (genero === 'todos' || card.dataset.genero === genero) {
                    card.style.display = 'block';
                    visibles++;
                } else {
                    card.style.display = 'none';
                }
            });

            let msg = document.getElementById('sin-resultados');
            if (visibles === 0) {
                if (!msg) {
                    msg = document.createElement('div');
                    msg.id = 'sin-resultados';
                    msg.className = 'sin-resultados';
                    msg.textContent = 'No hay películas de este género en cartelera.';
                    document.getElementById('peliculas-container').appendChild(msg);
                }
            } else if (msg) {
                msg.remove();
            }
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.menu-toggle');
            if (window.innerWidth <= 700 && 
                sidebar.classList.contains('open') && 
                !sidebar.contains(e.target) && 
                !toggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    </script>
</body>
</html>