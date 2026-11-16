<?php
session_start();
session_destroy();
header("Location: mostrador_login.php");
exit;
?>