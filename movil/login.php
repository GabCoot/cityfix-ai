<?php
session_start();
require_once '../config/conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && $usuario['password'] == md5($password)) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_rol'] = $usuario['rol'];

            // Redirigir al index con parámetros para guardar en localStorage
            header("Location: index.html?id={$usuario['id']}&nombre=" . urlencode($usuario['nombre']) . "&email=" . urlencode($usuario['email']));
            exit;
        } else {
            $error = "Correo o contraseña incorrectos";
        }
    } else {
        $error = "Completa todos los campos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>CityFix AI - Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f0fdf4;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(16,185,129,0.3);
        }
        .logo-icon i { font-size: 40px; color: white; }
        .auth-card {
            background: white;
            border-radius: 32px;
            padding: 32px 28px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        .form-control-custom {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 16px;
            width: 100%;
            margin-bottom: 16px;
            font-size: 1rem;
        }
        .form-control-custom:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
        }
        .btn-login {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            border-radius: 40px;
            padding: 14px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #64748b;
            font-size: 0.85rem;
        }
        .register-link a { color: #10b981; text-decoration: none; font-weight: 600; }
        .error-msg {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 0.8rem;
            margin-bottom: 20px;
        }
        .input-group-icon { position: relative; }
        .input-group-icon i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        .input-group-icon .form-control-custom { padding-left: 45px; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="logo-icon"><i class="fas fa-city"></i></div>
    <h2 class="text-center mb-2">CityFix AI</h2>
    <p class="text-center text-muted mb-4">Bienvenido de vuelta</p>

    <?php if($error): ?>
        <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group-icon">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" class="form-control-custom" placeholder="Correo electrónico" required>
        </div>
        <div class="input-group-icon">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" class="form-control-custom" placeholder="Contraseña" required>
        </div>
        <button type="submit" class="btn-login"><i class="fas fa-arrow-right-to-bracket me-2"></i>Iniciar Sesión</button>
    </form>
    <div class="register-link">¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></div>
</div>

<script>
    // Capturar los parámetros de la URL si vienen del login
    const urlParams = new URLSearchParams(window.location.search);
    const usuarioId = urlParams.get('id');
    const usuarioNombre = urlParams.get('nombre');
    const usuarioEmail = urlParams.get('email');
    
    if(usuarioId && usuarioNombre && usuarioEmail) {
        // Guardar en localStorage
        localStorage.setItem('usuarioId', usuarioId);
        localStorage.setItem('usuarioNombre', decodeURIComponent(usuarioNombre));
        localStorage.setItem('usuarioEmail', decodeURIComponent(usuarioEmail));
        
        // Redirigir al index sin parámetros
        window.location.href = 'index.html';
    }
</script>
</body>
</html>