<?php
require_once '../models/UserModel.php';

class UserPresenter {
    private $model;
    private $db;

    public function __construct() {
        // Primero la conexión a la base de datos
        require_once '../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Luego el modelo con la conexión
        $this->model = new UserModel($this->db);
    }

    public function registerUser($email, $password, $tipo, $nombre) {
        if (empty($email) || empty($password) || empty($tipo) || empty($nombre)) {
            return ["success" => false, "message" => "Todos los campos son obligatorios"];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "El formato del email no es válido"];
        }

        $result = $this->model->register($email, $password, $tipo, $nombre);
        
        if ($result) {
            return ["success" => true, "message" => "Usuario registrado correctamente"];
        } else {
            return ["success" => false, "message" => "Error al registrar el usuario"];
        }
    }

    public function loginUser($email, $password) {
        if (empty($email) || empty($password)) {
            return ["success" => false, "message" => "Email y contraseña son obligatorios"];
        }

        $user = $this->model->login($email, $password);
        
        if ($user) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_tipo'] = $user['tipo'];
            $_SESSION['user_nombre'] = $user['nombre'];
            
            return ["success" => true, "message" => "Login exitoso", "user" => $user];
        } else {
            return ["success" => false, "message" => "Credenciales incorrectas"];
        }
    }

    public function getUserProfile($id) {
        return $this->model->getUserById($id);
    }

    public function getAllUsers() {
        $query = "SELECT id, email, tipo, nombre, fecha_registro FROM usuarios ORDER BY fecha_registro DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUserRole($user_id, $new_role) {
        $allowed_roles = ['jugador', 'entrenador', 'analista', 'administrador'];
        if (!in_array($new_role, $allowed_roles)) {
            return ["success" => false, "message" => "Rol no válido"];
        }

        $query = "UPDATE usuarios SET tipo = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $new_role);
        $stmt->bindParam(2, $user_id);
        
        if ($stmt->execute()) {
            return ["success" => true, "message" => "Rol de usuario actualizado correctamente"];
        } else {
            return ["success" => false, "message" => "Error al actualizar el rol"];
        }
    }

    public function deleteUser($user_id) {
        // Verificar que no sea el usuario actual
        if ($user_id == $_SESSION['user_id']) {
            return ["success" => false, "message" => "No puedes eliminarte a ti mismo"];
        }

        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $user_id);
        
        if ($stmt->execute()) {
            return ["success" => true, "message" => "Usuario eliminado correctamente"];
        } else {
            return ["success" => false, "message" => "Error al eliminar el usuario"];
        }
    }
}
?>