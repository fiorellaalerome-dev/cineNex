<?php
require 'mostrador_auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Mostrador - CineNex</title>
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
        }
        .logo span { color: #fff; font-weight: 400; font-size: 0.95rem; }
        .logout {
            color: #aaa;
            text-decoration: none;
        }
        .logout:hover { color: #e50914; }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 60px 20px;
            text-align: center;
        }
        h1 { font-size: 2rem; margin-bottom: 10px; }
        .subtitulo { color: #888; margin-bottom: 45px; }

        .botones {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
        }
        .btn-opcion {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 14px;
            padding: 40px 20px;
            text-decoration: none;
            color: #fff;
            transition: all 0.25s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }
        .btn-opcion:hover {
            transform: translateY(-5px);
            border-color: #e50914;
            box-shadow: 0 10px 25px rgba(229, 9, 20, 0.15);
        }
        .btn-opcion .icono { font-size: 2.8rem; }
        .btn-opcion .texto { font-size: 1.2rem; font-weight: 600; }
        .btn-opcion .desc { font-size: 0.9rem; color: #888; }
    </style>
</head>
<body>

<header>
    <div class="logo">CineNex <span>| Mostrador</span></div>
    <a href="mostrador_logout.php" class="logout">Cerrar sesión</a>
</header>

<div class="container">
    <h1>Panel de Mostrador</h1>
    <p class="subtitulo">Seleccioná una opción</p>

    <div class="botones">
        <a href="mostrador_vender.php" class="btn-opcion">
            <div class="icono">🎟️</div>
            <div class="texto">Vender entradas</div>
            <div class="desc">Venta presencial en boletería</div>
        </a>

        <a href="mostrador_verificar.php" class="btn-opcion">
            <div class="icono">🔍</div>
            <div class="texto">Verificar ticket</div>
            <div class="desc">Validar entradas compradas online</div>
        </a>
    </div>
</div>

</body>
</html>