<?php
require 'conexion.php';

// Solo aceptar POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$nombre   = trim($_POST['nombre'] ?? '');
$email    = trim($_POST['email'] ?? '');
$pelicula = trim($_POST['pelicula'] ?? '');
$horario  = trim($_POST['horario'] ?? '');
$asientos = trim($_POST['asientos'] ?? '');
$snacks   = trim($_POST['snacks'] ?? 'Ninguna');
$total    = floatval($_POST['total'] ?? 0);
$imagen   = trim($_POST['imagen'] ?? '');

// Validación básica
if (empty($nombre) || empty($pelicula) || empty($horario) || empty($asientos) || $total <= 0) {
    die("Datos incompletos");
}

// Generar código único
$codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

// Guardar en la base de datos
$stmt = $conn->prepare("
    INSERT INTO tickets 
    (codigo, nombre_cliente, email, pelicula, horario, asientos, snacks, total, imagen_url, origen, estado)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'online', 'valido')
");

$stmt->bind_param(
    "sssssssds",
    $codigo,
    $nombre,
    $email,
    $pelicula,
    $horario,
    $asientos,
    $snacks,
    $total,
    $imagen
);

if ($stmt->execute()) {
    // Redirigir al ticket con todos los datos
    $params = http_build_query([
        'codigo'   => $codigo,
        'nombre'   => $nombre,
        'email'    => $email,
        'pelicula' => $pelicula,
        'horario'  => $horario,
        'asientos' => $asientos,
        'snacks'   => $snacks,
        'total'    => $total,
        'imagen'   => $imagen
    ]);

    header("Location: ticket.php?" . $params);
    exit;
} else {
    die("Error al guardar el ticket: " . $conn->error);
}

$stmt->close();
$conn->close();
?>