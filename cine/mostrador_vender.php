<?php
require 'mostrador_auth.php';
require 'conexion.php';

$error = "";

// Películas activas
$peliculas_rs = $conn->query("
    SELECT p.id_pelicula, p.titulo
    FROM peliculas p
    WHERE p.activa = 1
    ORDER BY p.titulo
");
$peliculas = [];
while ($p = $peliculas_rs->fetch_assoc()) {
    $peliculas[] = $p;
}

// Funciones (horarios) futuras o de hoy
$funciones_rs = $conn->query("
    SELECT f.id_funcion, f.id_pelicula, f.id_sala, f.fecha_hora, f.precio,
           s.nombre AS sala
    FROM funciones f
    JOIN salas s ON f.id_sala = s.id_sala
    WHERE f.fecha_hora >= NOW()
    ORDER BY f.fecha_hora
");
$funciones = [];
while ($f = $funciones_rs->fetch_assoc()) {
    $funciones[] = $f;
}

// Asientos por sala
$asientos_rs = $conn->query("
    SELECT id_asiento, id_sala, codigo, fila, numero
    FROM asientos
    ORDER BY id_sala, fila, numero
");
$asientos_por_sala = [];
while ($a = $asientos_rs->fetch_assoc()) {
    $asientos_por_sala[$a['id_sala']][] = $a;
}

// Asientos ocupados por función
$ocupados_rs = $conn->query("
    SELECT r.id_funcion, a.codigo
    FROM detalle_reserva dr
    JOIN reservas r ON dr.id_reserva = r.id_reserva
    JOIN asientos a ON dr.id_asiento = a.id_asiento
    WHERE r.estado IN ('pendiente', 'confirmada')
");
$ocupados_por_funcion = [];
while ($o = $ocupados_rs->fetch_assoc()) {
    $ocupados_por_funcion[$o['id_funcion']][] = $o['codigo'];
}

// Snacks
$snacks = [
    ['id' => 'pochoclo_chico',  'nombre' => 'Pochoclo Chico',              'precio' => 25],
    ['id' => 'pochoclo_grande', 'nombre' => 'Pochoclo Grande',             'precio' => 40],
    ['id' => 'gaseosa',         'nombre' => 'Gaseosa',                     'precio' => 30],
    ['id' => 'agua',            'nombre' => 'Agua',                        'precio' => 20],
    ['id' => 'nachos',          'nombre' => 'Nachos con queso',            'precio' => 35],
    ['id' => 'combo',           'nombre' => 'Combo (Pochoclo + Gaseosa)',  'precio' => 55],
];

// Guardar venta
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre       = trim($_POST['nombre'] ?? '');
    $pelicula     = trim($_POST['pelicula'] ?? '');
    $horario      = trim($_POST['horario_texto'] ?? '');
    $asientos     = trim($_POST['asientos'] ?? '');
    $precio_boleto = floatval($_POST['precio_boleto'] ?? 0);
    $metodo_pago  = $_POST['metodo_pago'] ?? '';
    $snacks_sel   = $_POST['snacks'] ?? [];

    $total_snacks = 0;
    $nombres_snacks = [];
    foreach ($snacks as $s) {
        if (in_array($s['id'], $snacks_sel)) {
            $total_snacks += $s['precio'];
            $nombres_snacks[] = $s['nombre'];
        }
    }
    $snacks_texto = count($nombres_snacks) > 0 ? implode(', ', $nombres_snacks) : 'Ninguna';

    $cantidad = max(1, count(array_filter(array_map('trim', explode(',', $asientos)))));
    $total = ($precio_boleto * $cantidad) + $total_snacks;

    if (empty($nombre) || empty($pelicula) || empty($horario) || empty($asientos) || $total <= 0 || empty($metodo_pago)) {
        $error = "Completá todos los campos obligatorios y elegí al menos un asiento.";
    } else {
        $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

        $stmt = $conn->prepare("
            INSERT INTO tickets
            (codigo, nombre_cliente, pelicula, horario, asientos, snacks, total, origen, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'mostrador', 'valido')
        ");
        $stmt->bind_param("ssssssd", $codigo, $nombre, $pelicula, $horario, $asientos, $snacks_texto, $total);

        if ($stmt->execute()) {
            $params = http_build_query([
                'codigo'   => $codigo,
                'nombre'   => $nombre,
                'pelicula' => $pelicula,
                'horario'  => $horario,
                'asientos' => $asientos,
                'snacks'   => $snacks_texto,
                'total'    => $total,
                'metodo'   => $metodo_pago
            ]);
            header("Location: ticket.php?" . $params);
            exit;
        } else {
            $error = "Error al generar el ticket.";
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
    <title>Vender Entradas - Mostrador</title>
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
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        h1 { margin-bottom: 8px; }
        .subtitulo { color: #888; margin-bottom: 30px; }

        .form-box {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 14px;
            padding: 28px;
            margin-bottom: 25px;
        }
        .form-box h2 {
            font-size: 1.15rem;
            margin-bottom: 18px;
            color: #fff;
        }
        .form-group {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        label { font-size: 0.9rem; color: #aaa; }
        input, select {
            background: #111;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 12px 14px;
            color: #fff;
            font-size: 1rem;
            outline: none;
        }
        input:focus, select:focus { border-color: #e50914; }

        /* ASIENTOS */
        .pantalla {
            background: linear-gradient(90deg, #333, #555, #333);
            color: #ccc;
            text-align: center;
            padding: 10px;
            border-radius: 8px 8px 0 0;
            margin-bottom: 25px;
            font-size: 0.85rem;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        .mapa-asientos {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            min-height: 80px;
        }
        .fila {
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .letra-fila {
            width: 24px;
            text-align: center;
            font-weight: 600;
            color: #888;
            font-size: 0.85rem;
        }
        .asiento {
            width: 36px;
            height: 36px;
            border: none;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 700;
            transition: all 0.15s;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 3px;
            position: relative;
            border-radius: 8px 8px 4px 4px;
            box-shadow: inset 0 -3px 0 rgba(0,0,0,0.25);
        }
        .asiento::before {
            content: '';
            position: absolute;
            top: -5px;
            left: 3px;
            right: 3px;
            height: 9px;
            background: inherit;
            border-radius: 5px 5px 0 0;
        }
        .asiento.libre { background: #4b5563; color: #e5e7eb; }
        .asiento.libre:hover { background: #6b7280; transform: translateY(-2px); }
        .asiento.ocupado {
            background: #1e3a8a;
            color: #93c5fd;
            cursor: not-allowed;
            opacity: 0.85;
        }
        .asiento.seleccionado {
            background: #e50914;
            color: #fff;
            box-shadow: 0 0 0 2px #fff, inset 0 -3px 0 rgba(0,0,0,0.3);
        }
        .leyenda {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .leyenda-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: #aaa;
        }
        .cuadro {
            width: 18px;
            height: 18px;
            border-radius: 5px 5px 2px 2px;
        }
        .cuadro.libre { background: #4b5563; }
        .cuadro.ocupado { background: #1e3a8a; }
        .cuadro.seleccionado { background: #e50914; }

        .sin-mapa {
            text-align: center;
            color: #666;
            padding: 30px;
            font-size: 0.95rem;
        }

        /* SNACKS */
        .snacks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
        }
        .snack-card {
            background: #111;
            border: 2px solid #333;
            border-radius: 12px;
            padding: 14px;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .snack-card:hover { border-color: #555; }
        .snack-card.activo {
            border-color: #e50914;
            background: #1f0a0c;
        }
        .snack-card input { display: none; }
        .snack-nombre { font-weight: 600; color: #fff; }
        .snack-precio { color: #e50914; font-weight: 700; font-size: 0.95rem; }

        /* PAGO */
        .metodos {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .metodo-btn {
            background: #111;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 16px 10px;
            text-align: center;
            cursor: pointer;
            transition: 0.2s;
        }
        .metodo-btn:hover { border-color: #555; }
        .metodo-btn.activo {
            border-color: #e50914;
            background: #1f0a0c;
        }
        .metodo-btn input { display: none; }
        .metodo-btn .icono { font-size: 1.8rem; margin-bottom: 6px; }
        .metodo-btn .texto { font-weight: 600; font-size: 0.95rem; }

        .panel-pago {
            display: none;
            margin-top: 16px;
            background: #111;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 16px;
        }
        .panel-pago.activo { display: block; }
        .panel-pago h3 { font-size: 1rem; margin-bottom: 10px; color: #e50914; }
        .panel-pago p { color: #ccc; margin: 6px 0; font-size: 0.95rem; }

        .total-box {
            background: #111;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .total-box span { color: #e50914; }

        .btn {
            width: 100%;
            padding: 15px;
            background: #e50914;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
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

        @media (max-width: 600px) {
            .metodos { grid-template-columns: 1fr; }
            .asiento { width: 30px; height: 30px; font-size: 0.65rem; }
        }
    </style>
</head>
<body>

<header>
    <a href="mostrador_panel.php" class="logo">CineNex <span>| Mostrador</span></a>
    <a href="mostrador_panel.php" class="volver">← Volver</a>
</header>

<div class="container">
    <h1>Vender entradas</h1>
    <p class="subtitulo">Venta presencial en boletería</p>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="form-venta">

        <!-- 1. DATOS -->
        <div class="form-box">
            <h2>1. Datos del cliente y función</h2>

            <div class="form-group">
                <label>Nombre del cliente *</label>
                <input type="text" name="nombre" id="nombre" required placeholder="Ej: María González" oninput="capitalizarNombre(this)">
            </div>

            <div class="form-group">
                <label>Película *</label>
                <select name="pelicula" id="pelicula" required onchange="cargarHorarios()">
                    <option value="">Seleccionar película</option>
                    <?php foreach ($peliculas as $p): ?>
                        <option value="<?= htmlspecialchars($p['titulo']) ?>" data-id="<?= $p['id_pelicula'] ?>">
                            <?= htmlspecialchars($p['titulo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Horario *</label>
                <select name="horario_texto" id="horario" required onchange="cargarAsientos()">
                    <option value="">Primero elegí una película</option>
                </select>
            </div>

            <input type="hidden" name="precio_boleto" id="precio_boleto" value="0">
            <input type="hidden" name="asientos" id="asientos_input" value="">
        </div>

        <!-- 2. ASIENTOS -->
        <div class="form-box">
            <h2>2. Elegí los asientos</h2>
            <div class="pantalla">PANTALLA</div>
            <div class="mapa-asientos" id="mapa">
                <div class="sin-mapa">Seleccioná una película y un horario para ver los asientos</div>
            </div>
            <div class="leyenda">
                <div class="leyenda-item"><div class="cuadro libre"></div> Libre</div>
                <div class="leyenda-item"><div class="cuadro ocupado"></div> Ocupado</div>
                <div class="leyenda-item"><div class="cuadro seleccionado"></div> Seleccionado</div>
            </div>
        </div>

        <!-- 3. SNACKS -->
        <div class="form-box">
            <h2>3. Comidas y bebidas (opcional)</h2>
            <div class="snacks-grid">
                <?php foreach ($snacks as $s): ?>
                    <label class="snack-card" onclick="toggleSnack(this)">
                        <input type="checkbox" name="snacks[]" value="<?= $s['id'] ?>" data-precio="<?= $s['precio'] ?>">
                        <div class="snack-nombre"><?= htmlspecialchars($s['nombre']) ?></div>
                        <div class="snack-precio">$<?= $s['precio'] ?></div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 4. PAGO -->
        <div class="form-box">
            <h2>4. Método de pago *</h2>
            <div class="metodos">
                <label class="metodo-btn" onclick="elegirMetodo('efectivo', this)">
                    <input type="radio" name="metodo_pago" value="efectivo" required>
                    <div class="icono">💵</div>
                    <div class="texto">Efectivo</div>
                </label>
                <label class="metodo-btn" onclick="elegirMetodo('tarjeta', this)">
                    <input type="radio" name="metodo_pago" value="tarjeta">
                    <div class="icono">💳</div>
                    <div class="texto">Tarjeta</div>
                </label>
                <label class="metodo-btn" onclick="elegirMetodo('transferencia', this)">
                    <input type="radio" name="metodo_pago" value="transferencia">
                    <div class="icono">📱</div>
                    <div class="texto">Transferencia</div>
                </label>
            </div>

            <div class="panel-pago" id="panel-efectivo">
                <h3>Pago en efectivo</h3>
                <p>Cobrá el total en efectivo.</p>
                <p><strong>Total:</strong> <span id="total-efectivo">$0</span></p>
                <div class="form-group" style="margin-top:10px;">
                    <label>Monto recibido</label>
                    <input type="number" id="monto-recibido" placeholder="Ej: 500" oninput="calcularVuelto()">
                </div>
                <p id="vuelto-texto" style="display:none;"></p>
            </div>

            <div class="panel-pago" id="panel-tarjeta">
                <h3>Pago con tarjeta</h3>
                <p>Usá el posnet con el monto: <strong id="total-tarjeta">$0</strong></p>
                <p style="color:#888;">Cuando esté aprobado, generá el ticket.</p>
            </div>

            <div class="panel-pago" id="panel-transferencia">
                <h3>Pago por transferencia</h3>
                <p><strong>Banco:</strong> BBVA</p>
                <p><strong>Titular:</strong> CineNex S.A. de C.V.</p>
                <p><strong>CLABE:</strong> 012345678901234567</p>
                <p><strong>Monto:</strong> <span id="total-transferencia">$0</span></p>
            </div>
        </div>

        <div class="total-box">
            Total a cobrar: <span id="total-general">$0</span>
        </div>

        <button type="submit" class="btn">Generar e imprimir ticket</button>
    </form>
</div>

<script>
// Datos desde PHP
const funciones = <?= json_encode($funciones, JSON_UNESCAPED_UNICODE) ?>;
const asientosPorSala = <?= json_encode($asientos_por_sala, JSON_UNESCAPED_UNICODE) ?>;
const ocupadosPorFuncion = <?= json_encode($ocupados_por_funcion, JSON_UNESCAPED_UNICODE) ?>;

let asientosSeleccionados = [];
let precioUnitario = 0;
let funcionActual = null;

function capitalizarNombre(input) {
    const start = input.selectionStart;
    const end = input.selectionEnd;
    input.value = input.value.replace(/\b\w/g, l => l.toUpperCase());
    input.setSelectionRange(start, end);
}

function cargarHorarios() {
    const selectPeli = document.getElementById('pelicula');
    const idPeli = selectPeli.options[selectPeli.selectedIndex].dataset.id;
    const selectHorario = document.getElementById('horario');

    selectHorario.innerHTML = '<option value="">Seleccionar horario</option>';
    asientosSeleccionados = [];
    document.getElementById('asientos_input').value = '';
    document.getElementById('mapa').innerHTML = '<div class="sin-mapa">Seleccioná un horario para ver los asientos</div>';
    precioUnitario = 0;
    document.getElementById('precio_boleto').value = 0;
    calcularTotal();

    if (!idPeli) return;

    const filtradas = funciones.filter(f => String(f.id_pelicula) === String(idPeli));

    if (filtradas.length === 0) {
        selectHorario.innerHTML = '<option value="">No hay horarios disponibles</option>';
        return;
    }

    filtradas.forEach(f => {
        const fecha = new Date(f.fecha_hora);
        const texto = fecha.toLocaleString('es-AR', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        }) + ' — ' + f.sala + ' — $' + parseFloat(f.precio).toFixed(0);

        const opt = document.createElement('option');
        opt.value = fecha.toLocaleString('es-AR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' }) + ' (' + f.sala + ')';
        opt.textContent = texto;
        opt.dataset.idFuncion = f.id_funcion;
        opt.dataset.idSala = f.id_sala;
        opt.dataset.precio = f.precio;
        selectHorario.appendChild(opt);
    });
}

function cargarAsientos() {
    const selectHorario = document.getElementById('horario');
    const option = selectHorario.options[selectHorario.selectedIndex];

    asientosSeleccionados = [];
    document.getElementById('asientos_input').value = '';

    if (!option || !option.dataset.idFuncion) {
        document.getElementById('mapa').innerHTML = '<div class="sin-mapa">Seleccioná un horario</div>';
        precioUnitario = 0;
        document.getElementById('precio_boleto').value = 0;
        calcularTotal();
        return;
    }

    const idFuncion = option.dataset.idFuncion;
    const idSala = option.dataset.idSala;
    precioUnitario = parseFloat(option.dataset.precio) || 0;
    document.getElementById('precio_boleto').value = precioUnitario;

    const asientos = asientosPorSala[idSala] || [];
    const ocupados = ocupadosPorFuncion[idFuncion] || [];

    if (asientos.length === 0) {
        document.getElementById('mapa').innerHTML = '<div class="sin-mapa">Esta sala no tiene asientos cargados</div>';
        calcularTotal();
        return;
    }

    // Agrupar por fila
    const filas = {};
    asientos.forEach(a => {
        if (!filas[a.fila]) filas[a.fila] = [];
        filas[a.fila].push(a);
    });

    let html = '';
    Object.keys(filas).sort().forEach(fila => {
        html += `<div class="fila"><span class="letra-fila">${fila}</span>`;
        filas[fila].sort((a, b) => a.numero - b.numero).forEach(a => {
            const ocupado = ocupados.includes(a.codigo);
            const clase = ocupado ? 'ocupado' : 'libre';
            const disabled = ocupado ? 'disabled' : '';
            html += `<button type="button" class="asiento ${clase}" data-asiento="${a.codigo}" ${disabled} onclick="toggleAsiento(this)">${a.numero}</button>`;
        });
        html += `<span class="letra-fila">${fila}</span></div>`;
    });

    document.getElementById('mapa').innerHTML = html;
    calcularTotal();
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
    document.getElementById('asientos_input').value = asientosSeleccionados.sort().join(', ');
    calcularTotal();
}

function toggleSnack(card) {
    const cb = card.querySelector('input');
    setTimeout(() => {
        card.classList.toggle('activo', cb.checked);
        calcularTotal();
    }, 0);
}

function calcularTotal() {
    let totalSnacks = 0;
    document.querySelectorAll('input[name="snacks[]"]:checked').forEach(cb => {
        totalSnacks += parseFloat(cb.dataset.precio) || 0;
    });

    const total = (precioUnitario * asientosSeleccionados.length) + totalSnacks;

    document.getElementById('total-general').textContent = '$' + total.toFixed(2);
    document.getElementById('total-efectivo').textContent = '$' + total.toFixed(2);
    document.getElementById('total-tarjeta').textContent = '$' + total.toFixed(2);
    document.getElementById('total-transferencia').textContent = '$' + total.toFixed(2);
    calcularVuelto();
}

function calcularVuelto() {
    const total = parseFloat(document.getElementById('total-general').textContent.replace('$', '')) || 0;
    const recibido = parseFloat(document.getElementById('monto-recibido').value) || 0;
    const el = document.getElementById('vuelto-texto');

    if (recibido > 0) {
        const vuelto = recibido - total;
        el.style.display = 'block';
        if (vuelto >= 0) {
            el.style.color = '#86efac';
            el.textContent = 'Vuelto: $' + vuelto.toFixed(2);
        } else {
            el.style.color = '#fca5a5';
            el.textContent = 'Falta: $' + Math.abs(vuelto).toFixed(2);
        }
    } else {
        el.style.display = 'none';
    }
}

function elegirMetodo(metodo, el) {
    document.querySelectorAll('.metodo-btn').forEach(b => b.classList.remove('activo'));
    el.classList.add('activo');
    el.querySelector('input').checked = true;
    document.querySelectorAll('.panel-pago').forEach(p => p.classList.remove('activo'));
    document.getElementById('panel-' + metodo).classList.add('activo');
}

// Validar asientos antes de enviar
document.getElementById('form-venta').addEventListener('submit', function(e) {
    if (asientosSeleccionados.length === 0) {
        e.preventDefault();
        alert('Debés seleccionar al menos un asiento.');
    }
});
</script>
</body>
</html>