<?php
session_start();
require_once '../config/conexion.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (!empty($nombre) && !empty($email) && !empty($password)) {

        // Validar que las contraseñas coincidan
        if ($password !== $confirm_password) {
            $error = "Las contraseñas no coinciden";
        }
        // Validar longitud de contraseña
        elseif (strlen($password) < 6) {
            $error = "La contraseña debe tener al menos 6 caracteres";
        }
        else {
            // Verificar si el email ya existe
            $sql = "SELECT id FROM usuarios WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $error = "El correo electrónico ya está registrado";
            } else {
                // Insertar nuevo usuario (solo los campos que existen)
                $hashedPassword = md5($password);
                $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'usuario')";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$nombre, $email, $hashedPassword])) {
                    $success = "¡Registro exitoso! Redirigiendo al login...";
                    // Guardar email para prellenar en login
                    $_SESSION['registro_email'] = $email;
                    
                    // Redirigir después de 2 segundos
                    header("refresh:2; url=login.php");
                } else {
                    $error = "Error al registrar usuario. Intenta nuevamente.";
                }
            }
        }
    } else {
        $error = "Completa todos los campos obligatorios";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="theme-color" content="#10b981">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>CityFix AI - Crear Cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f0fdf4;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
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
        
        .logo-icon i {
            font-size: 40px;
            color: white;
        }
        
        .logo-container h2 {
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .logo-container p {
            color: #64748b;
            font-size: 0.85rem;
        }
        
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
            font-size: 1rem;
            width: 100%;
            margin-bottom: 16px;
            transition: all 0.2s;
        }
        
        .form-control-custom:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            border-radius: 40px;
            padding: 14px;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16,185,129,0.4);
        }
        
        .btn-register:active {
            transform: translateY(0);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #64748b;
            font-size: 0.85rem;
        }
        
        .login-link a {
            color: #10b981;
            text-decoration: none;
            font-weight: 600;
        }
        
        .error-msg, .success-msg {
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 0.8rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .error-msg {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .success-msg {
            background: #d1fae5;
            color: #059669;
        }
        
        .error-msg i, .success-msg i {
            font-size: 1rem;
        }
        
        .input-group-icon {
            position: relative;
        }
        
        .input-group-icon i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
        }
        
        .input-group-icon .form-control-custom {
            padding-left: 45px;
        }
        
        .terms {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 16px 0;
            font-size: 0.75rem;
            color: #64748b;
        }
        
        .terms input {
            width: 18px;
            height: 18px;
            accent-color: #10b981;
        }
        
        .terms a {
            color: #10b981;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="logo-container">
        <div class="logo-icon">
            <i class="fas fa-user-plus"></i>
        </div>
        <h2>Crear Cuenta</h2>
        <p>Regístrate para comenzar</p>
    </div>

    <?php if($error): ?>
        <div class="error-msg">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="success-msg">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group-icon">
            <i class="fas fa-user"></i>
            <input type="text" name="nombre" class="form-control-custom" placeholder="Nombre completo" required autocomplete="name" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </div>
        
        <div class="input-group-icon">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" class="form-control-custom" placeholder="Correo electrónico" required autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        
        <div class="input-group-icon">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" class="form-control-custom" placeholder="Contraseña" required autocomplete="new-password">
        </div>
        
        <div class="input-group-icon">
            <i class="fas fa-check-circle"></i>
            <input type="password" name="confirm_password" class="form-control-custom" placeholder="Confirmar contraseña" required>
        </div>
        
        <div class="terms">
            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms">
                Acepto los <a href="#" onclick="mostrarTerminos(); return false;">Términos y Condiciones</a>
            </label>
        </div>

        <button type="submit" class="btn-register">
            <i class="fas fa-user-plus me-2"></i>Registrarme
        </button>
    </form>

    <div class="login-link">
        ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
    </div>
</div>

<script>
    function mostrarTerminos() {
        alert('Términos y Condiciones de CityFix AI\n\n1. Los reportes son de dominio público\n2. No compartimos tus datos personales\n3. Puedes cancelar tu suscripción en cualquier momento\n4. Las notificaciones son opcionales\n\nVersión 1.0 - CityFix AI');
    }
</script>
</body>
</html>