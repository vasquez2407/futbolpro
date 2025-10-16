<?php
require_once '../models/PlayerModel.php';

class PlayerPresenter {
    private $model;

    public function __construct() {
        $this->model = new PlayerModel();
    }

    public function createPlayer($usuario_id, $nombre, $fecha_nacimiento, $posicion, $equipo) {
        if (empty($nombre) || empty($posicion)) {
            return ["success" => false, "message" => "Nombre y posición son obligatorios"];
        }

        $result = $this->model->createPlayer($usuario_id, $nombre, $fecha_nacimiento, $posicion, $equipo);
        
        if ($result) {
            return ["success" => true, "message" => "Jugador creado correctamente"];
        } else {
            return ["success" => false, "message" => "Error al crear el jugador"];
        }
    }

    public function getPlayersByUser($usuario_id) {
        return $this->model->getPlayersByUser($usuario_id);
    }

    public function getPlayerById($id) {
        return $this->model->getPlayerById($id);
    }

    public function updatePlayer($id, $nombre, $fecha_nacimiento, $posicion, $equipo) {
        if (empty($nombre) || empty($posicion)) {
            return ["success" => false, "message" => "Nombre y posición son obligatorios"];
        }

        $result = $this->model->updatePlayer($id, $nombre, $fecha_nacimiento, $posicion, $equipo);
        
        if ($result) {
            return ["success" => true, "message" => "Jugador actualizado correctamente"];
        } else {
            return ["success" => false, "message" => "Error al actualizar el jugador"];
        }
    }

    public function deletePlayer($id) {
        $result = $this->model->deletePlayer($id);
        
        if ($result) {
            return ["success" => true, "message" => "Jugador eliminado correctamente"];
        } else {
            return ["success" => false, "message" => "Error al eliminar el jugador"];
        }
    }

    public function getPlayersCount() {
        return $this->model->getPlayersCount();
    }

    public function getAllPlayers() {
    return $this->model->getAllPlayers();
}
}
?>