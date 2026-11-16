<?php
session_start();

if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header("Location: admin_login.php");
    exit;
}
?>