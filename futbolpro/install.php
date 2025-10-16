<?php
require_once 'app/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Leer el script SQL
    $sql = file_get_contents('database/init.sql');
    $db->exec($sql);

    // Verificar si ya existe un administrador
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE tipo = 'administrador'");
    $stmt->execute();
    $exists = $stmt->fetchColumn();

    if ($exists == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO usuarios (email, password, tipo, nombre) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin@futbolpro.com', $hashedPassword, 'administrador', 'Administrador']);
    }
    
    echo "✅ Sistema instalado correctamente.<br>";
    echo "Se ha creado un usuario administrador por defecto:<br>";
    echo "👉 Usuario: <b>admin@futbolpro.com</b><br>";
    echo "👉 Contraseña: <b>admin123</b><br>";
    echo "<a href='app/views/login.php'>Iniciar sesión</a>";
    
} catch (PDOException $e) {
    echo "Error durante la instalación: " . $e->getMessage();
}
?>
