<?php
$peliculas = [
    1 => ['titulo' => 'Dune: Parte Dos', 'precio' => 120],
    2 => ['titulo' => 'Oppenheimer', 'precio' => 110],
    3 => ['titulo' => 'Deadpool & Wolverine', 'precio' => 130],
    4 => ['titulo' => 'Inside Out 2', 'precio' => 100],
    5 => ['titulo' => 'Alien: Romulus', 'precio' => 115],
    6 => ['titulo' => 'Twisters', 'precio' => 110],
    7 => ['titulo' => 'Despicable Me 4', 'precio' => 95],
    8 => ['titulo' => 'Longlegs', 'precio' => 105],
];

$snacks = [
    ['id' => 'pochoclo_chico', 'nombre' => 'Pochoclo Chico', 'precio' => 25],
    ['id' => 'pochoclo_grande', 'nombre' => 'Pochoclo Grande', 'precio' => 40],
    ['id' => 'gaseosa', 'nombre' => 'Gaseosa', 'precio' => 30],
    ['id' => 'agua', 'nombre' => 'Agua', 'precio' => 20],
    ['id' => 'nachos', 'nombre' => 'Nachos con queso', 'precio' => 35],
    ['id' => 'combo', 'nombre' => 'Combo (Pochoclo + Gaseosa)', 'precio' => 55],
];

$ocupados = ['A3', 'A4', 'B5', 'B6', 'C2', 'C7', 'D4', 'E1', 'E8', 'F3', 'F4', 'G6'];
$filas = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
$columnas = range(1, 10);

