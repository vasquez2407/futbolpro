<?php
session_start();
require_once 'access_control.php';

// Redirigir si no está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../presenters/PlayerPresenter.php';
require_once '../presenters/StatsPresenter.php';

$playerPresenter = new PlayerPresenter();
$statsPresenter = new StatsPresenter();
$message = '';
$players = $playerPresenter->getAllPlayers();


// Registrar estadísticas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_stats'])) {
    $result = $statsPresenter->createStats(
        $_POST['jugador_id'],
        $_POST['partido_date'],
        $_POST['goles'] ?? 0,
        $_POST['asistencias'] ?? 0,
        $_POST['minutos_jugados'] ?? 0,
        $_POST['tarjetas_amarillas'] ?? 0,
        $_POST['tarjetas_rojas'] ?? 0,
        $_POST['equipo_rival']
    );
    
    $message = $result['message'];
    if ($result['success']) {
        header("Location: stats.php");
        exit();
    }
}

// Obtener estadísticas si se seleccionó un jugador
$playerStats = [];
$selectedPlayer = null;
$performancePrediction = null;

if (isset($_GET['player_id']) && !empty($_GET['player_id'])) {
    $playerStats = $statsPresenter->getStatsByPlayer($_GET['player_id']);
    $selectedPlayer = $playerPresenter->getPlayerById($_GET['player_id']);
    
    // Obtener predicción de rendimiento
    $performancePrediction = $statsPresenter->predictPerformance($_GET['player_id']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Estadísticas - FutbolPro</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="logo">FutbolPro</div>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <?php if ($_SESSION['user_tipo'] == 'jugador'): ?>
                        <li><a href="players.php">Jugadores</a></li>
                        <li><a href="stats.php" class="active">Estadísticas</a></li>
                        <li><a href="injuries.php">Lesiones</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 'entrenador'): ?>
                        <li><a href="players.php">Jugadores</a></li>
                        <li><a href="stats.php" class="active">Estadísticas</a></li>
                        <li><a href="injuries.php">Lesiones</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 'analista'): ?>
                        <li><a href="stats.php" class="active">Estadísticas</a></li>
                        <li><a href="compare_players.php" class="active">Comparar</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 'administrador'): ?>
                        <li><a href="players.php">Jugadores</a></li>
                        <li><a href="stats.php" class="active">Estadísticas</a></li>
                        <li><a href="compare_players.php">Comparar</a></li>
                        <li><a href="injuries.php">Lesiones</a></li>
                        <li><a href="admin.php">Administración</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container dashboard">
        <div class="dashboard-header">
    <h1>Gestión de Estadísticas</h1>
    <?php if ($_SESSION['user_tipo'] == 'jugador' || $_SESSION['user_tipo'] == 'administrador'): ?>
        <button onclick="document.getElementById('statsModal').style.display='block'" class="btn">+ Registrar Estadísticas</button>
    <?php endif; ?>
