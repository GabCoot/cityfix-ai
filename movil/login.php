<?php
session_start();
require_once '../config/conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {

        // Buscar usuario
        $sql = "SELECT * FROM usuarios WHERE email = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Validar usuario
        if ($usuario && $usuario['password'] == md5($password)) {

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_rol'] = $usuario['rol'];

            // Redirección al módulo móvil
            header("Location: index.html");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CityFix AI</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f0fdf4;
            font-family: Arial;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .card-login {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .btn-login {
            background: #10b981;
            color: white;
            width: 100%;
            border: none;
            padding: 12px;
            border-radius: 30px;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="card-login">

    <h3 class="text-center mb-3">CityFix AI</h3>
    <p class="text-center text-muted">Iniciar sesión</p>

    <?php if($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

        <input type="email" name="email" class="form-control mb-3" placeholder="Correo" required>

        <input type="password" name="password" class="form-control mb-3" placeholder="Contraseña" required>

        <button class="btn-login" type="submit">
            Entrar
        </button>

    </form>

</div>

</body>
</html>