-- Crear base de datos
CREATE DATABASE IF NOT EXISTS futbolpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE futbolpro;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tipo ENUM('jugador', 'entrenador', 'analista', 'administrador') NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de jugadores
CREATE TABLE jugadores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    nombre VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE,
    posicion ENUM('portero', 'defensa', 'centrocampista', 'delantero'),
    equipo VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de estadísticas
CREATE TABLE estadisticas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jugador_id INT NOT NULL,
    partido_date DATE NOT NULL,
    goles INT DEFAULT 0,
    asistencias INT DEFAULT 0,
    minutos_jugados INT DEFAULT 0,
    tarjetas_amarillas INT DEFAULT 0,
    tarjetas_rojas INT DEFAULT 0,
    equipo_rival VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jugador_id) REFERENCES jugadores(id) ON DELETE CASCADE
);

-- Tabla de lesiones
CREATE TABLE lesiones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jugador_id INT NOT NULL,
    tipo VARCHAR(100) NOT NULL,
    fecha_lesion DATE NOT NULL,
    duracion_dias INT,
    gravedad ENUM('Leve', 'Moderada', 'Grave') NOT NULL,
    tratamiento TEXT,
    zona_lesion VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jugador_id) REFERENCES jugadores(id) ON DELETE CASCADE
);

SELECT '✅ Base de datos y tablas creadas exitosamente!' as Status;

