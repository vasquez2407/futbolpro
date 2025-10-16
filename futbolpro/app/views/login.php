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
    $result = $presenter->loginUser($_POST['email'], $_POST['password']);
    
    if ($result['success']) {
        header("Location: dashboard.php");
        exit();
    } else {
        $message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - FutbolPro</title>
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
            <h2 class="form-title">Iniciar Sesión</h2>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-error"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-input" type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <input class="form-input" type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-block">Iniciar Sesión</button>
            </form>
            
            <p style="text-align: center; margin-top: 1rem;">
                ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
            </p>
        </div>
    </main>
</body>
</html>