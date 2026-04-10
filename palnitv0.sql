-- ========================================
-- CREACIÓN DE BASE DE DATOS
-- ========================================
CREATE DATABASE IF NOT EXISTS planit;

USE planit;

-- ========================================
-- TABLA ROLES
-- ========================================
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- TABLA USUARIOS
-- ========================================
CREATE TABLE usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id BIGINT UNSIGNED NOT NULL,

    telefono VARCHAR(20) NULL,
    pais VARCHAR(100) NULL,
    codigo_postal VARCHAR(20) NULL,
    poblacion VARCHAR(100) NULL,
    direccion VARCHAR(255) NULL,

    fecha_nacimiento DATE NULL,
    documento_identidad VARCHAR(50) UNIQUE NULL,

    intentos_fallidos INT NOT NULL DEFAULT 0 CHECK (intentos_fallidos >= 0),
    bloqueado_hasta DATETIME NULL,

    remember_token VARCHAR(100) NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_usuario_rol
        FOREIGN KEY (rol_id)
        REFERENCES roles(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- ========================================
-- TABLA SESSIONS
-- ========================================
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,

    INDEX idx_sessions_user_id (user_id),
    INDEX idx_sessions_last_activity (last_activity)
);

-- ========================================
-- TABLA PASSWORD_RESETS
-- ========================================
CREATE TABLE password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_password_email (email)
);

-- ========================================
-- TABLA EMAIL_VERIFICATIONS
-- ========================================

CREATE TABLE email_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_email_verifications_email (email)
);

-- ========================================
-- PROCESO DE COMPRA
-- ========================================

CREATE TABLE vuelos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    codigo VARCHAR(20) NOT NULL, -- Ej: VY1234

    origen VARCHAR(100) NOT NULL,
    destino VARCHAR(100) NOT NULL,

    fecha_salida DATETIME NOT NULL,
    fecha_llegada DATETIME NOT NULL,

    es_schengen BOOLEAN NOT NULL DEFAULT 1,

    precio_base DECIMAL(10,2) NOT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE reservas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    usuario_id BIGINT UNSIGNED NULL,

    localizador VARCHAR(20) NOT NULL UNIQUE,

    vuelo_id BIGINT UNSIGNED NOT NULL,
    vuelo_vuelta_id BIGINT UNSIGNED NULL,

    plan_ida ENUM('easy', 'comfort') DEFAULT 'easy',
    plan_vuelta ENUM('easy', 'comfort') DEFAULT 'easy',

    precio_total DECIMAL(10,2) NOT NULL,

    estado ENUM('pendiente', 'pagado', 'cancelado') DEFAULT 'pendiente',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (vuelo_id) REFERENCES vuelos(id),
    FOREIGN KEY (vuelo_vuelta_id) REFERENCES vuelos(id)
);

CREATE TABLE pasajeros (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    reserva_id BIGINT UNSIGNED NOT NULL,

    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,

    fecha_nacimiento DATE NULL,

    tipo ENUM('infante', 'menor', 'adulto') NOT NULL,

    documento_identidad VARCHAR(50) NULL,
    nacionalidad VARCHAR(100) NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE
);

CREATE TABLE asientos_vuelo (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    vuelo_id BIGINT UNSIGNED NOT NULL,

    codigo VARCHAR(10) NOT NULL, -- Ej: 12A

    tipo ENUM('space_one', 'space_plus', 'space', 'estandar') NOT NULL,

    ocupado BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (vuelo_id) REFERENCES vuelos(id) ON DELETE CASCADE,

    UNIQUE (vuelo_id, codigo) -- evita duplicados tipo 12A en mismo vuelo
);

CREATE TABLE pasajero_asiento (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    pasajero_id BIGINT UNSIGNED NOT NULL,
    asiento_vuelo_id BIGINT UNSIGNED NOT NULL,
    vuelo_id BIGINT UNSIGNED NOT NULL, --  CLAVE

    estado ENUM('pendiente', 'confirmado') DEFAULT 'pendiente',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (pasajero_id) REFERENCES pasajeros(id) ON DELETE CASCADE,
    FOREIGN KEY (asiento_vuelo_id) REFERENCES asientos_vuelo(id) ON DELETE CASCADE,
    FOREIGN KEY (vuelo_id) REFERENCES vuelos(id) ON DELETE CASCADE,

    UNIQUE (pasajero_id, vuelo_id), --  clave real
    UNIQUE (asiento_vuelo_id)
);

CREATE TABLE equipajes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    pasajero_id BIGINT UNSIGNED NOT NULL,
    vuelo_id BIGINT UNSIGNED NOT NULL, -- CLAVE para ida/vuelta

    tipo ENUM('mano', 'facturado') NOT NULL,

    peso ENUM('20', '25', '30') NULL,

    cantidad INT DEFAULT 1,

    precio DECIMAL(10,2) NOT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (pasajero_id) REFERENCES pasajeros(id) ON DELETE CASCADE,
    FOREIGN KEY (vuelo_id) REFERENCES vuelos(id) ON DELETE CASCADE,

    -- Evita duplicados
    UNIQUE (pasajero_id, vuelo_id, tipo)
);

CREATE TABLE pagos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    reserva_id BIGINT UNSIGNED NOT NULL,

    metodo ENUM('tarjeta', 'paypal') NOT NULL,

    cantidad DECIMAL(10,2) NOT NULL,

    estado ENUM('pendiente', 'completado', 'fallido') DEFAULT 'pendiente',

    fecha_pago DATETIME NULL,

    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE
);