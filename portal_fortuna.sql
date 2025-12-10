-- Crear la base de datos
CREATE DATABASE portal_fortuna;
USE portal_fortuna;

-- Tabla USUARIO (expandida)
CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    ci VARCHAR(20) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    edad INT,
    genero ENUM('masculino', 'femenino', 'otro'),
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- Hasheada
    foto VARCHAR(255),  -- Ruta a la imagen subida
    rol ENUM('usuario', 'administrador') DEFAULT 'usuario',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla ACTIVIDAD
CREATE TABLE actividad (
    id_actividad INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha DATE,
    id_usuario INT,
    foto VARCHAR(255),  -- Foto opcional para la actividad
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE SET NULL
);

-- Tabla NOTICIA
CREATE TABLE noticia (
    id_noticia INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT,
    foto VARCHAR(255),  -- Foto opcional para la noticia
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE SET NULL
);

-- Tabla MENSAJE (para formularios de contacto)
CREATE TABLE mensaje (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    asunto VARCHAR(200) NOT NULL,
    contenido TEXT,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE SET NULL
);

-- Tabla RECUPERACION_CONTRASENA (para códigos de recuperación)
CREATE TABLE recuperacion_contrasena (
    id_recuperacion INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    codigo VARCHAR(6) NOT NULL,
    expiracion TIMESTAMP NOT NULL,
    usado BOOLEAN DEFAULT FALSE
);

-- Tabla CHAT_ANONIMO (mensajes anónimos)
CREATE TABLE chat_anonimo (
    id_chat INT AUTO_INCREMENT PRIMARY KEY,
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para optimización
CREATE INDEX idx_actividad_fecha ON actividad(fecha);
CREATE INDEX idx_noticia_fecha ON noticia(fecha);
CREATE INDEX idx_mensaje_fecha ON mensaje(fecha_envio);
CREATE INDEX idx_recuperacion_email ON recuperacion_contrasena(email);

-- Datos de ejemplo
INSERT INTO usuario (nombre, apellido, ci, telefono, direccion, edad, genero, email, password, rol) VALUES
('Juan', 'Perez', '12345678', '04123456789', 'Calle 1, La Fortuna', 30, 'masculino', 'juan@habitante.com', '$2y$10$examplehashedpassword', 'usuario'),  -- Contraseña: 'password123' (hasheada)
('Admin', 'Fortuna', '87654321', '04129876543', 'Centro', 40, 'femenino', 'admin@fortuna.com', '$2y$10$examplehashedpassword', 'administrador');

INSERT INTO actividad (titulo, descripcion, fecha, id_usuario) VALUES
('Bailo Terapia', 'Clases de baile para adultos mayores', '2023-10-15', 1),
('Celebración del Carnaval', 'Evento festivo comunitario', '2023-11-20', 2);

INSERT INTO noticia (titulo, contenido, id_usuario) VALUES
('Nueva Escuela en Construcción', 'Información sobre el avance de la escuela local.', 2),
('Reunión Comunitaria', 'Convocatoria para reunión el próximo sábado.', 2);

USE portal_fortuna;

-- Nueva tabla para auditoría (logs de acciones de admin)
CREATE TABLE auditoria (
    id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
    accion VARCHAR(255) NOT NULL,
    id_admin INT,
    detalles TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_admin) REFERENCES usuario(id_usuario) ON DELETE SET NULL
);

-- Nueva tabla para rate limiting en chat
CREATE TABLE rate_limiting (
    id_rate INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    ultima_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_ip (ip)
);

-- Índices adicionales
CREATE INDEX idx_auditoria_fecha ON auditoria(fecha);