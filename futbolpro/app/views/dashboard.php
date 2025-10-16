<?php
session_start();
require_once 'access_control.php';

// Incluir presentadores necesarios
require_once '../presenters/UserPresenter.php';
require_once '../presenters/PlayerPresenter.php';
require_once '../presenters/StatsPresenter.php';
require_once '../presenters/InjuryPresenter.php';

$userPresenter = new UserPresenter();
$playerPresenter = new PlayerPresenter();
$statsPresenter = new StatsPresenter();
$injuryPresenter = new InjuryPresenter();

$user = $userPresenter->getUserProfile($_SESSION['user_id']);
$playerCount = $playerPresenter->getPlayersCount();

// 📊 OBTENER ESTADÍSTICAS REALES DE LA BASE DE DATOS
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Contar partidos registrados
$partidosQuery = "SELECT COUNT(*) as total FROM estadisticas";
$partidosStmt = $db->prepare($partidosQuery);
$partidosStmt->execute();
$partidosData = $partidosStmt->fetch(PDO::FETCH_ASSOC);
$partidosCount = $partidosData['total'];

// Sumar goles totales
$golesQuery = "SELECT SUM(goles) as total FROM estadisticas";
$golesStmt = $db->prepare($golesQuery);
$golesStmt->execute();
$golesData = $golesStmt->fetch(PDO::FETCH_ASSOC);
$golesTotal = $golesData['total'] ? $golesData['total'] : 0;

// Sumar asistencias totales
$asistenciasQuery = "SELECT SUM(asistencias) as total FROM estadisticas";
$asistenciasStmt = $db->prepare($asistenciasQuery);
$asistenciasStmt->execute();
$asistenciasData = $asistenciasStmt->fetch(PDO::FETCH_ASSOC);
$asistenciasTotal = $asistenciasData['total'] ? $asistenciasData['total'] : 0;

// Obtener estadísticas recientes
$recentStats = $statsPresenter->getRecentStats(5);

// Obtener datos reales para el gráfico (últimos 6 meses)
$chartLabels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'];
$chartGoles = [0, 0, 0, 0, 0, 0];
$chartAsistencias = [0, 0, 0, 0, 0, 0];

if ($partidosCount > 0) {
    $chartQuery = "SELECT 
                    DATE_FORMAT(partido_date, '%b') as mes,
                    YEAR(partido_date) as año,
                    SUM(goles) as goles,
                    SUM(asistencias) as asistencias
                  FROM estadisticas 
                  WHERE partido_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                  GROUP BY YEAR(partido_date), MONTH(partido_date)
                  ORDER BY año, MONTH(partido_date)";
    
    $chartStmt = $db->prepare($chartQuery);
    $chartStmt->execute();
    $chartResults = $chartStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($chartResults)) {
        $chartLabels = [];
        $chartGoles = [];
        $chartAsistencias = [];
        
        foreach ($chartResults as $result) {
            $chartLabels[] = $result['mes'];
            $chartGoles[] = $result['goles'];
            $chartAsistencias[] = $result['asistencias'];
        }
    }
}

