<?php
require_once '../models/StatsModel.php';

class StatsPresenter {
    private $model;

    public function __construct() {
        $this->model = new StatsModel();
    }

    public function createStats($jugador_id, $partido_date, $goles, $asistencias, $minutos_jugados, $tarjetas_amarillas, $tarjetas_rojas, $equipo_rival) {
        if (empty($jugador_id) || empty($partido_date) || empty($equipo_rival)) {
            return ["success" => false, "message" => "Jugador, fecha y equipo rival son obligatorios"];
        }

        $result = $this->model->createStats($jugador_id, $partido_date, $goles, $asistencias, $minutos_jugados, $tarjetas_amarillas, $tarjetas_rojas, $equipo_rival);
        
        if ($result) {
            return ["success" => true, "message" => "Estadísticas registradas correctamente"];
        } else {
            return ["success" => false, "message" => "Error al registrar las estadísticas"];
        }
    }

    public function getStatsByPlayer($jugador_id) {
        return $this->model->getStatsByPlayer($jugador_id);
    }

    public function getRecentStats($limit = 10) {
        return $this->model->getRecentStats($limit);
    }

    public function getPlayerPerformanceTrend($jugador_id) {
        return $this->model->getPlayerPerformanceTrend($jugador_id);
    }

    public function predictPerformance($jugador_id) {
        $trends = $this->getPlayerPerformanceTrend($jugador_id);
        
        if (empty($trends) || count($trends) < 3) {
            return ["success" => false, "message" => "No hay suficientes datos históricos para predecir"];
        }
        
        // Algoritmo de predicción mejorado con ponderación temporal
        $total_goals = 0;
        $total_assists = 0;
        $total_weight = 0;
        
        // Asignar mayor peso a los meses más recientes
        for ($i = 0; $i < count($trends); $i++) {
            $weight = $i + 1; // Peso incremental (mes más reciente tiene mayor peso)
            $total_goals += $trends[$i]['avg_goals'] * $weight;
            $total_assists += $trends[$i]['avg_assists'] * $weight;
            $total_weight += $weight;
        }
        
        $predicted_goals = round($total_goals / $total_weight, 2);
        $predicted_assists = round($total_assists / $total_weight, 2);
        
        // Ajustar según minutos jugados (asumiendo 90 min/partido)
        $last_month = end($trends);
        $minutes_ratio = $last_month['total_minutes'] / (90 * count($trends));
        
        $predicted_goals = round($predicted_goals * $minutes_ratio, 2);
        $predicted_assists = round($predicted_assists * $minutes_ratio, 2);
        
        return [
            "success" => true,
            "predicted_goals" => max(0, $predicted_goals), // No puede ser negativo
            "predicted_assists" => max(0, $predicted_assists),
            "based_on" => count($trends) . " meses de datos históricos con ponderación temporal"
        ];
    }

    public function comparePlayers($player1_id, $player2_id) {
    $player1_stats = $this->getStatsByPlayer($player1_id);
    $player2_stats = $this->getStatsByPlayer($player2_id);

    if (empty($player1_stats)) {
        return ["success" => false, "message" => "El jugador 1 no tiene estadísticas registradas."];
    }
    if (empty($player2_stats)) {
        return ["success" => false, "message" => "El jugador 2 no tiene estadísticas registradas."];
    }

    // Calcular promedios con protección
    $player1_avg_goals = count($player1_stats) > 0 ? array_sum(array_column($player1_stats, 'goles')) / count($player1_stats) : 0;
    $player2_avg_goals = count($player2_stats) > 0 ? array_sum(array_column($player2_stats, 'goles')) / count($player2_stats) : 0;

    $player1_avg_assists = count($player1_stats) > 0 ? array_sum(array_column($player1_stats, 'asistencias')) / count($player1_stats) : 0;
    $player2_avg_assists = count($player2_stats) > 0 ? array_sum(array_column($player2_stats, 'asistencias')) / count($player2_stats) : 0;

    $player1_avg_minutes = count($player1_stats) > 0 ? array_sum(array_column($player1_stats, 'minutos_jugados')) / count($player1_stats) : 0;
    $player2_avg_minutes = count($player2_stats) > 0 ? array_sum(array_column($player2_stats, 'minutos_jugados')) / count($player2_stats) : 0;

    return [
        "success" => true,
        "player1" => [
            "avg_goals" => round($player1_avg_goals, 2),
            "avg_assists" => round($player1_avg_assists, 2),
            "avg_minutes" => round($player1_avg_minutes, 2),
            "total_matches" => count($player1_stats)
        ],
        "player2" => [
            "avg_goals" => round($player2_avg_goals, 2),
            "avg_assists" => round($player2_avg_assists, 2),
            "avg_minutes" => round($player2_avg_minutes, 2),
            "total_matches" => count($player2_stats)
        ]
    ];
}
}
?>