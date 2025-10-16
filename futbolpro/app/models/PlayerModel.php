<?php
require_once '../config/database.php';

class PlayerModel {
    private $db;
    private $table = 'jugadores';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function createPlayer($usuario_id, $nombre, $fecha_nacimiento, $posicion, $equipo) {
        $query = "INSERT INTO " . $this->table . " (usuario_id, nombre, fecha_nacimiento, posicion, equipo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $usuario_id);
        $stmt->bindParam(2, $nombre);
        $stmt->bindParam(3, $fecha_nacimiento);
        $stmt->bindParam(4, $posicion);
        $stmt->bindParam(5, $equipo);
        
        return $stmt->execute();
    }

    public function getPlayersByUser($usuario_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE usuario_id = ? ORDER BY nombre";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $usuario_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPlayerById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePlayer($id, $nombre, $fecha_nacimiento, $posicion, $equipo) {
        $query = "UPDATE " . $this->table . " SET nombre = ?, fecha_nacimiento = ?, posicion = ?, equipo = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $nombre);
        $stmt->bindParam(2, $fecha_nacimiento);
        $stmt->bindParam(3, $posicion);
        $stmt->bindParam(4, $equipo);
        $stmt->bindParam(5, $id);
        
        return $stmt->execute();
    }

    public function deletePlayer($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        
        return $stmt->execute();
    }

    public function getPlayersCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table;
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function getAllPlayers() {
    $stmt = $this->db->query("SELECT * FROM jugadores ORDER BY nombre ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


}
?>