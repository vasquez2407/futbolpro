<?php
session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once '../presenters/UserPresenter.php';

$presenter = new UserPresenter();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $presenter->registerUser(
        $_POST['email'],
        $_POST['password'],
        $_POST['tipo'],
        $_POST['nombre']
    );
    
    $message = $result['message'];
    if ($result['success']) {
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - FutbolPro</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="logo">FutbolPro</div>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="form-container">
            <h2 class="form-title">Crear Cuenta</h2>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-error"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre Completo</label>
                    <input class="form-input" type="text" id="nombre" name="nombre" required
                           value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-input" type="email" id="email" name="email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <input class="form-input" type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="tipo">Tipo de Usuario</label>
                    <select class="form-input" id="tipo" name="tipo" required>
                        <option value="">Seleccione un tipo</option>
                        <option value="jugador" <?php echo isset($_POST['tipo']) && $_POST['tipo'] == 'jugador' ? 'selected' : ''; ?>>Jugador</option>
                        <option value="entrenador" <?php echo isset($_POST['tipo']) && $_POST['tipo'] == 'entrenador' ? 'selected' : ''; ?>>Entrenador</option>
                        <option value="analista" <?php echo isset($_POST['tipo']) && $_POST['tipo'] == 'analista' ? 'selected' : ''; ?>>Analista</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-block">Registrarse</button>
            </form>
            
            <p style="text-align: center; margin-top: 1rem;">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
            </p>
        </div>
    </main>
</body>
</html>