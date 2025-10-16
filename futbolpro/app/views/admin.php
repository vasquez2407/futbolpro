<?php
session_start();
require_once 'access_control.php';

// Verificar si es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

require_once '../presenters/UserPresenter.php';

$userPresenter = new UserPresenter();
$message = '';

// Obtener todos los usuarios
$users = $userPresenter->getAllUsers();

// Cambiar rol de usuario
if (isset($_GET['change_role'])) {
    $result = $userPresenter->updateUserRole($_GET['user_id'], $_GET['new_role']);
    $message = $result['message'];
    header("Location: admin.php");
    exit();
}

// Eliminar usuario
if (isset($_GET['delete_user'])) {
    $result = $userPresenter->deleteUser($_GET['user_id']);
    $message = $result['message'];
    header("Location: admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - FutbolPro</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="logo">FutbolPro</div>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="players.php">Jugadores</a></li>
                        <li><a href="stats.php" class="active">Estadísticas</a></li>
                        <li><a href="compare_players.php" class="active">Comparar</a></li>
                        <li><a href="injuries.php">Lesiones</a></li>
                    <li><a href="admin.php" class="active">Administración</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container dashboard">
        <div class="dashboard-header">
            <h1>Panel de Administración</h1>
            <p>Gestión de usuarios y permisos del sistema</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-error' : 'alert-success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Gestión de Usuarios</h2>
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Tipo de Usuario</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['nombre']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td>
                                    <form method="GET" action="admin.php" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <select name="new_role" onchange="this.form.submit()" style="padding: 0.25rem; font-size: 0.9rem;">
                                            <option value="jugador" <?php echo $user['tipo'] == 'jugador' ? 'selected' : ''; ?>>Jugador</option>
                                            <option value="entrenador" <?php echo $user['tipo'] == 'entrenador' ? 'selected' : ''; ?>>Entrenador</option>
                                            <option value="analista" <?php echo $user['tipo'] == 'analista' ? 'selected' : ''; ?>>Analista</option>
                                            <option value="administrador" <?php echo $user['tipo'] == 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                                        </select>
                                        <input type="hidden" name="change_role" value="1">
                                    </form>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['fecha_registro'])); ?></td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="admin.php?delete_user=1&user_id=<?php echo $user['id']; ?>" class="btn btn-action btn-delete" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">Eliminar</a>
                                    <?php else: ?>
                                    <span style="color: #6b7280; font-size: 0.9rem;">Usuario actual</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No hay usuarios registrados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo count(array_filter($users, function($u) { return $u['tipo'] == 'jugador'; })); ?></div>
                <div class="stat-label">Jugadores</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count(array_filter($users, function($u) { return $u['tipo'] == 'entrenador'; })); ?></div>
                <div class="stat-label">Entrenadores</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count(array_filter($users, function($u) { return $u['tipo'] == 'analista'; })); ?></div>
                <div class="stat-label">Analistas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count(array_filter($users, function($u) { return $u['tipo'] == 'administrador'; })); ?></div>
                <div class="stat-label">Administradores</div>
            </div>
        </div>
    </main>
</body>
</html>