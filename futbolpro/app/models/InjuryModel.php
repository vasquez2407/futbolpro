<?php
require_once '../config/database.php';

class InjuryModel {
    private $db;
    private $table = 'lesiones';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function createInjury($jugador_id, $tipo, $fecha_lesion, $duracion_dias, $gravedad, $tratamiento, $zona_lesion) {
        $query = "INSERT INTO " . $this->table . " (jugador_id, tipo, fecha_lesion, duracion_dias, gravedad, tratamiento, zona_lesion) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $jugador_id);
        $stmt->bindParam(2, $tipo);
        $stmt->bindParam(3, $fecha_lesion);
        $stmt->bindParam(4, $duracion_dias);
        $stmt->bindParam(5, $gravedad);
        $stmt->bindParam(6, $tratamiento);
        $stmt->bindParam(7, $zona_lesion);
        
        return $stmt->execute();
    }

    public function getInjuriesByPlayer($jugador_id) {
        $query = "SELECT l.*, j.nombre as nombre_jugador 
                  FROM " . $this->table . " l 
                  INNER JOIN jugadores j ON l.jugador_id = j.id 
                  WHERE l.jugador_id = ? 
                  ORDER BY l.fecha_lesion DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $jugador_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInjuryById($id) {
        $query = "SELECT l.*, j.nombre as nombre_jugador 
                  FROM " . $this->table . " l 
                  INNER JOIN jugadores j ON l.jugador_id = j.id 
                  WHERE l.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateInjury($id, $tipo, $fecha_lesion, $duracion_dias, $gravedad, $tratamiento, $zona_lesion) {
        $query = "UPDATE " . $this->table . " SET tipo = ?, fecha_lesion = ?, duracion_dias = ?, gravedad = ?, tratamiento = ?, zona_lesion = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $tipo);
        $stmt->bindParam(2, $fecha_lesion);
        $stmt->bindParam(3, $duracion_dias);
        $stmt->bindParam(4, $gravedad);
        $stmt->bindParam(5, $tratamiento);
        $stmt->bindParam(6, $zona_lesion);
        $stmt->bindParam(7, $id);
        
        return $stmt->execute();
    }

    public function deleteInjury($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        
        return $stmt->execute();
    }

    public function getPlayerInjuryHistory($jugador_id) {
        $query = "SELECT 
                    tipo,
                    gravedad,
                    zona_lesion,
                    COUNT(*) as total,
                    AVG(duracion_dias) as avg_duration
                  FROM " . $this->table . " 
                  WHERE jugador_id = ? 
                  GROUP BY tipo, gravedad, zona_lesion
                  ORDER BY total DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $jugador_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInjuryRiskPrediction($jugador_id) {
        $query = "SELECT 
                    zona_lesion,
                    COUNT(*) as total_lesiones,
                    AVG(duracion_dias) as avg_recuperacion,
                    MAX(fecha_lesion) as ultima_lesion,
                    DATEDIFF(CURDATE(), MAX(fecha_lesion)) as dias_desde_ultima
                  FROM " . $this->table . " 
                  WHERE jugador_id = ? 
                  GROUP BY zona_lesion
                  ORDER BY total_lesiones DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $jugador_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentInjuries($limit = 10) {
        $query = "SELECT l.*, j.nombre as nombre_jugador 
                  FROM " . $this->table . " l 
                  INNER JOIN jugadores j ON l.jugador_id = j.id 
                  ORDER BY l.fecha_lesion DESC 
                  LIMIT ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>