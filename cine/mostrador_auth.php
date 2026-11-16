<?php
session_start();

if (!isset($_SESSION['mostrador_logueado']) || $_SESSION['mostrador_logueado'] !== true) {
    header("Location: mostrador_login.php");
    exit;
}
?>