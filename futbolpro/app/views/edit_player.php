<?php
session_start();
require_once 'access_control.php';

// Redirigir si no está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../presenters/PlayerPresenter.php';

$playerPresenter = new PlayerPresenter();
$message = '';

// Obtener el ID del jugador a editar
$player_id = $_GET['id'] ?? null;
$player = null;

if ($player_id) {
    $player = $playerPresenter->getPlayerById($player_id);
    
    // Verificar que el jugador existe y pertenece al usuario
    if (!$player || $player['usuario_id'] != $_SESSION['user_id']) {
        header("Location: players.php");
        exit();
    }
} else {
    header("Location: players.php");
    exit();
}

// Actualizar jugador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_player'])) {
    $result = $playerPresenter->updatePlayer(
        $player_id,
        $_POST['nombre'],
        $_POST['fecha_nacimiento'],
        $_POST['posicion'],
        $_POST['equipo']
    );
    
    $message = $result['message'];
    if ($result['success']) {
        header("Location: players.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Jugador - FutbolPro</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="logo">FutbolPro</div>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="players.php">Jugadores</a></li>
                    <li><a href="stats.php">Estadísticas</a></li>
                    <li><a href="injuries.php">Lesiones</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container dashboard">
        <div class="dashboard-header">
            <h1>Editar Jugador</h1>
            <a href="players.php" class="btn">← Volver a Jugadores</a>
        </div>

        <?php if (!empty($message)): ?>
            <div style="padding: 10px; background: <?php echo strpos($message, 'correctamente') !== false ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo strpos($message, 'correctamente') !== false ? '#155724' : '#721c24'; ?>; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="update_player" value="1">
                
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre Completo *</label>
                    <input class="form-input" type="text" id="nombre" name="nombre" 
                           value="<?php echo htmlspecialchars($player['nombre'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="fecha_nacimiento">Fecha de Nacimiento</label>
                    <input class="form-input" type="date" id="fecha_nacimiento" name="fecha_nacimiento" 
                           value="<?php echo $player['fecha_nacimiento'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="posicion">Posición *</label>
                    <select class="form-input" id="posicion" name="posicion" required>
                        <option value="">Seleccione una posición</option>
                        <option value="portero" <?php echo ($player['posicion'] ?? '') == 'portero' ? 'selected' : ''; ?>>Portero</option>
                        <option value="defensa" <?php echo ($player['posicion'] ?? '') == 'defensa' ? 'selected' : ''; ?>>Defensa</option>
                        <option value="centrocampista" <?php echo ($player['posicion'] ?? '') == 'centrocampista' ? 'selected' : ''; ?>>Centrocampista</option>
                        <option value="delantero" <?php echo ($player['posicion'] ?? '') == 'delantero' ? 'selected' : ''; ?>>Delantero</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="equipo">Equipo</label>
                    <input class="form-input" type="text" id="equipo" name="equipo" 
                           value="<?php echo htmlspecialchars($player['equipo'] ?? ''); ?>">
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary">Actualizar Jugador</button>
                    <a href="players.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>