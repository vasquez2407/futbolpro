<?php
session_start();
require_once 'access_control.php';

// Redirigir si no está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../presenters/PlayerPresenter.php';
require_once '../presenters/InjuryPresenter.php';

$playerPresenter = new PlayerPresenter();
$injuryPresenter = new InjuryPresenter();
$message = '';

$players = $playerPresenter->getAllPlayers();


// Registrar lesión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_injury'])) {
    $result = $injuryPresenter->createInjury(
        $_POST['jugador_id'],
        $_POST['tipo'],
        $_POST['fecha_lesion'],
        $_POST['duracion_dias'] ?? null,
        $_POST['gravedad'],
        $_POST['tratamiento'] ?? '',
        $_POST['zona_lesion']
    );
    
    $message = $result['message'];
    if ($result['success']) {
        header("Location: injuries.php");
        exit();
    }
}

// Eliminar lesión
if (isset($_GET['delete_id'])) {
    $result = $injuryPresenter->deleteInjury($_GET['delete_id']);
    $message = $result['message'];
    header("Location: injuries.php");
    exit();
}

// Obtener lesiones si se seleccionó un jugador
$playerInjuries = [];
$injuryHistory = [];
$riskPrediction = null;
$selectedPlayer = null;

if (isset($_GET['player_id']) && !empty($_GET['player_id'])) {
    $playerInjuries = $injuryPresenter->getInjuriesByPlayer($_GET['player_id']);
    $injuryHistory = $injuryPresenter->getPlayerInjuryHistory($_GET['player_id']);
    $riskPrediction = $injuryPresenter->getInjuryRiskPrediction($_GET['player_id']);
    $selectedPlayer = $playerPresenter->getPlayerById($_GET['player_id']);
}

// Obtener lesiones recientes
$recentInjuries = $injuryPresenter->getRecentInjuries(5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Lesiones - FutbolPro</title>
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
                        <li><a href="injuries.php" class="active">Lesiones</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 'entrenador'): ?>
                        <li><a href="players.php">Jugadores</a></li>
                        <li><a href="stats.php">Estadísticas</a></li>
                        <li><a href="injuries.php" class="active">Lesiones</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 'analista'): ?>
                        <li><a href="stats.php">Estadísticas</a></li>
                        <li><a href="compare_players.php">Comparar</a></li>
                        <li><a href="injuries.php" class="active">Lesiones</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 'administrador'): ?>
                        <li><a href="players.php">Jugadores</a></li>
                        <li><a href="stats.php">Estadísticas</a></li>
                        <li><a href="compare_players.php">Comparar</a></li>
                        <li><a href="injuries.php" class="active">Lesiones</a></li>
                        <li><a href="admin.php">Administración</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container dashboard">
        <div class="dashboard-header">
            <h1>Gestión de Lesiones</h1>
            <?php if ($_SESSION['user_tipo'] == 'jugador' || $_SESSION['user_tipo'] == 'administrador'): ?>
    <button id="showInjuryFormBtn" class="btn">+ Registrar Lesión</button>