</div>


        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-error' : 'alert-success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Seleccionar Jugador para Analizar</h2>
            </div>
            <form method="GET" action="stats.php" class="form-inline">
                <div class="form-group">
                    <label class="form-label" for="player_id">Jugador</label>
                    <select class="form-input" id="player_id" name="player_id" required>
                        <option value="">Seleccione un jugador</option>
                        <?php foreach ($players as $player): ?>
                        <option value="<?php echo $player['id']; ?>" <?php echo isset($_GET['player_id']) && $_GET['player_id'] == $player['id'] ? 'selected' : ''; ?>>
                            <?php echo $player['nombre']; ?> (<?php echo $player['posicion']; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Analizar</button>
            </form>
        </div>

        <?php if ($selectedPlayer): ?>
        <!-- Resumen del jugador -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($playerStats); ?></div>
                <div class="stat-label">Partidos Jugados</div>
            </div>
            
            <?php
            $totalGoles = 0;
            $totalAsistencias = 0;
            $totalMinutos = 0;
            
            foreach ($playerStats as $stat) {
                $totalGoles += $stat['goles'];
                $totalAsistencias += $stat['asistencias'];
                $totalMinutos += $stat['minutos_jugados'];
            }
            
            $avgGoles = count($playerStats) > 0 ? round($totalGoles / count($playerStats), 2) : 0;
            $avgAsistencias = count($playerStats) > 0 ? round($totalAsistencias / count($playerStats), 2) : 0;
            ?>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalGoles; ?></div>
                <div class="stat-label">Goles Totales</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalAsistencias; ?></div>
                <div class="stat-label">Asistencias Totales</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $avgGoles; ?></div>
                <div class="stat-label">Goles por Partido</div>
            </div>
        </div>

        <!-- Predicción de rendimiento -->
        <?php if ($performancePrediction && $performancePrediction['success']): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Predicción de Rendimiento</h2>
                <span class="badge badge-primary">Sistema Inteligente</span>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $performancePrediction['predicted_goals']; ?></div>
                    <div class="stat-label">Goles Esperados (próximo partido)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $performancePrediction['predicted_assists']; ?></div>
                    <div class="stat-label">Asistencias Esperadas (próximo partido)</div>
                </div>
            </div>
            <p><small>Basado en <?php echo $performancePrediction['based_on']; ?></small></p>
        </div>
        <?php endif; ?>

        <!-- Gráfico de rendimiento -->
        <div class="chart-container">
            <h2 class="chart-header">Evolución de Rendimiento - <?php echo $selectedPlayer['nombre']; ?></h2>
            <canvas id="performanceChart" height="100"></canvas>
        </div>

        <!-- Tabla de estadísticas -->
        <div class="table-container">
            <h2 class="chart-header">Estadísticas por Partido</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>VS</th>
                        <th>Goles</th>
                        <th>Asistencias</th>
                        <th>Minutos</th>
                        <th>Tarj. Amarillas</th>
                        <th>Tarj. Rojas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($playerStats)): ?>
                        <?php foreach ($playerStats as $stat): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($stat['partido_date'])); ?></td>
                            <td><?php echo $stat['equipo_rival']; ?></td>
                            <td><?php echo $stat['goles']; ?></td>
                            <td><?php echo $stat['asistencias']; ?></td>
                            <td><?php echo $stat['minutos_jugados']; ?></td>
                            <td><?php echo $stat['tarjetas_amarillas']; ?></td>
                            <td><?php echo $stat['tarjetas_rojas']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No hay estadísticas registradas para este jugador</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if ($_SESSION['user_tipo'] == 'jugador' || $_SESSION['user_tipo'] == 'administrador'): ?>
        <!-- Modal para registrar estadísticas -->
        <div id="statsModal" class="modal" style="display: none;">
            <div class="form-container" style="margin: 5% auto; width: 90%; max-width: 600px;">
                <h2 class="form-title">Registrar Estadísticas de Partido</h2>
                <span onclick="document.getElementById('statsModal').style.display='none'" class="close">&times;</span>
                
                <form method="POST" action="">
                    <input type="hidden" name="add_stats" value="1">
                    
                    <div class="form-group">
                        <label class="form-label" for="jugador_id">Jugador *</label>
                        <select class="form-input" id="jugador_id" name="jugador_id" required>
                            <option value="">Seleccione un jugador</option>
                            <?php foreach ($players as $player): ?>
                            <option value="<?php echo $player['id']; ?>"><?php echo $player['nombre']; ?> (<?php echo $player['posicion']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="partido_date">Fecha del Partido *</label>
                        <input class="form-input" type="date" id="partido_date" name="partido_date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="equipo_rival">Equipo Rival *</label>
                        <input class="form-input" type="text" id="equipo_rival" name="equipo_rival" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="goles">Goles</label>
                            <input class="form-input" type="number" id="goles" name="goles" min="0" value="0">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="asistencias">Asistencias</label>
                            <input class="form-input" type="number" id="asistencias" name="asistencias" min="0" value="0">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="minutos_jugados">Minutos Jugados</label>
                            <input class="form-input" type="number" id="minutos_jugados" name="minutos_jugados" min="0" max="120" value="90">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="tarjetas_amarillas">Tarjetas Amarillas</label>
                            <input class="form-input" type="number" id="tarjetas_amarillas" name="tarjetas_amarillas" min="0" max="2" value="0">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="tarjetas_rojas">Tarjetas Rojas</label>
                            <input class="form-input" type="number" id="tarjetas_rojas" name="tarjetas_rojas" min="0" max="1" value="0">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-block">Registrar Estadísticas</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <?php if ($selectedPlayer && !empty($playerStats)): ?>
    <script>
        // Preparar datos para el gráfico
        const dates = [<?php echo implode(',', array_map(function($stat) { return "'" . date('d/m/Y', strtotime($stat['partido_date'])) . "'"; }, $playerStats)); ?>].reverse();
        const goals = [<?php echo implode(',', array_column($playerStats, 'goles')); ?>].reverse();
        const assists = [<?php echo implode(',', array_column($playerStats, 'asistencias')); ?>].reverse();
        
        // Gráfico de rendimiento
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Goles',
                    data: goals,
                    borderColor: '#1e3a8a',
                    backgroundColor: 'rgba(30, 58, 138, 0.1)',
                    tension: 0.3,
                    fill: true
                }, {
                    label: 'Asistencias',
                    data: assists,
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Evolución de Rendimiento'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Fecha del Partido'
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>

    <script>
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('statsModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>