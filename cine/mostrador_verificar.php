<?php
require 'mostrador_auth.php';
require 'conexion.php';

$ticket = null;
$error = "";
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $codigo = strtoupper(trim($_POST['codigo'] ?? ''));

    if (empty($codigo)) {
        $error = "Ingresá el código del ticket.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM tickets WHERE codigo = ?");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            $error = "Ticket no encontrado. Verificá el código.";
        } else {
            $ticket = $resultado->fetch_assoc();

            // Si el personal marca el ticket como usado
            if (isset($_POST['marcar_usado']) && $ticket['estado'] === 'valido') {
                $upd = $conn->prepare("UPDATE tickets SET estado = 'usado', fecha_uso = NOW() WHERE codigo = ?");
                $upd->bind_param("s", $codigo);
                $upd->execute();
                $ticket['estado'] = 'usado';
                $mensaje = "Ticket marcado como usado correctamente.";
            }
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
    <title>Verificar Ticket - Mostrador</title>
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
        .volver { color: #aaa; text-decoration: none; }
        .volver:hover { color: #fff; }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        h1 { margin-bottom: 8px; }
        .subtitulo { color: #888; margin-bottom: 30px; }

        .buscador {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 6px; color: #aaa; font-size: 0.9rem; }
        input {
            width: 100%;
            padding: 14px;
            background: #111;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            font-size: 1.2rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            outline: none;
            text-align: center;
        }
        input:focus { border-color: #e50914; }
        .btn {
            width: 100%;
            padding: 14px;
            background: #e50914;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn:hover { background: #f40612; }

        .error {
            background: #3b0a0a;
            color: #fca5a5;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .mensaje {
            background: #0f3d1a;
            color: #86efac;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .resultado {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 14px;
            padding: 25px;
        }
        .resultado h2 { margin-bottom: 20px; font-size: 1.3rem; }
        .resultado p {
            margin: 10px 0;
            color: #ccc;
            display: flex;
            justify-content: space-between;
        }
        .resultado strong { color: #fff; }

        .estado {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        .estado.valido { background: #0f3d1a; color: #86efac; }
        .estado.usado { background: #3b0a0a; color: #fca5a5; }
        .estado.cancelado { background: #333; color: #999; }

        .btn-usar {
            width: 100%;
            margin-top: 20px;
            padding: 14px;
            background: #16a34a;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-usar:hover { background: #15803d; }
        .btn-usar:disabled {
            background: #444;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<header>
    <a href="mostrador_panel.php" class="logo">CineNex <span>| Mostrador</span></a>
    <a href="mostrador_panel.php" class="volver">← Volver</a>
</header>

<div class="container">
    <h1>Verificar ticket online</h1>
    <p class="subtitulo">Ingresá el código del ticket o escaneá el QR</p>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($mensaje): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="buscador">
        <form method="POST">
            <div class="form-group">
                <label>Código del ticket</label>
                <input type="text" name="codigo" placeholder="Ej: A3F9B2C1" required
                       value="<?= htmlspecialchars($_POST['codigo'] ?? '') ?>" autofocus>
            </div>
            <button type="submit" class="btn">Buscar ticket</button>
        </form>
    </div>

    <?php if ($ticket): ?>
        <div class="resultado">
            <span class="estado <?= $ticket['estado'] ?>">
                <?= strtoupper($ticket['estado']) ?>
            </span>

            <h2>Detalle del ticket</h2>
            <p><strong>Código</strong> <span><?= htmlspecialchars($ticket['codigo']) ?></span></p>
            <p><strong>Cliente</strong> <span><?= htmlspecialchars($ticket['nombre_cliente']) ?></span></p>
            <p><strong>Película</strong> <span><?= htmlspecialchars($ticket['pelicula']) ?></span></p>
            <p><strong>Horario</strong> <span><?= htmlspecialchars($ticket['horario']) ?></span></p>
            <p><strong>Asientos</strong> <span><?= htmlspecialchars($ticket['asientos']) ?></span></p>
            <p><strong>Comidas</strong> <span><?= htmlspecialchars($ticket['snacks']) ?></span></p>
            <p><strong>Total</strong> <span>$<?= number_format($ticket['total'], 2) ?></span></p>
            <p><strong>Origen</strong> <span><?= $ticket['origen'] === 'online' ? 'Compra online' : 'Mostrador' ?></span></p>

            <?php if ($ticket['estado'] === 'valido'): ?>
                <form method="POST">
                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($ticket['codigo']) ?>">
                    <button type="submit" name="marcar_usado" class="btn-usar">
                        ✅ Marcar como usado (cliente ingresó)
                    </button>
                </form>
            <?php else: ?>
                <button class="btn-usar" disabled>Ticket ya utilizado</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>