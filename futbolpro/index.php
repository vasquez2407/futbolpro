<?php
session_start();

// Redirigir al dashboard si ya está logueado
if (isset($_SESSION['user_id'])) {
    header("Location: app/views/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-CZ15J2FK84"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-CZ15J2FK84');
</script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutbolPro - Sistema Inteligente de Gestión Futbolística</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .hero {
            background: linear-gradient(rgba(30, 58, 138, 0.8), rgba(30, 58, 138, 0.8)), url('assets/images/futbol-hero.jpg') center/cover;
            color: white;
            padding: 5rem 0;
            text-align: center;
        }
        
        .hero-title {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .features {
            padding: 4rem 0;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .feature-card {
            text-align: center;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            background: white;
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="logo">FutbolPro</div>
                <ul class="nav-links">
                    <li><a href="#features">Características</a></li>
                    <li><a href="app/views/login.php">Iniciar Sesión</a></li>
                    <li><a href="app/views/register.php">Registrarse</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Sistema Inteligente de Gestión Futbolística</h1>
            <p class="hero-subtitle">Registra, analiza y predice el rendimiento de jugadores con tecnología avanzada</p>
            <a href="app/views/register.php" class="btn">Comenzar Ahora</a>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 1rem; color: var(--primary-color);">Características Principales</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 3rem;">FutbolPro ofrece todas las herramientas que necesitas para la gestión y análisis del rendimiento futbolístico</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Estadísticas Detalladas</h3>
                    <p>Registro y seguimiento de goles, asistencias, minutos jugados y más</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <h3>Análisis de Rendimiento</h3>
                    <p>Visualiza la evolución del rendimiento con gráficos interactivos</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔮</div>
                    <h3>Predicciones Inteligentes</h3>
                    <p>Modelos predictivos para estimar el rendimiento futuro</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🏥</div>
                    <h3>Gestión de Lesiones</h3>
                    <p>Registro y seguimiento del historial médico de los jugadores</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Comparación de Jugadores</h3>
                    <p>Analiza y compara el rendimiento entre diferentes jugadores</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h3>Seguridad de Datos</h3>
                    <p>Tus datos están protegidos con los más altos estándares de seguridad</p>
                </div>
            </div>
        </div>
    </section>

    <footer style="background: var(--dark-color); color: white; padding: 2rem 0; text-align: center;">
        <div class="container">
            <p>&copy; 2023 FutbolPro - Unidades Tecnológicas de Santander</p>
        </div>
    </footer>
</body>
</html>