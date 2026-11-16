<?php
session_start();

if (isset($_SESSION['mostrador_logueado'])) {
    header("Location: mostrador_panel.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST['usuario'] ?? '';
    $clave = $_POST['clave'] ?? '';

    // Credenciales del mostrador (podés cambiarlas)
    if ($usuario === "mostrador" && $clave === "cine2026") {
        $_SESSION['mostrador_logueado'] = true;
        $_SESSION['mostrador_nombre'] = "Personal de mostrador";
        header("Location: mostrador_panel.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mostrador - CineNex</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0f0f0f;
            color: #e5e5e5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 380px;
        }
        .logo {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 800;
            color: #e50914;
            margin-bottom: 8px;
        }
        .logo span { color: #fff; font-weight: 400; font-size: 1rem; }
        h2 {
            text-align: center;
            color: #aaa;
            font-weight: 500;
            margin-bottom: 30px;
            font-size: 1.05rem;
        }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; color: #aaa; font-size: 0.9rem; }
        input {
            width: 100%;
            padding: 12px 14px;
            background: #111;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            outline: none;
        }
        input:focus { border-color: #e50914; }
        .btn {
            width: 100%;
            padding: 13px;
            background: #e50914;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
        }
        .btn:hover { background: #f40612; }
        .error {
            background: #3b0a0a;
            color: #fca5a5;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo">CineNex <span>| Mostrador</span></div>
        <h2>Acceso personal de boletería</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="usuario" required autofocus>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="clave" required>
            </div>
            <button type="submit" class="btn">Ingresar</button>
        </form>
    </div>
</body>
</html>