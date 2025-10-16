<?php
require_once '../config/database.php';

class StatsModel {
    private $db;
    private $table = 'estadisticas';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function createStats($jugador_id, $partido_date, $goles, $asistencias, $minutos_jugados, $tarjetas_amarillas, $tarjetas_rojas, $equipo_rival) {
        $query = "INSERT INTO " . $this->table . " (jugador_id, partido_date, goles, asistencias, minutos_jugados, tarjetas_amarillas, tarjetas_rojas, equipo_rival) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $jugador_id);
        $stmt->bindParam(2, $partido_date);
        $stmt->bindParam(3, $goles);
        $stmt->bindParam(4, $asistencias);
        $stmt->bindParam(5, $minutos_jugados);
        $stmt->bindParam(6, $tarjetas_amarillas);
        $stmt->bindParam(7, $tarjetas_rojas);
        $stmt->bindParam(8, $equipo_rival);
        
        return $stmt->execute();
    }

    public function getStatsByPlayer($jugador_id) {
        $query = "SELECT e.*, j.nombre as nombre_jugador 
                  FROM " . $this->table . " e 
                  INNER JOIN jugadores j ON e.jugador_id = j.id 
                  WHERE e.jugador_id = ? 
                  ORDER BY e.partido_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $jugador_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentStats($limit = 10) {
        $query = "SELECT e.*, j.nombre as nombre_jugador 
                  FROM " . $this->table . " e 
                  INNER JOIN jugadores j ON e.jugador_id = j.id 
                  ORDER BY e.partido_date DESC 
                  LIMIT ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPlayerPerformanceTrend($jugador_id) {
        $query = "SELECT 
                    YEAR(partido_date) as year, 
                    MONTH(partido_date) as month, 
                    AVG(goles) as avg_goals, 
                    AVG(asistencias) as avg_assists,
                    SUM(minutos_jugados) as total_minutes
                  FROM " . $this->table . " 
                  WHERE jugador_id = ? 
                  GROUP BY YEAR(partido_date), MONTH(partido_date)
                  ORDER BY year, month";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $jugador_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>