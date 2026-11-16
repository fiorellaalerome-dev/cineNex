<?php
session_start();

$nombre     = $_GET['nombre']     ?? $_SESSION['ticket']['nombre']     ?? 'Cliente';
$email      = $_GET['email']      ?? $_SESSION['ticket']['email']      ?? '';
$pelicula   = $_GET['pelicula']   ?? $_SESSION['ticket']['pelicula']   ?? '';
$horario    = $_GET['horario']    ?? $_SESSION['ticket']['horario']    ?? '';
$asientos   = $_GET['asientos']   ?? $_SESSION['ticket']['asientos']   ?? '';
$snacks     = $_GET['snacks']     ?? $_SESSION['ticket']['snacks']     ?? 'Ninguna';
$total      = $_GET['total']      ?? $_SESSION['ticket']['total']      ?? '0';
$imagen     = $_GET['imagen']     ?? $_SESSION['ticket']['imagen']     ?? '';
$codigo     = $_GET['codigo']     ?? $_SESSION['ticket']['codigo']     ?? strtoupper(substr(md5(uniqid()), 0, 8));

// Si no viene imagen, usamos un placeholder
if (empty($imagen)) {
    $imagen = 'https://via.placeholder.com/300x450/1a1a1a/666?text=Pelicula';
}

// Datos que van dentro del QR (para que el cine pueda verificar)
$datos_qr = "CineNex Ticket\nCodigo: $codigo\nCliente: $nombre\nPelicula: $pelicula\nHorario: $horario\nAsientos: $asientos\nTotal: $$total";

$mensaje_envio = "";
if (isset($_POST['enviar_correo']) && !empty($email)) {
    $asunto = "Tu ticket de CineNex - $codigo";
    
    $cuerpo = "
Hola $nombre,

¡Gracias por tu compra en CineNex!

========== TU TICKET ==========
Código: $codigo
Película: $pelicula
Horario: $horario
Asientos: $asientos
Comidas/Bebidas: $snacks
Total pagado: $$total
==============================

Presentá este correo o el ticket descargado en la entrada del cine.

¡Que disfrutes la función!
CineNex
    ";

    $headers  = "From: entradas@cinenex.com\r\n";
    $headers .= "Reply-To: entradas@cinenex.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    if (mail($email, $asunto, $cuerpo, $headers)) {
        $mensaje_envio = "✅ Ticket enviado correctamente a $email";
    } else {
        $mensaje_envio = "⚠️ No se pudo enviar el correo. Podés descargar el ticket.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Ticket - CineNex</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0f0f0f;
            color: #e5e5e5;
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 420px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            margin-bottom: 8px;
            font-size: 1.7rem;
        }
        .subtitulo {
            text-align: center;
            color: #888;
            margin-bottom: 28px;
            font-size: 0.95rem;
        }

        /* TICKET ALARGADO */
        .ticket {
            background: #1a1a1a;
            border: 2px dashed #e50914;
            border-radius: 18px;
            padding: 0;
            overflow: hidden;
            position: relative;
        }
        .ticket-top {
            background: #e50914;
            padding: 14px;
            text-align: center;
        }
        .ticket-logo {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: 1px;
        }
        .ticket-codigo {
            margin-top: 4px;
            font-size: 0.95rem;
            color: #ffe0e0;
            letter-spacing: 2px;
        }

        /* IMAGEN DE LA PELÍCULA */
        .ticket-imagen {
            width: 100%;
            height: 280px;
            object-fit: cover;
            display: block;
            background: #111;
        }

        .ticket-body {
            padding: 22px 24px 10px;
        }
        .ticket-body p {
            margin: 11px 0;
            font-size: 0.98rem;
            color: #ccc;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .ticket-body strong {
            color: #fff;
            min-width: 110px;
        }
        .ticket-body span {
            text-align: right;
            color: #ddd;
        }

        .ticket-total {
            margin: 18px 24px 0;
            padding: 14px 0;
            border-top: 1px dashed #333;
            border-bottom: 1px dashed #333;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: #e50914;
        }

        /* QR */
        .ticket-qr {
            text-align: center;
            padding: 20px 20px 26px;
        }
        .ticket-qr p {
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 12px;
        }
        .ticket-qr img {
            width: 160px;
            height: 160px;
            background: #fff;
            padding: 8px;
            border-radius: 10px;
        }

        /* BOTONES */
        .acciones {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 28px;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .btn-descargar {
            background: #e50914;
            color: #fff;
        }
        .btn-descargar:hover { background: #f40612; }
        .btn-correo {
            background: #16a34a;
            color: #fff;
        }
        .btn-correo:hover { background: #15803d; }
        .btn-volver {
            background: #333;
            color: #ddd;
        }
        .btn-volver:hover { background: #444; }

        .mensaje {
            background: #0f3d1a;
            border: 1px solid #22c55e;
            color: #86efac;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        @media print {
            body { background: #fff; color: #000; padding: 0; }
            .acciones, .subtitulo { display: none !important; }
            .ticket {
                border: 2px solid #000;
                max-width: 100%;
            }
            .ticket-top { background: #000; }
            .ticket-total { color: #000; }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>¡Compra exitosa!</h1>
    <p class="subtitulo">Aquí está tu ticket de entrada</p>

    <?php if ($mensaje_envio): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje_envio) ?></div>
    <?php endif; ?>

    <!-- TICKET -->
    <div class="ticket" id="ticket">
        <div class="ticket-top">
            <div class="ticket-logo">CineNex</div>
            <div class="ticket-codigo">N° <?= htmlspecialchars($codigo) ?></div>
        </div>

        <!-- FOTO DE LA PELÍCULA -->
        <img 
            class="ticket-imagen" 
            src="<?= htmlspecialchars($imagen) ?>" 
            alt="<?= htmlspecialchars($pelicula) ?>"
            onerror="this.src='https://via.placeholder.com/300x450/1a1a1a/666?text=Pelicula'"
        >

        <div class="ticket-body">
            <p><strong>Cliente</strong> <span><?= htmlspecialchars($nombre) ?></span></p>
            <p><strong>Película</strong> <span><?= htmlspecialchars($pelicula) ?></span></p>
            <p><strong>Horario</strong> <span><?= htmlspecialchars($horario) ?></span></p>
            <p><strong>Asientos</strong> <span><?= htmlspecialchars($asientos) ?></span></p>
            <p><strong>Comidas</strong> <span><?= htmlspecialchars($snacks) ?></span></p>
        </div>

        <div class="ticket-total">
            Total: $<?= htmlspecialchars($total) ?>
        </div>

        <!-- QR PARA EL CINE -->
        <div class="ticket-qr">
            <p>Mostrá este código en la entrada</p>
            <img 
                src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=<?= urlencode($datos_qr) ?>" 
                alt="QR del ticket"
            >
        </div>
    </div>

    <!-- BOTONES -->
    <div class="acciones">
        <button class="btn btn-descargar" onclick="window.print()">
            📥 Descargar / Imprimir ticket
        </button>

        <form method="POST" style="margin:0;">
            <button type="submit" name="enviar_correo" class="btn btn-correo">
                📧 Enviar ticket a mi correo
            </button>
        </form>

        <a href="index.php" class="btn btn-volver">
            Volver a la cartelera
        </a>
    </div>
</div>

</body>
</html>