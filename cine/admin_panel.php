<?php
require 'admin_auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - CineNex</title>
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
            padding: 18px 30px;
            border-bottom: 1px solid #222;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.6rem;
            font-weight: 800;
            color: #e50914;
        }
        .logo span {
            color: #fff;
            font-weight: 400;
            font-size: 1rem;
        }
        .logout {
            color: #aaa;
            text-decoration: none;
            font-size: 0.95rem;
        }
        .logout:hover { color: #e50914; }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 60px 20px;
            text-align: center;
        }
        h1 {
            font-size: 2.2rem;
            margin-bottom: 12px;
            font-weight: 700;
        }
        .subtitulo {
            color: #888;
            margin-bottom: 50px;
            font-size: 1.1rem;
        }
        .botones {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            max-width: 900px;
            margin: 0 auto;
        }
        .btn-admin {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 14px;
            padding: 35px 20px;
            text-decoration: none;
            color: #fff;
            transition: all 0.25s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }
        .btn-admin:hover {
            transform: translateY(-5px);
            border-color: #e50914;
            box-shadow: 0 10px 25px rgba(229, 9, 20, 0.15);
        }
        .btn-admin .icono {
            font-size: 2.5rem;
        }
        .btn-admin .texto {
            font-size: 1.15rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">CineNex <span>| Administrador</span></div>
    <a href="admin_logout.php" class="logout">Cerrar sesión</a>
</header>

<div class="container">
    <h1>Panel de Administración</h1>
    <p class="subtitulo">Seleccioná una opción para gestionar el cine</p>

    <div class="botones">
        <a href="cargar_peliculas.php" class="btn-admin">
            <div class="icono">🎬</div>
            <div class="texto">Cargar películas</div>
        </a>

        <a href="cargar_salas.php" class="btn-admin">
            <div class="icono">🏛️</div>
            <div class="texto">Cargar salas</div>
        </a>

        <a href="admin_ver_entradas.php" class="btn-admin">
            <div class="icono">🎟️</div>
            <div class="texto">Ver entradas</div>
        </a>

        <a href="admin_stock_snacks.php" class="btn-admin">
            <div class="icono">🍿</div>
            <div class="texto">Stock comidas y bebidas</div>
        </a>

        <a href="admin_promos.php" class="btn-admin">
            <div class="icono">🏷️</div>
            <div class="texto">Crear promos</div>
        </a>
    </div>
</div>

</body>
</html>