// Dashboard según rol
$userStats = [];
if ($_SESSION['user_tipo'] == 'jugador') {
    $player = $playerPresenter->getPlayersByUser($_SESSION['user_id']);
    if (!empty($player)) {
        $player_id = $player[0]['id'];
        $userStats = $statsPresenter->getStatsByPlayer($player_id);
    }
} elseif ($_SESSION['user_tipo'] == 'entrenador') {
    $userStats['total_players'] = $playerCount;
    $userStats['recent_injuries'] = $injuryPresenter->getRecentInjuries(3);
} elseif ($_SESSION['user_tipo'] == 'analista') {
    $userStats['total_matches'] = $partidosCount;
    $userStats['performance_data'] = $statsPresenter->getRecentStats(10);
} elseif ($_SESSION['user_tipo'] == 'administrador') {
    $userStats['total_users'] = count($userPresenter->getAllUsers());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FutbolPro</title>
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
                        <li><a href="stats.php">Estadísticas</a></li>
                        <li><a href="injuries.php">Lesiones</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 'entrenador'): ?>
                        <li><a href="players.php">Jugadores</a></li>
                        <li><a href="stats.php">Estadísticas</a></li>
                        <li><a href="injuries.php">Lesiones</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 'analista'): ?>
                        <li><a href="compare_players.php">Comparar</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 'administrador'): ?>
                        <li><a href="players.php">Jugadores</a></li>
                        <li><a href="stats.php">Estadísticas</a></li>
                        <li><a href="compare_players.php">Comparar</a></li>
                        <li><a href="injuries.php">Lesiones</a></li>
                        <li><a href="admin.php">Administración</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Cerrar Sesión (<?php echo $user['nombre']; ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container dashboard">
        <div class="dashboard-header">
            <h1>Dashboard Principal</h1>
            <p>Bienvenido, <?php echo $user['nombre']; ?> (<?php echo $user['tipo']; ?>)</p>
        </div>

        <!-- Mostrar contenido específico según el rol -->
        <?php if ($_SESSION['user_tipo'] == 'jugador' && !empty($userStats)): ?>
        <div class="stats-grid">
            <?php
            $totalGoles = array_sum(array_column($userStats, 'goles'));
            $totalAsistencias = array_sum(array_column($userStats, 'asistencias'));
            $avgGoles = count($userStats) > 0 ? round($totalGoles / count($userStats), 2) : 0;
            ?>
        </div>

        <?php elseif ($_SESSION['user_tipo'] == 'entrenador'): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $userStats['total_players']; ?></div>
                <div class="stat-label">Jugadores a Cargo</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo count($userStats['recent_injuries']); ?></div>
                <div class="stat-label">Lesiones Recientes</div>
            </div>
        </div>

        <?php elseif ($_SESSION['user_tipo'] == 'administrador'): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $userStats['total_users']; ?></div>
                <div class="stat-label">Usuarios Totales</div>
            </div>
        </div>

        <?php endif; ?>

        <!-- Estadísticas generales para todos -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $playerCount; ?></div>
                <div class="stat-label">Jugadores Registrados</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $partidosCount; ?></div>
                <div class="stat-label">Partidos Registrados</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $golesTotal; ?></div>
                <div class="stat-label">Goles Totales</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $asistenciasTotal; ?></div>
                <div class="stat-label">Asistencias Totales</div>
            </div>
        </div>

        <div class="chart-container">
            <h2 class="chart-header">Rendimiento por Mes</h2>
            <canvas id="performanceChart" height="100"></canvas>
        </div>

        <div class="table-container">
            <h2 class="chart-header">Estadísticas Recientes</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Jugador</th>
                        <th>Partido</th>
                        <th>Goles</th>
                        <th>Asistencias</th>
                        <th>Minutos</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentStats)): ?>
                        <?php foreach ($recentStats as $stat): ?>
                        <tr>
                            <td><?php echo $stat['nombre_jugador']; ?></td>
                            <td>VS <?php echo $stat['equipo_rival']; ?></td>
                            <td><?php echo $stat['goles']; ?></td>
                            <td><?php echo $stat['asistencias']; ?></td>
                            <td><?php echo $stat['minutos_jugados']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($stat['partido_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No hay estadísticas registradas</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // Gráfico de rendimiento con datos REALES
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Goles',
                    data: <?php echo json_encode($chartGoles); ?>,
                    borderColor: '#1e3a8a',
                    backgroundColor: 'rgba(30, 58, 138, 0.1)',
                    tension: 0.3,
                    fill: true
                }, {
                    label: 'Asistencias',
                    data: <?php echo json_encode($chartAsistencias); ?>,
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
                            text: 'Mes'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>