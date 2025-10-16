<?php
class UserModel {
    private $db;
    private $table = 'usuarios';

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($email, $password, $tipo, $nombre) {
        // Verificar si el email ya existe primero
        $checkQuery = "SELECT id FROM " . $this->table . " WHERE email = ?";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(1, $email);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            return false; // Email ya existe
        }
        
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $query = "INSERT INTO " . $this->table . " (email, password, tipo, nombre) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->bindParam(2, $hashed_password);
        $stmt->bindParam(3, $tipo);
        $stmt->bindParam(4, $nombre);
        
        return $stmt->execute();
    }

    public function login($email, $password) {
        $query = "SELECT id, email, password, tipo, nombre FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }

    public function getUserById($id) {
        $query = "SELECT id, email, tipo, nombre FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>