<?php
// Control de acceso basado en roles
function checkAccess($allowed_roles) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    if (!in_array($_SESSION['user_tipo'], $allowed_roles)) {
        header("Location: dashboard.php");
        exit();
    }
}

// Definir roles permitidos para cada página
$page_roles = [
    'admin.php' => ['administrador'],
    'players.php' => ['jugador', 'entrenador', 'analista', 'administrador'],
    'stats.php' => ['jugador', 'entrenador', 'analista', 'administrador'],
    'compare_players.php' => ['entrenador', 'analista', 'administrador'],
    'injuries.php' => ['jugador', 'entrenador', 'analista', 'administrador'],
    'dashboard.php' => ['jugador', 'entrenador', 'analista', 'administrador']
];

// Aplicar control de acceso
$current_page = basename($_SERVER['PHP_SELF']);
if (isset($page_roles[$current_page])) {
    checkAccess($page_roles[$current_page]);
}
?>