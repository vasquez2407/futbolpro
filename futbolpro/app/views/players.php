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
$playerPresenter = new PlayerPresenter();
$message = '';

$players = $playerPresenter->getAllPlayers();


// Crear jugador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_player'])) {
    $result = $playerPresenter->createPlayer(
        $_SESSION['user_id'],
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

// Eliminar jugador
if (isset($_GET['delete_id'])) {
    $result = $playerPresenter->deletePlayer($_GET['delete_id']);
    $message = $result['message'];
    header("Location: players.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Jugadores - FutbolPro</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="logo">FutbolPro</div>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <?php if ($_SESSION['user_tipo'] == 'jugador' || $_SESSION['user_tipo'] == 'administrador' || $_SESSION['user_tipo'] == 'entrenador'): ?>
                        <li><a href="players.php" class="active">Jugadores</a></li>
                    <?php endif; ?>
                    <?php if ($_SESSION['user_tipo'] == 'jugador' || $_SESSION['user_tipo'] == 'entrenador' || $_SESSION['user_tipo'] == 'analista' || $_SESSION['user_tipo'] == 'administrador'): ?>
                        <li><a href="stats.php">Estadísticas</a></li>
                    <?php endif; ?>
                    <?php if ($_SESSION['user_tipo'] == 'administrador'): ?>
                        <li><a href="compare_players.php">Comparar</a></li>
                    <?php endif; ?>
                    <?php if ($_SESSION['user_tipo'] == 'jugador' || $_SESSION['user_tipo'] == 'entrenador' || $_SESSION['user_tipo'] == 'administrador'): ?>
                        <li><a href="injuries.php">Lesiones</a></li>
                    <?php endif; ?>
                    <?php if ($_SESSION['user_tipo'] == 'administrador'): ?>
                        <li><a href="admin.php">Administracion</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container dashboard">
        <div class="dashboard-header">
            <h1>Gestión de Jugadores</h1>
            <?php if ($_SESSION['user_tipo'] == 'jugador' || $_SESSION['user_tipo'] == 'administrador'): ?>
                <button onclick="document.getElementById('createModal').style.display='block'" class="btn">+ Nuevo Jugador</button>
            <?php endif; ?>
        </div>

        <?php if (!empty($message)): ?>
            <div style="padding: 10px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Posición</th>
                        <th>Equipo</th>
                        <th>Fecha Nacimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($players)): ?>
                        <?php foreach ($players as $player): ?>
                        <tr>
                            <td><?php echo $player['nombre']; ?></td>
                            <td><?php echo $player['posicion']; ?></td>
                            <td><?php echo $player['equipo'] ?? 'N/A'; ?></td>
                            <td><?php echo $player['fecha_nacimiento'] ? date('d/m/Y', strtotime($player['fecha_nacimiento'])) : 'N/A'; ?></td>
                            <td>
                            <?php if ($_SESSION['user_tipo'] == 'jugador' || $_SESSION['user_tipo'] == 'administrador'): ?>
                                <a href="edit_player.php?id=<?php echo $player['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem;">Editar</a>
                                <a href="players.php?delete_id=<?php echo $player['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: var(--danger-color);" onclick="return confirm('¿Estás seguro de eliminar este jugador?')">Eliminar</a>
                            <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No hay jugadores registrados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal para crear jugador -->
        <div id="createModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
            <div class="form-container" style="margin: 5% auto; width: 90%; max-width: 500px;">
                <h2 class="form-title">Nuevo Jugador</h2>
                <span onclick="document.getElementById('createModal').style.display='none'" style="float: right; cursor: pointer; font-size: 1.5rem;">&times;</span>
                
                <form method="POST" action="">
                    <input type="hidden" name="create_player" value="1">
                    
                    <div class="form-group">
                        <label class="form-label" for="nombre">Nombre Completo *</label>
                        <input class="form-input" type="text" id="nombre" name="nombre" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input class="form-input" type="date" id="fecha_nacimiento" name="fecha_nacimiento">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="posicion">Posición *</label>
                        <select class="form-input" id="posicion" name="posicion" required>
                            <option value="">Seleccione una posición</option>
                            <option value="portero">Portero</option>
                            <option value="defensa">Defensa</option>
                            <option value="centrocampista">Centrocampista</option>
                            <option value="delantero">Delantero</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="equipo">Equipo</label>
                        <input class="form-input" type="text" id="equipo" name="equipo">
                    </div>
                    
                    <button type="submit" class="btn btn-block">Crear Jugador</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('createModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>