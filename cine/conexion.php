<?php
$host = "localhost";
$usuario = "root";
$clave = "";                // Cambiá si tenés contraseña
$base_datos = "cine";

$conn = new mysqli($host, $usuario, $clave, $base_datos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>