<?php
session_start();
require_once 'access_control.php';

require_once '../presenters/PlayerPresenter.php';
require_once '../presenters/StatsPresenter.php';

$playerPresenter = new PlayerPresenter();
$statsPresenter = new StatsPresenter();
$message = '';

// Obtener todos los jugadores del sistema
$players = $playerPresenter->getAllPlayers();


// Comparar jugadores (comparación simple de 2 jugadores)
$comparison = null;
if (isset($_GET['player1_id']) && isset($_GET['player2_id'])) {
    $comparison = $statsPresenter->comparePlayers($_GET['player1_id'], $_GET['player2_id']);
    if (!$comparison['success']) {
        $message = $comparison['message'];
    }
    
    // Obtener información de los jugadores
    $player1 = $playerPresenter->getPlayerById($_GET['player1_id']);
    $player2 = $playerPresenter->getPlayerById($_GET['player2_id']);
}

// --- NUEVO CÓDIGO: Comparación múltiple de jugadores --- //
$comparisonResults = [];
if (isset($_GET['player_ids']) && is_array($_GET['player_ids'])) {
    $playerIds = array_filter($_GET['player_ids']);
    if (count($playerIds) >= 2 && count($playerIds) <= 4) {
        foreach ($playerIds as $playerId) {
            $playerStats = $statsPresenter->getStatsByPlayer($playerId);
            if (!empty($playerStats)) {
                $player = $playerPresenter->getPlayerById($playerId);
                $totalGoals = array_sum(array_column($playerStats, 'goles'));
                $totalAssists = array_sum(array_column($playerStats, 'asistencias'));
                $totalMatches = count($playerStats);
                
                $comparisonResults[] = [
                    'player' => $player,
                    'avg_goals' => $totalMatches > 0 ? round($totalGoals / $totalMatches, 2) : 0,
                    'avg_assists' => $totalMatches > 0 ? round($totalAssists / $totalMatches, 2) : 0,
                    'total_matches' => $totalMatches,
                    'total_goals' => $totalGoals,
                    'total_assists' => $totalAssists
                ];
            }
        }
    }
}
// --- FIN DEL NUEVO CÓDIGO --- //

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($_SESSION['user_tipo'] !== 'entrenador') ? 'Comparar Jugadores - FutbolPro' : 'FutbolPro'; ?></title>
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
                    <?php if ($_SESSION['user_tipo'] == 'administrador'): ?>
                        <li><a href="players.php">Jugadores</a></li>
                        <li><a href="stats.php" class="active">Estadísticas</a></li>
                        <li><a href="compare_players.php">Comparar</a></li>
                        <li><a href="injuries.php">Lesiones</a></li>
                        <li><a href="admin.php">Administración</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 'entrenador'): ?>
                        <li><a href="players.php">Jugadores</a></li>
                        <li><a href="stats.php">Estadísticas</a></li>
                        <li><a href="injuries.php">Lesiones</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 'analista'): ?>
                        <li><a href="compare_players.php">Comparar</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container dashboard">
        <?php if ($_SESSION['user_tipo'] !== 'entrenador'): ?>
            <div class="dashboard-header">
                <h1>Comparación de Jugadores</h1>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-error"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Formulario de comparación simple (2 jugadores) -->
            <div class="card">
    <div class="card-header">
        <h2 class="card-title">Comparación Simple (2 Jugadores)</h2>
    </div>
    <form method="GET" action="compare_players.php" class="form-inline">
        <div class="form-group">
            <label class="form-label" for="player1_id">Jugador 1</label>
            <select class="form-input" id="player1_id" name="player1_id" required>
                <option value="">Seleccione un jugador</option>
                <?php foreach ($players as $player): ?>
                <option value="<?php echo $player['id']; ?>" 
                    <?php echo isset($_GET['player1_id']) && $_GET['player1_id'] == $player['id'] ? 'selected' : ''; ?>>
                    <?php echo $player['nombre']; ?> (<?php echo $player['posicion']; ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="player2_id">Jugador 2</label>
            <select class="form-input" id="player2_id" name="player2_id" required>
                <option value="">Seleccione un jugador</option>
                <?php foreach ($players as $player): ?>
                <option value="<?php echo $player['id']; ?>" 
                    <?php echo isset($_GET['player2_id']) && $_GET['player2_id'] == $player['id'] ? 'selected' : ''; ?>>
                    <?php echo $player['nombre']; ?> (<?php echo $player['posicion']; ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn">Comparar</button>
    </form>
</div>


            <!-- NUEVO: Formulario para comparación múltiple -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Comparación Múltiple de Jugadores (2-4 jugadores)</h2>
                </div>
                <form method="GET" action="compare_players.php">
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1rem;">
                        <?php for ($i = 0; $i < 4; $i++): ?>
                        <div class="form-group">
                            <label class="form-label">Jugador <?php echo $i + 1; ?></label>
                            <select class="form-input" name="player_ids[]">
                                <option value="">Seleccione jugador</option>
                                <?php foreach ($players as $player): ?>
                                <option value="<?php echo $player['id']; ?>" <?php echo isset($_GET['player_ids'][$i]) && $_GET['player_ids'][$i] == $player['id'] ? 'selected' : ''; ?>>
                                    <?php echo $player['nombre']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <button type="submit" class="btn">Comparar Jugadores</button>
                </form>
            </div>

            <!-- Resultados de comparación simple (2 jugadores) -->
<?php if ($comparison && isset($player1) && isset($player2)): ?>
    <?php if ($comparison['success']): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Resultados de la Comparación Simple</h2>
                <span class="badge badge-primary">Análisis Estadístico</span>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; text-align: center;">
                <div>
                    <h3><?php echo $player1['nombre']; ?></h3>
                    <p><?php echo $player1['posicion']; ?></p>
                </div>
                <div><h3>VS</h3></div>
                <div>
                    <h3><?php echo $player2['nombre']; ?></h3>
                    <p><?php echo $player2['posicion']; ?></p>
                </div>
            </div>

            <div class="chart-container">
                <canvas id="comparisonChart" height="100"></canvas>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Estadística</th>
                            <th><?php echo $player1['nombre']; ?></th>
                            <th><?php echo $player2['nombre']; ?></th>
                            <th>Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Partidos jugados</td>
                            <td><?php echo $comparison['player1']['total_matches']; ?></td>
                            <td><?php echo $comparison['player2']['total_matches']; ?></td>
                            <td><?php echo $comparison['player1']['total_matches'] - $comparison['player2']['total_matches']; ?></td>
                        </tr>
                        <tr>
                            <td>Goles por partido</td>
                            <td><?php echo $comparison['player1']['avg_goals']; ?></td>
                            <td><?php echo $comparison['player2']['avg_goals']; ?></td>
                            <td><?php echo round($comparison['player1']['avg_goals'] - $comparison['player2']['avg_goals'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Asistencias por partido</td>
                            <td><?php echo $comparison['player1']['avg_assists']; ?></td>
                            <td><?php echo $comparison['player2']['avg_assists']; ?></td>
                            <td><?php echo round($comparison['player1']['avg_assists'] - $comparison['player2']['avg_assists'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Minutos por partido</td>
                            <td><?php echo $comparison['player1']['avg_minutes']; ?></td>
                            <td><?php echo $comparison['player2']['avg_minutes']; ?></td>
                            <td><?php echo round($comparison['player1']['avg_minutes'] - $comparison['player2']['avg_minutes'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-error">
            <?php echo $comparison['message']; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>


            <!-- NUEVO: Resultados de comparación múltiple -->
            <?php if (!empty($comparisonResults)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Resultados de Comparación Múltiple</h2>
                </div>
                <div class="chart-container">
                    <canvas id="multiComparisonChart" height="100"></canvas>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Jugador</th>
                                <th>Partidos</th>
                                <th>Goles Totales</th>
                                <th>Asistencias Totales</th>
                                <th>Goles/Partido</th>
                                <th>Asistencias/Partido</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comparisonResults as $result): ?>
                            <tr>
                                <td><?php echo $result['player']['nombre']; ?></td>
                                <td><?php echo $result['total_matches']; ?></td>
                                <td><?php echo $result['total_goals']; ?></td>
                                <td><?php echo $result['total_assists']; ?></td>
                                <td><?php echo $result['avg_goals']; ?></td>
                                <td><?php echo $result['avg_assists']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <script>
                // Gráfico de comparación múltiple
                const multiCtx = document.getElementById('multiComparisonChart').getContext('2d');
                const multiComparisonChart = new Chart(multiCtx, {
                    type: 'radar',
                    data: {
                        labels: ['Goles por Partido', 'Asistencias por Partido', 'Partidos Jugados', 'Contribución Ofensiva'],
                        datasets: [
                            <?php foreach ($comparisonResults as $index => $result): ?>
                            {
                                label: '<?php echo $result['player']['nombre']; ?>',
                                data: [
                                    <?php echo $result['avg_goals'] * 10; ?>,
                                    <?php echo $result['avg_assists'] * 10; ?>,
                                    <?php echo min($result['total_matches'], 50); ?>,
                                    <?php echo ($result['avg_goals'] + $result['avg_assists']) * 5; ?>
                                ],
                                backgroundColor: `rgba(${<?php echo $index * 60; ?>}, 100, 200, 0.2)`,
                                borderColor: `rgba(${<?php echo $index * 60; ?>}, 100, 200, 1)`,
                                pointBackgroundColor: `rgba(${<?php echo $index * 60; ?>}, 100, 200, 1)`,
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: `rgba(${<?php echo $index * 60; ?>}, 100, 200, 1)`
                            },
                            <?php endforeach; ?>
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Comparación de Rendimiento'
                            },
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            r: {
                                angleLines: {
                                    display: true
                                },
                                suggestedMin: 0,
                                suggestedMax: 50
                            }
                        }
                    }
                });
            </script>
            <?php endif; ?>

    </main>

    <?php if ($comparison && $comparison['success']): ?>
    <script>
        // Gráfico de comparación simple
        const ctx = document.getElementById('comparisonChart').getContext('2d');
        const comparisonChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Goles por partido', 'Asistencias por partido', 'Minutos por partido'],
                datasets: [{
                    label: '<?php echo $player1['nombre']; ?>',
                    data: [
                        <?php echo $comparison['player1']['avg_goals']; ?>,
                        <?php echo $comparison['player1']['avg_assists']; ?>,
                        <?php echo $comparison['player1']['avg_minutes']; ?>
                    ],
                    backgroundColor: 'rgba(30, 58, 138, 0.7)'
                }, {
                    label: '<?php echo $player2['nombre']; ?>',
                    data: [
                        <?php echo $comparison['player2']['avg_goals']; ?>,
                        <?php echo $comparison['player2']['avg_assists']; ?>,
                        <?php echo $comparison['player2']['avg_minutes']; ?>
                    ],
                    backgroundColor: 'rgba(14, 165, 233, 0.7)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Comparación de Rendimiento'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>