<?php endif; ?>

        </div>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-error' : 'alert-success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Lesiones Recientes -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Lesiones Recientes</h2>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Jugador</th>
                            <th>Tipo</th>
                            <th>Zona</th>
                            <th>Gravedad</th>
                            <th>Fecha</th>
                            <th>Duración (días)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentInjuries)): ?>
                            <?php foreach ($recentInjuries as $injury): ?>
                            <tr>
                                <td><?php echo $injury['nombre_jugador']; ?></td>
                                <td><?php echo $injury['tipo']; ?></td>
                                <td><?php echo $injury['zona_lesion']; ?></td>
                                <td>
                                    <span class="badge 
                                        <?php echo $injury['gravedad'] == 'Leve' ? 'badge-success' : 
                                              ($injury['gravedad'] == 'Moderada' ? 'badge-warning' : 'badge-primary'); ?>">
                                        <?php echo $injury['gravedad']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($injury['fecha_lesion'])); ?></td>
                                <td><?php echo $injury['duracion_dias'] ?? 'N/A'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No hay lesiones registradas</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Seleccionar Jugador para Análisis -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Analizar Historial de Lesiones</h2>
            </div>
            <form method="GET" action="injuries.php" class="form-inline">
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

              <!-- Formulario para registrar lesión SIEMPRE disponible tras el botón, fuera del condicional de $selectedPlayer -->
        <?php if ($_SESSION['user_tipo'] == 'jugador' || $_SESSION['user_tipo'] == 'administrador'): ?>
    <div id="injuryFormContainer" class="card" style="margin-top: 2rem; display: none;">
            <div class="card-header">
                <h2 class="form-title">Registrar Nueva Lesión</h2>
            </div>
            <div class="form-container" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <form method="POST" action="">
                    <input type="hidden" name="add_injury" value="1">
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
                        <label class="form-label" for="tipo">Tipo de Lesión *</label>
                        <select class="form-input" id="tipo" name="tipo" required>
                            <option value="">Seleccione el tipo</option>
                            <option value="Muscular">Muscular</option>
                            <option value="Articular">Articular</option>
                            <option value="Ósea">Ósea</option>
                            <option value="Ligamentosa">Ligamentosa</option>
                            <option value="Tendinosa">Tendinosa</option>
                            <option value="Contusión">Contusión</option>
                            <option value="Fractura">Fractura</option>
                            <option value="Esguince">Esguince</option>
                            <option value="Distensión">Distensión</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="zona_lesion">Zona del Cuerpo *</label>
                        <select class="form-input" id="zona_lesion" name="zona_lesion" required>
                            <option value="">Seleccione la zona</option>
                            <option value="Pierna">Pierna</option>
                            <option value="Rodilla">Rodilla</option>
                            <option value="Tobillo">Tobillo</option>
                            <option value="Muslo">Muslo</option>
                            <option value="Pantorrilla">Pantorrilla</option>
                            <option value="Cadera">Cadera</option>
                            <option value="Espalda">Espalda</option>
                            <option value="Hombro">Hombro</option>
                            <option value="Brazo">Brazo</option>
                            <option value="Mano">Mano</option>
                            <option value="Cabeza">Cabeza</option>
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="fecha_lesion">Fecha de Lesión *</label>
                            <input class="form-input" type="date" id="fecha_lesion" name="fecha_lesion" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="duracion_dias">Duración (días)</label>
                            <input class="form-input" type="number" id="duracion_dias" name="duracion_dias" min="1" placeholder="Días de recuperación">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="gravedad">Gravedad *</label>
                        <select class="form-input" id="gravedad" name="gravedad" required>
                            <option value="">Seleccione la gravedad</option>
                            <option value="Leve">Leve (1-7 días)</option>
                            <option value="Moderada">Moderada (1-4 semanas)</option>
                            <option value="Grave">Grave (1+ meses)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tratamiento">Tratamiento/Rehabilitación</label>
                        <textarea class="form-input" id="tratamiento" name="tratamiento" rows="3" placeholder="Describa el tratamiento aplicado..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-block">Registrar Lesión</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($selectedPlayer): ?>
    <!-- Predicción de Riesgo de Lesiones -->
    <?php if ($riskPrediction && !empty($riskPrediction['data'])): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                Predicción de Riesgo de Lesiones - <?php echo htmlspecialchars($selectedPlayer['nombre']); ?>
                (Total de lesiones: <?php echo isset($riskPrediction['total_injuries']) ? (int)$riskPrediction['total_injuries'] : 0; ?>)
            </h2>
            <span class="badge badge-primary">Sistema Inteligente</span>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Zona del Cuerpo</th>
                        <th>Probabilidad de Lesión</th>
                        <th>Recomendación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riskPrediction['data'] as $info): ?>
                    <tr>
                        <td><?php echo ucfirst(htmlspecialchars($info['zona_lesion'])); ?></td>
                        <td>
                            <span class="badge 
                                <?php echo ($info['risk_level'] === 'Alto') ? 'badge-primary' : 
                                           (($info['risk_level'] === 'Moderado') ? 'badge-warning' : 'badge-success'); ?>">
                                <?php echo htmlspecialchars($info['risk_level']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($info['recommendation']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php else: ?>
    <!-- No hay análisis disponible para este jugador -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Predicción de Riesgo de Lesiones - <?php echo htmlspecialchars($selectedPlayer['nombre']); ?></h2>
        </div>
        <div class="card-body" style="padding:1rem;">
            <p>No hay historial de lesiones suficiente para generar un análisis o no se pudo procesar el análisis.</p>
            <p>Si el jugador tiene lesiones registradas, intenta <strong>volver a analizar</strong> o comprueba que las estadísticas/lesiones están correctamente asociadas al jugador.</p>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>


    </main>

    <?php if ($selectedPlayer && !empty($injuryHistory)): ?>
    <script>
        // Preparar datos para el gráfico de lesiones
        const injuryTypes = [<?php echo implode(',', array_map(function($item) { return "'" . $item['tipo'] . "'"; }, $injuryHistory)); ?>];
        const injuryCounts = [<?php echo implode(',', array_column($injuryHistory, 'total')); ?>];
        const injuryColors = ['#1e3a8a', '#0ea5e9', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899'];
        
        // Gráfico de lesiones
        const ctx = document.getElementById('injuryChart').getContext('2d');
        const injuryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: injuryTypes,
                datasets: [{
                    data: injuryCounts,
                    backgroundColor: injuryColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribución de Tipos de Lesión'
                    },
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    </script>
    <?php endif; ?>

    <script>
        // Mostrar/ocultar el formulario de registrar lesión
        const showInjuryFormBtn = document.getElementById('showInjuryFormBtn');
        const injuryFormContainer = document.getElementById('injuryFormContainer');
        if (showInjuryFormBtn && injuryFormContainer) {
            showInjuryFormBtn.addEventListener('click', function() {
                if (injuryFormContainer.style.display === 'none' || injuryFormContainer.style.display === '') {
                    injuryFormContainer.style.display = 'block';
                    injuryFormContainer.scrollIntoView({behavior: 'smooth'});
                } else {
                    injuryFormContainer.style.display = 'none';
                }
            });
        }

        // Actualizar opciones de gravedad según la selección
        document.getElementById('gravedad').addEventListener('change', function() {
            const duracionInput = document.getElementById('duracion_dias');
            switch(this.value) {
                case 'Leve':
                    duracionInput.placeholder = '1-7 días';
                    break;
                case 'Moderada':
                    duracionInput.placeholder = '8-28 días';
                    break;
                case 'Grave':
                    duracionInput.placeholder = '29+ días';
                    break;
                default:
                    duracionInput.placeholder = 'Días de recuperación';
            }
        });
    </script>
</body>
</html>