<?php
require_once '../models/InjuryModel.php';

class InjuryPresenter {
    private $model;

    public function __construct() {
        $this->model = new InjuryModel();
    }

    public function createInjury($jugador_id, $tipo, $fecha_lesion, $duracion_dias, $gravedad, $tratamiento, $zona_lesion) {
        if (empty($jugador_id) || empty($tipo) || empty($fecha_lesion) || empty($gravedad)) {
            return ["success" => false, "message" => "Jugador, tipo, fecha y gravedad son obligatorios"];
        }

        $result = $this->model->createInjury($jugador_id, $tipo, $fecha_lesion, $duracion_dias, $gravedad, $tratamiento, $zona_lesion);
        
        if ($result) {
            return ["success" => true, "message" => "Lesión registrada correctamente"];
        } else {
            return ["success" => false, "message" => "Error al registrar la lesión"];
        }
    }

    public function getInjuriesByPlayer($jugador_id) {
        return $this->model->getInjuriesByPlayer($jugador_id);
    }

    public function getInjuryById($id) {
        return $this->model->getInjuryById($id);
    }

    public function updateInjury($id, $tipo, $fecha_lesion, $duracion_dias, $gravedad, $tratamiento, $zona_lesion) {
        if (empty($tipo) || empty($fecha_lesion) || empty($gravedad)) {
            return ["success" => false, "message" => "Tipo, fecha y gravedad son obligatorios"];
        }

        $result = $this->model->updateInjury($id, $tipo, $fecha_lesion, $duracion_dias, $gravedad, $tratamiento, $zona_lesion);
        
        if ($result) {
            return ["success" => true, "message" => "Lesión actualizada correctamente"];
        } else {
            return ["success" => false, "message" => "Error al actualizar la lesión"];
        }
    }

    public function deleteInjury($id) {
        $result = $this->model->deleteInjury($id);
        
        if ($result) {
            return ["success" => true, "message" => "Lesión eliminada correctamente"];
        } else {
            return ["success" => false, "message" => "Error al eliminar la lesión"];
        }
    }

    public function getPlayerInjuryHistory($jugador_id) {
        return $this->model->getPlayerInjuryHistory($jugador_id);
    }

    public function getInjuryRiskPrediction($jugador_id) {
        $injuryData = $this->model->getInjuryRiskPrediction($jugador_id);
        
        if (empty($injuryData)) {
            return ["success" => false, "message" => "No hay historial de lesiones para analizar"];
        }

        $riskAnalysis = [];
        $totalInjuries = 0;

        foreach ($injuryData as $injury) {
            $totalInjuries += $injury['total_lesiones'];
        }

        foreach ($injuryData as $injury) {
            $recurrenceRate = ($injury['total_lesiones'] / $totalInjuries) * 100;
            
            // Calcular riesgo basado en recurrencia, tiempo desde última lesión y gravedad promedio
            $recurrenceRisk = min(100, $recurrenceRate * 20);
            $timeRisk = $injury['dias_desde_ultima'] < 90 ? (90 - $injury['dias_desde_ultima']) / 90 * 50 : 0;
            
            $totalRisk = min(100, $recurrenceRisk + $timeRisk);
            
            $riskLevel = 'Bajo';
            if ($totalRisk > 70) $riskLevel = 'Alto';
            elseif ($totalRisk > 40) $riskLevel = 'Moderado';
            
            $riskAnalysis[] = [
                'zona_lesion' => $injury['zona_lesion'],
                'total_lesiones' => $injury['total_lesiones'],
                'avg_recuperacion' => round($injury['avg_recuperacion'], 1),
                'dias_desde_ultima' => $injury['dias_desde_ultima'],
                'recurrence_rate' => round($recurrenceRate, 1),
                'risk_score' => round($totalRisk),
                'risk_level' => $riskLevel,
                'recommendation' => $this->generateRecommendation($riskLevel, $injury['zona_lesion'])
            ];
        }

        return [
    "success" => true,
    "data" => $riskAnalysis,   
    "total_injuries" => $totalInjuries
];

    }

    private function generateRecommendation($riskLevel, $zonaLesion) {
        $recommendations = [
            'Alto' => [
                "Evaluación médica inmediata requerida",
                "Evitar carga en $zonaLesion",
                "Sesiones de fisioterapia intensiva",
                "Considerar estudios por imágenes"
            ],
            'Moderado' => [
                "Monitorización cuidadosa de síntomas",
                "Ejercicios de fortalecimiento preventivo",
                "Estiramientos específicos para $zonaLesion",
                "Adecuado calentamiento pre-entreno"
            ],
            'Bajo' => [
                "Mantenimiento de rutina de ejercicios",
                "Estiramientos regulares",
                "Hidratación adecuada",
                "Descanso apropiado entre sesiones"
            ]
        ];

        return $recommendations[$riskLevel][array_rand($recommendations[$riskLevel])];
    }

    public function getRecentInjuries($limit = 10) {
        return $this->model->getRecentInjuries($limit);
    }
}
?>