$cuenta_transferencia = [
    'banco'     => 'BBVA',
    'titular'   => 'CineNex S.A. de C.V.',
    'clabe'     => '012345678901234567',
    'cuenta'    => '1234567890',
    'concepto'  => 'Boletos CineNex'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineNex - Compra de Boletos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #0f0f0f;
            color: #e5e5e5;
            min-height: 100vh;
        }
        header {
            position: sticky; top: 0; height: 70px; background: #141414;
            display: flex; align-items: center; padding: 0 30px;
            border-bottom: 1px solid #222; z-index: 100;
        }
        .logo { font-size: 1.7rem; font-weight: 800; color: #e50914; }
        .logo span { color: #fff; font-weight: 400; font-size: 1rem; }
        .volver { margin-left: auto; color: #aaa; text-decoration: none; font-size: 0.95rem; }
        .volver:hover { color: #fff; }
        .container { max-width: 1000px; margin: 0 auto; padding: 40px 20px; }
        h1 { font-size: 1.9rem; margin-bottom: 8px; }
        .subtitulo { color: #888; margin-bottom: 35px; }

        .seccion {
            background: #1a1a1a; border: 1px solid #2a2a2a;
            border-radius: 14px; padding: 28px; margin-bottom: 30px;
        }
        .seccion h2 {
            font-size: 1.25rem; margin-bottom: 20px; color: #fff;
            display: flex; align-items: center; gap: 10px;
        }
        .seccion h2 span {
            background: #e50914; color: #fff; width: 28px; height: 28px;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-size: 0.9rem; font-weight: 700;
        }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }
        label { font-size: 0.9rem; color: #aaa; }
        input, select {
            background: #111; border: 1px solid #333; border-radius: 8px;
            padding: 12px 14px; color: #fff; font-size: 1rem; outline: none;
        }
        input:focus, select:focus { border-color: #e50914; }

        .pantalla {
            background: linear-gradient(90deg, #333, #555, #333); color: #ccc;
            text-align: center; padding: 12px; border-radius: 8px 8px 0 0;
            margin-bottom: 35px; font-size: 0.9rem; letter-spacing: 4px;
            text-transform: uppercase; box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        }
        .mapa-asientos {
            display: flex; flex-direction: column; align-items: center;
            gap: 12px; margin-bottom: 25px;
        }
        .fila { display: flex; align-items: center; gap: 8px; }
        .letra-fila { width: 26px; text-align: center; font-weight: 600; color: #888; font-size: 0.9rem; }

        .asiento {
            width: 38px; height: 38px; border: none; cursor: pointer;
            font-size: 0.7rem; font-weight: 700; transition: all 0.18s;
            display: flex; align-items: flex-end; justify-content: center;
            padding-bottom: 4px; position: relative;
            border-radius: 8px 8px 4px 4px; background: #555; color: #ddd;
            box-shadow: inset 0 -4px 0 rgba(0,0,0,0.25);
        }
        .asiento::before {
            content: ''; position: absolute; top: -6px; left: 3px; right: 3px;
            height: 10px; background: inherit; border-radius: 6px 6px 0 0;
        }
        .asiento.libre { background: #4b5563; color: #e5e7eb; }
        .asiento.libre:hover { background: #6b7280; transform: translateY(-3px); }
        .asiento.ocupado { background: #1e3a8a; color: #93c5fd; cursor: not-allowed; opacity: 0.85; }
        .asiento.seleccionado {
            background: #e50914; color: #fff;
            box-shadow: 0 0 0 2px #fff, inset 0 -4px 0 rgba(0,0,0,0.3);
            transform: translateY(-2px);
        }

        .leyenda {
            display: flex; justify-content: center; gap: 25px;
            margin-top: 15px; flex-wrap: wrap;
        }
        .leyenda-item { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: #aaa; }
        .cuadro { width: 22px; height: 22px; border-radius: 6px 6px 3px 3px; position: relative; }
        .cuadro::before {
            content: ''; position: absolute; top: -4px; left: 2px; right: 2px;
            height: 7px; background: inherit; border-radius: 4px 4px 0 0;
        }
        .cuadro.libre { background: #4b5563; }
        .cuadro.ocupado { background: #1e3a8a; }
        .cuadro.seleccionado { background: #e50914; }

        /* SNACKS CON CANTIDAD */
        .snacks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 14px;
        }
        .snack-card {
            background: #111; border: 2px solid #333; border-radius: 12px;
            padding: 16px; transition: all 0.2s;
        }
        .snack-card.activo { border-color: #e50914; background: #1f0a0c; }
        .snack-nombre { font-weight: 600; color: #fff; margin-bottom: 4px; }
        .snack-precio { color: #e50914; font-weight: 700; margin-bottom: 12px; }
        .cantidad-box {
            display: flex; align-items: center; gap: 10px;
        }
        .cantidad-box button {
            width: 32px; height: 32px; border: none; border-radius: 8px;
            background: #333; color: #fff; font-size: 1.2rem; cursor: pointer;
            font-weight: 700;
        }
        .cantidad-box button:hover { background: #555; }
        .cantidad-box span {
            min-width: 28px; text-align: center; font-weight: 700; font-size: 1.1rem;
        }
        .opcional { font-size: 0.85rem; color: #888; margin-bottom: 15px; }

        .resumen {
            display: flex; flex-wrap: wrap; justify-content: space-between;
            align-items: flex-start; gap: 20px;
        }
        .detalle-compra { flex: 1; min-width: 250px; }
        .detalle-compra p { margin: 6px 0; color: #ccc; font-size: 0.95rem; }
        .detalle-compra strong { color: #fff; }
        .precio-total { font-size: 1.7rem; font-weight: 700; color: #fff; text-align: right; }
        .precio-total span { color: #e50914; }

        .aviso-reserva {
            background: #3b2a0a; border: 1px solid #eab308;
            color: #fde68a; padding: 14px 16px; border-radius: 10px;
            margin-bottom: 20px; font-size: 0.95rem; line-height: 1.5;
        }

        .botones-accion {
            display: flex; gap: 15px; flex-wrap: wrap; justify-content: center;
            margin-top: 25px;
        }
        .btn-comprar, .btn-reservar {
            padding: 16px 28px; font-size: 1.1rem; font-weight: 700;
            border-radius: 10px; cursor: pointer; border: none;
            transition: all 0.2s; min-width: 200px;
        }
        .btn-comprar {
            background: #e50914; color: #fff;
        }
        .btn-comprar:hover { background: #f40612; transform: translateY(-2px); }
        .btn-comprar:disabled { background: #444; cursor: not-allowed; transform: none; }
        .btn-reservar {
            background: #1e3a5f; color: #fff; border: 1px solid #3b82f6;
        }
        .btn-reservar:hover { background: #2563eb; transform: translateY(-2px); }
        .btn-reservar:disabled { background: #333; border-color: #444; cursor: not-allowed; transform: none; }

        #seccion-pago { display: none; animation: fadeIn 0.4s ease; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .metodos-pago { display: flex; gap: 20px; margin-bottom: 25px; flex-wrap: wrap; }
        .metodo-opcion {
            flex: 1; min-width: 180px; background: #111; border: 2px solid #333;
            border-radius: 12px; padding: 18px; text-align: center;
            cursor: pointer; transition: all 0.2s;
        }
        .metodo-opcion:hover { border-color: #666; }
        .metodo-opcion.activo { border-color: #e50914; background: #1f0a0c; }
        .metodo-opcion input { display: none; }
        .metodo-opcion .icono { font-size: 2rem; margin-bottom: 8px; }
        .metodo-opcion .nombre { font-weight: 600; color: #fff; }

        #form-tarjeta { display: none; margin-top: 10px; }
        .tarjeta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        #form-transferencia { display: none; margin-top: 10px; text-align: center; }
        .qr-container {
            background: #fff; padding: 20px; border-radius: 12px;
            display: inline-block; margin: 20px 0;
        }
        .qr-container img { width: 220px; height: 220px; display: block; }
        .datos-cuenta {
            background: #111; border-radius: 10px; padding: 18px;
            text-align: left; max-width: 400px; margin: 0 auto 20px;
            font-size: 0.95rem; line-height: 1.7;
        }
        .datos-cuenta strong { color: #e50914; }

        .btn-confirmar-pago {
            display: block; width: 100%; max-width: 320px; margin: 25px auto 0;
            background: #16a34a; color: #fff; border: none; padding: 16px 30px;
            font-size: 1.1rem; font-weight: 700; border-radius: 10px; cursor: pointer;
        }
        .btn-confirmar-pago:hover { background: #15803d; }

        @media (max-width: 650px) {
            .form-grid, .tarjeta-grid { grid-template-columns: 1fr; }
            .asiento { width: 32px; height: 32px; font-size: 0.65rem; }
            .fila { gap: 5px; }
            .botones-accion { flex-direction: column; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">CineNex <span>| Compra de boletos</span></div>
    <a href="index.php" class="volver">← Volver a cartelera</a>
</header>

<div class="container">
    <h1>Compra tu boleto online</h1>
    <p class="subtitulo">Completá tus datos, elegí asientos y sumá comida o bebida si querés</p>

    <form id="form-compra">

        <!-- 1. DATOS -->
        <div class="seccion">
            <h2><span>1</span> Datos del comprador</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre">Nombre completo *</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan Pérez" oninput="capitalizarNombre(this)">
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico *</label>
                    <input type="email" id="email" name="email" required placeholder="Ej: juan@email.com">
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono *</label>
                    <input type="tel" id="telefono" name="telefono" required placeholder="Ej: 5512345678">
                </div>
                <div class="form-group">
                    <label for="pelicula">Película *</label>
                    <select id="pelicula" name="pelicula" required onchange="actualizarPrecio()">
                        <option value="">Selecciona una película</option>
                        <?php foreach ($peliculas as $id => $peli): ?>
                            <option value="<?= $id ?>" data-precio="<?= $peli['precio'] ?>">
                                <?= htmlspecialchars($peli['titulo']) ?> - $<?= $peli['precio'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label for="horario">Horario *</label>
                    <select id="horario" name="horario" required>
                        <option value="">Selecciona un horario</option>
                        <option value="14:30">14:30</option>
                        <option value="17:00">17:00</option>
                        <option value="19:30">19:30</option>
                        <option value="22:00">22:00</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. ASIENTOS -->
        <div class="seccion">
            <h2><span>2</span> Seleccioná tus asientos</h2>
            <div class="pantalla">PANTALLA</div>
            <div class="mapa-asientos" id="mapa">
                <?php foreach ($filas as $fila): ?>
                    <div class="fila">
                        <span class="letra-fila"><?= $fila ?></span>
                        <?php foreach ($columnas as $col):
                            $codigo = $fila . $col;
                            $clase = in_array($codigo, $ocupados) ? 'ocupado' : 'libre';
                        ?>
                            <button type="button"
                                    class="asiento <?= $clase ?>"
                                    data-asiento="<?= $codigo ?>"
                                    <?= $clase === 'ocupado' ? 'disabled' : '' ?>
                                    onclick="toggleAsiento(this)">
                                <?= $col ?>
                            </button>
                        <?php endforeach; ?>
                        <span class="letra-fila"><?= $fila ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="leyenda">
                <div class="leyenda-item"><div class="cuadro libre"></div> Libre</div>
                <div class="leyenda-item"><div class="cuadro ocupado"></div> Ocupado</div>
                <div class="leyenda-item"><div class="cuadro seleccionado"></div> Seleccionado</div>
            </div>
        </div>

        <!-- 3. COMIDAS CON CANTIDAD -->
        <div class="seccion">
            <h2><span>3</span> Comidas y bebidas</h2>
            <p class="opcional">Opcional — elegí la cantidad de cada producto</p>

            <div class="snacks-grid">
                <?php foreach ($snacks as $snack): ?>
                    <div class="snack-card" id="card-<?= $snack['id'] ?>">
                        <div class="snack-nombre"><?= htmlspecialchars($snack['nombre']) ?></div>
                        <div class="snack-precio">$<?= $snack['precio'] ?> c/u</div>
                        <div class="cantidad-box">
                            <button type="button" onclick="cambiarCantidad('<?= $snack['id'] ?>', -1, <?= $snack['precio'] ?>, '<?= htmlspecialchars($snack['nombre']) ?>')">−</button>
                            <span id="cant-<?= $snack['id'] ?>">0</span>
                            <button type="button" onclick="cambiarCantidad('<?= $snack['id'] ?>', 1, <?= $snack['precio'] ?>, '<?= htmlspecialchars($snack['nombre']) ?>')">+</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 4. RESUMEN -->
        <div class="seccion">
            <h2><span>4</span> Resumen</h2>

            <div class="aviso-reserva">
                ⚠️ <strong>Importante:</strong> Si solo reservás, debés presentarte en el cine
                <strong>al menos 20 minutos antes</strong> del inicio de la función para confirmar
                y pagar tu entrada. Si no llegás a tiempo, el asiento puede liberarse.
            </div>

            <div class="resumen">
                <div class="detalle-compra">
                    <p>Asientos: <strong id="lista-asientos">Ninguno</strong></p>
                    <p>Comidas/Bebidas: <strong id="lista-snacks">Ninguna</strong></p>
                </div>
                <div class="precio-total">
                    Total: <span id="precio-total">$0</span>
                </div>
            </div>

            <div class="botones-accion">
                <button type="button" class="btn-reservar" id="btn-reservar" disabled onclick="soloReservar()">
                    Solo reservar
                </button>
                <button type="button" class="btn-comprar" id="btn-comprar" disabled onclick="mostrarPago()">
                    Pagar ahora
                </button>
            </div>
        </div>

        <!-- 5. PAGO -->
        <div class="seccion" id="seccion-pago">
            <h2><span>5</span> Método de pago</h2>

            <div class="metodos-pago">
                <label class="metodo-opcion" id="opcion-tarjeta" onclick="seleccionarMetodo('tarjeta')">
                    <input type="radio" name="metodo" value="tarjeta">
                    <div class="icono">💳</div>
                    <div class="nombre">Tarjeta de crédito / débito</div>
                </label>
                <label class="metodo-opcion" id="opcion-transferencia" onclick="seleccionarMetodo('transferencia')">
                    <input type="radio" name="metodo" value="transferencia">
                    <div class="icono">📱</div>
                    <div class="nombre">Transferencia bancaria</div>
                </label>
            </div>

            <div id="form-tarjeta">
                <div class="tarjeta-grid">
                    <div class="form-group full">
                        <label for="num-tarjeta">Número de tarjeta *</label>
                        <input type="text" id="num-tarjeta" maxlength="19" placeholder="ACCT-000035" oninput="formatearTarjeta(this)">
                    </div>
                    <div class="form-group full">
                        <label for="nombre-tarjeta">Nombre del titular *</label>
                        <input type="text" id="nombre-tarjeta" placeholder="Como aparece en la tarjeta" oninput="capitalizarNombre(this)">
                    </div>
                    <div class="form-group">
                        <label for="vencimiento">Vencimiento *</label>
                        <input type="text" id="vencimiento" maxlength="5" placeholder="MM/AA" oninput="formatearVencimiento(this)">
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV *</label>
                        <input type="text" id="cvv" maxlength="4" placeholder="123">
                    </div>
                </div>
            </div>

            <div id="form-transferencia">
                <p style="color:#aaa; margin-bottom:15px;">Escaneá el código QR o transferí con estos datos:</p>
                <div class="qr-container">
                    <img id="qr-code"
                         src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= urlencode('Banco: '.$cuenta_transferencia['banco'].'\nTitular: '.$cuenta_transferencia['titular'].'\nCLABE: '.$cuenta_transferencia['clabe'].'\nCuenta: '.$cuenta_transferencia['cuenta'].'\nConcepto: '.$cuenta_transferencia['concepto']) ?>"
                         alt="Código QR">
                </div>
                <div class="datos-cuenta">
                    <div><strong>Banco:</strong> <?= $cuenta_transferencia['banco'] ?></div>
                    <div><strong>Titular:</strong> <?= $cuenta_transferencia['titular'] ?></div>
                    <div><strong>CLABE:</strong> <?= $cuenta_transferencia['clabe'] ?></div>
                    <div><strong>Cuenta:</strong> <?= $cuenta_transferencia['cuenta'] ?></div>
                    <div><strong>Concepto:</strong> <?= $cuenta_transferencia['concepto'] ?></div>
                    <div id="monto-transferencia"><strong>Monto a transferir:</strong> $0</div>
                </div>
            </div>

            <button type="button" class="btn-confirmar-pago" onclick="confirmarPago()">
                Confirmar pago
            </button>
        </div>
    </form>
</div>

<script>
    let asientosSeleccionados = [];
    let snacksCantidad = {}; // { id: { nombre, precio, cantidad } }
    let precioUnitario = 0;
    let metodoSeleccionado = null;

    function capitalizarNombre(input) {
        const start = input.selectionStart;
        const end = input.selectionEnd;
        input.value = input.value.replace(/\b\w/g, l => l.toUpperCase());
        input.setSelectionRange(start, end);
    }

    function toggleAsiento(btn) {
        const codigo = btn.dataset.asiento;
        if (btn.classList.contains('seleccionado')) {
            btn.classList.remove('seleccionado');
            btn.classList.add('libre');
            asientosSeleccionados = asientosSeleccionados.filter(a => a !== codigo);
        } else {
            btn.classList.remove('libre');
            btn.classList.add('seleccionado');
            asientosSeleccionados.push(codigo);
        }
        actualizarResumen();
    }

    function cambiarCantidad(id, delta, precio, nombre) {
        if (!snacksCantidad[id]) {
            snacksCantidad[id] = { nombre, precio, cantidad: 0 };
        }
        snacksCantidad[id].cantidad = Math.max(0, snacksCantidad[id].cantidad + delta);
        document.getElementById('cant-' + id).textContent = snacksCantidad[id].cantidad;

        const card = document.getElementById('card-' + id);
        if (snacksCantidad[id].cantidad > 0) {
            card.classList.add('activo');
        } else {
            card.classList.remove('activo');
            delete snacksCantidad[id];
        }
        actualizarResumen();
    }

    function actualizarPrecio() {
        const select = document.getElementById('pelicula');
        const option = select.options[select.selectedIndex];
        precioUnitario = option.dataset.precio ? parseInt(option.dataset.precio) : 0;
        actualizarResumen();
    }

    function actualizarResumen() {
        const listaAsientos = document.getElementById('lista-asientos');
        const listaSnacks = document.getElementById('lista-snacks');
        const totalEl = document.getElementById('precio-total');
        const btnComprar = document.getElementById('btn-comprar');
        const btnReservar = document.getElementById('btn-reservar');
        const montoTrans = document.getElementById('monto-transferencia');

        listaAsientos.textContent = asientosSeleccionados.length === 0
            ? 'Ninguno'
            : asientosSeleccionados.sort().join(', ');

        const snacksActivos = Object.values(snacksCantidad).filter(s => s.cantidad > 0);
        if (snacksActivos.length === 0) {
            listaSnacks.textContent = 'Ninguna';
        } else {
            listaSnacks.textContent = snacksActivos
                .map(s => s.nombre + ' x' + s.cantidad)
                .join(', ');
        }

        const totalBoletos = asientosSeleccionados.length * precioUnitario;
        const totalSnacks = snacksActivos.reduce((sum, s) => sum + (s.precio * s.cantidad), 0);
        const total = totalBoletos + totalSnacks;

        totalEl.textContent = '$' + total;
        if (montoTrans) montoTrans.innerHTML = '<strong>Monto a transferir:</strong> $' + total;

        const listo = asientosSeleccionados.length > 0 && precioUnitario > 0;
        btnComprar.disabled = !listo;
        btnReservar.disabled = !listo;
        actualizarQR(total);
    }

    function actualizarQR(monto) {
        const datos = `Banco: <?= $cuenta_transferencia['banco'] ?>\nTitular: <?= $cuenta_transferencia['titular'] ?>\nCLABE: <?= $cuenta_transferencia['clabe'] ?>\nCuenta: <?= $cuenta_transferencia['cuenta'] ?>\nConcepto: <?= $cuenta_transferencia['concepto'] ?>\nMonto: $${monto}`;
        const qr = document.getElementById('qr-code');
        if (qr) qr.src = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(datos)}`;
    }

    function validarDatos() {
        const nombre = document.getElementById('nombre').value.trim();
        const email = document.getElementById('email').value.trim();
        const telefono = document.getElementById('telefono').value.trim();
        const pelicula = document.getElementById('pelicula').value;
        const horario = document.getElementById('horario').value;

        if (!nombre || !email || !telefono || !pelicula || !horario) {
            alert('Por favor completá todos los campos obligatorios.');
            return false;
        }
        if (asientosSeleccionados.length === 0) {
            alert('Debés seleccionar al menos un asiento.');
            return false;
        }
        return true;
    }

    function mostrarPago() {
        if (!validarDatos()) return;
        document.getElementById('seccion-pago').style.display = 'block';
        document.getElementById('btn-comprar').disabled = true;
        document.getElementById('btn-reservar').disabled = true;
        document.getElementById('seccion-pago').scrollIntoView({ behavior: 'smooth' });
    }

    function soloReservar() {
        if (!validarDatos()) return;

        const ok = confirm(
            '⚠️ RECORDATORIO IMPORTANTE\n\n' +
            'Si reservás sin pagar, debés estar en el cine al menos 20 minutos antes del inicio de la función para confirmar tu asiento y realizar el pago.\n\n' +
            'Si no llegás a tiempo, el asiento puede liberarse para otro cliente.\n\n' +
            '¿Deseás continuar con la reserva?'
        );
        if (!ok) return;

        enviarTicket('reserva');
    }

    function seleccionarMetodo(metodo) {
        metodoSeleccionado = metodo;
        document.getElementById('opcion-tarjeta').classList.toggle('activo', metodo === 'tarjeta');
        document.getElementById('opcion-transferencia').classList.toggle('activo', metodo === 'transferencia');
        document.getElementById('form-tarjeta').style.display = metodo === 'tarjeta' ? 'block' : 'none';
        document.getElementById('form-transferencia').style.display = metodo === 'transferencia' ? 'block' : 'none';
    }

    function formatearTarjeta(input) {
        let valor = input.value.replace(/\D/g, '').substring(0, 16);
        input.value = valor.replace(/(\d{4})(?=\d)/g, '$1 ');
    }

    function formatearVencimiento(input) {
        let valor = input.value.replace(/\D/g, '').substring(0, 4);
        if (valor.length >= 3) valor = valor.substring(0, 2) + '/' + valor.substring(2);
        input.value = valor;
    }

    function confirmarPago() {
        if (!metodoSeleccionado) {
            alert('Seleccioná un método de pago.');
            return;
        }
        if (metodoSeleccionado === 'tarjeta') {
            const num = document.getElementById('num-tarjeta').value.replace(/\s/g, '');
            const nombreTarjeta = document.getElementById('nombre-tarjeta').value.trim();
            const vencimiento = document.getElementById('vencimiento').value;
            const cvv = document.getElementById('cvv').value;
            if (num.length < 15 || !nombreTarjeta || vencimiento.length < 5 || cvv.length < 3) {
                alert('Completá correctamente los datos de la tarjeta.');
                return;
            }
        }
        enviarTicket('pago');
    }

    function enviarTicket(tipo) {
        const nombre = document.getElementById('nombre').value.trim();
        const email = document.getElementById('email').value.trim();
        const peliculaTexto = document.getElementById('pelicula').selectedOptions[0].text;
        const horario = document.getElementById('horario').value;

        const snacksActivos = Object.values(snacksCantidad).filter(s => s.cantidad > 0);
        const totalBoletos = asientosSeleccionados.length * precioUnitario;
        const totalSnacks = snacksActivos.reduce((sum, s) => sum + (s.precio * s.cantidad), 0);
        const total = totalBoletos + totalSnacks;

        const snacksTexto = snacksActivos.length > 0
            ? snacksActivos.map(s => s.nombre + ' x' + s.cantidad).join(', ')
            : 'Ninguna';

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'guardar_ticket.php';

        const campos = {
            nombre: nombre,
            email: email,
            pelicula: peliculaTexto,
            horario: horario,
            asientos: asientosSeleccionados.sort().join(', '),
            snacks: snacksTexto,
            total: total,
            imagen: '',
            tipo: tipo // 'pago' o 'reserva'
        };

        for (const key in campos) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = campos[key];
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }
</script>
</body>
</html>