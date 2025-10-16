<?php
// Forzar HTTPS en producción
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'on' && getenv('ENVIRONMENT') === 'production') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Headers de seguridad
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Política de seguridad de contenido
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data:;");

// Prevenir el almacenamiento en caché para páginas sensibles
$sensitive_pages = ['login.php', 'register.php', 'admin.php'];
if (in_array(basename($_SERVER['PHP_SELF']), $sensitive_pages)) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

// Validación de entrada
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

// Aplicar sanitización a los inputs
$_GET = sanitizeInput($_GET);
$_POST = sanitizeInput($_POST);
$_REQUEST = sanitizeInput($_REQUEST);
?>