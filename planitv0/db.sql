-- Planit MySQL Workbench schema
-- Script canonico de creacion completa alineado con el codigo Laravel actual.

-- DROP DATABASE IF EXISTS planit;

CREATE DATABASE planit
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE planit;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,

    rol_id BIGINT UNSIGNED NOT NULL,

    esta_verificado TINYINT(1) NOT NULL DEFAULT 0,
    esta_activo TINYINT(1) NOT NULL DEFAULT 1,

    deleted_at DATETIME NULL,
    anonymized_at DATETIME NULL,

    intentos_fallidos INT NOT NULL DEFAULT 0,
    bloqueado_hasta DATETIME NULL,

    remember_token VARCHAR(100) NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_usuarios_rol
        FOREIGN KEY (rol_id) REFERENCES roles(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX idx_usuarios_bloqueado_hasta (bloqueado_hasta)
) ENGINE=InnoDB;

CREATE TABLE usuarios_perfil (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,

    fecha_nacimiento DATE NOT NULL,

    telefono_prefijo VARCHAR(10) NULL,
    telefono_numero VARCHAR(15) NULL,

    pais VARCHAR(100) NULL,
    ciudad VARCHAR(100) NULL,
    direccion VARCHAR(150) NULL,
    codigo_postal VARCHAR(10) NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_usuarios_perfil_user
        FOREIGN KEY (user_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE verificaciones_email (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    hash_codigo CHAR(64) NOT NULL,
    expira_en DATETIME NOT NULL,
    usado TINYINT(1) NOT NULL DEFAULT 0,

    intentos INT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    ultimo_envio_en DATETIME NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_verificaciones_email_user
        FOREIGN KEY (user_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    UNIQUE KEY uq_verificaciones_email_user (user_id),
    INDEX idx_verificaciones_email_expira_en (expira_en),
    INDEX idx_verificaciones_email_bloqueado_hasta (bloqueado_hasta),
    INDEX idx_verificaciones_email_ultimo_envio_en (ultimo_envio_en)
) ENGINE=InnoDB;

CREATE TABLE reseteos_contrasena (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    hash_token CHAR(64) NOT NULL,
    expira_en DATETIME NOT NULL,
    usado TINYINT(1) NOT NULL DEFAULT 0,

    intentos INT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    ultimo_envio_en DATETIME NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_reseteos_contrasena_user
        FOREIGN KEY (user_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    UNIQUE KEY uq_reseteos_contrasena_user (user_id),
    INDEX idx_reseteos_contrasena_expira_en (expira_en),
    INDEX idx_reseteos_contrasena_bloqueado_hasta (bloqueado_hasta),
    INDEX idx_reseteos_contrasena_ultimo_envio_en (ultimo_envio_en)
) ENGINE=InnoDB;

CREATE TABLE historial_contrasenas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    hash_contrasena VARCHAR(255) NOT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_historial_contrasenas_user
        FOREIGN KEY (user_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_historial_contrasenas_user (user_id),
    INDEX idx_historial_contrasenas_user_created (user_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE bajas_cuenta (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,

    motivo ENUM('problemas_web','atencion_cliente','no_necesito','otro') NOT NULL,
    comentario TEXT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_bajas_cuenta_user
        FOREIGN KEY (user_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX idx_bajas_cuenta_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,

    INDEX idx_sessions_user_id (user_id),
    INDEX idx_sessions_last_activity (last_activity),

    CONSTRAINT fk_sessions_user
        FOREIGN KEY (user_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reservas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,

    enlazada_en DATETIME NULL,
    localizador VARCHAR(20) NOT NULL UNIQUE,
    vuelo_id BIGINT UNSIGNED NULL,
    vuelo_vuelta_id BIGINT UNSIGNED NULL,

    origen VARCHAR(100) NOT NULL,
    destino VARCHAR(100) NOT NULL,

    fecha_salida DATETIME NOT NULL,
    fecha_llegada DATETIME NOT NULL,

    estado ENUM('confirmada','datos_pendientes','completada','cancelada_usuario','cancelada_aerolinea')
        NOT NULL DEFAULT 'datos_pendientes',

    plan_tarifa VARCHAR(20) NOT NULL DEFAULT 'planit_easy',
    precio_total DECIMAL(10,2) NULL,
    email_contacto VARCHAR(150) NOT NULL,

    checkin_disponible_desde DATETIME NULL,
    checkin_realizado_en DATETIME NULL,

    checkin_estado ENUM('pendiente','confirmada') NOT NULL DEFAULT 'pendiente',
    tarjetas_emitidas TINYINT(1) NOT NULL DEFAULT 0,
    checkin_correo_intentado_en DATETIME NULL,
    checkin_correo_estado ENUM('pendiente','enviado','fallido') NOT NULL DEFAULT 'pendiente',
    checkin_correo_error VARCHAR(255) NULL,

    equipaje_resumen VARCHAR(255) NULL,
    asientos_resumen VARCHAR(255) NULL,
    meteorologia_resumen VARCHAR(255) NULL,

    deleted_at DATETIME NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reservas_user
        FOREIGN KEY (user_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_reservas_vuelo_ida
        FOREIGN KEY (vuelo_id) REFERENCES vuelos(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_reservas_vuelo_vuelta
        FOREIGN KEY (vuelo_vuelta_id) REFERENCES vuelos(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    INDEX idx_reservas_user (user_id),
    INDEX idx_reservas_vuelo_ida (vuelo_id),
    INDEX idx_reservas_vuelo_vuelta (vuelo_vuelta_id),
    INDEX idx_reservas_estado (estado),
    INDEX idx_reservas_fecha_salida (fecha_salida),
    INDEX idx_reservas_plan_tarifa (plan_tarifa),
    INDEX idx_reservas_checkin_desde (checkin_disponible_desde),
    INDEX idx_reservas_checkin_realizado (checkin_realizado_en),
    INDEX idx_reservas_checkin_estado (checkin_estado),
    INDEX idx_reservas_tarjetas_emitidas (tarjetas_emitidas),
    INDEX idx_reservas_checkin_correo_estado (checkin_correo_estado),
    INDEX idx_reservas_enlazada_en (enlazada_en),
    INDEX idx_reservas_deleted_at (deleted_at)
) ENGINE=InnoDB;

-- NEGOCIO: TOKENS PARA ENLAZAR RESERVA A CUENTA
CREATE TABLE verificaciones_enlace_reserva (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    email_contacto VARCHAR(150) NOT NULL,

    hash_token CHAR(64) NOT NULL,
    expira_en DATETIME NOT NULL,
    usado TINYINT(1) NOT NULL DEFAULT 0,

    intentos INT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    ultimo_envio_en DATETIME NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_verif_enlace_reserva_reserva
        FOREIGN KEY (reserva_id) REFERENCES reservas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_verif_enlace_reserva_user
        FOREIGN KEY (user_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    UNIQUE KEY uq_verif_enlace_reserva_reserva (reserva_id),
    INDEX idx_verif_enlace_reserva_user_usado (user_id, usado),
    INDEX idx_verif_enlace_reserva_expira_en (expira_en),
    INDEX idx_verif_enlace_reserva_bloqueado_hasta (bloqueado_hasta),
    INDEX idx_verif_enlace_reserva_ultimo_envio_en (ultimo_envio_en)
) ENGINE=InnoDB;

-- NEGOCIO CHECK-IN: DATOS DE PASAJEROS Y ASIGNACION DE ASIENTO
CREATE TABLE reserva_pasajeros (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id BIGINT UNSIGNED NOT NULL,

    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,

    tipo_documento ENUM('DNI','PASAPORTE') NULL,
    numero_documento VARCHAR(20) NULL,
    numero_documento_norm VARCHAR(20) NULL,

    fecha_nacimiento DATE NULL,

    checkin_confirmado_en DATETIME NULL,
    asiento_codigo VARCHAR(10) NULL,
    asiento_asignado_en DATETIME NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reserva_pasajeros_reserva
        FOREIGN KEY (reserva_id) REFERENCES reservas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    UNIQUE KEY uq_reserva_pasajeros_doc_norm (reserva_id, tipo_documento, numero_documento_norm),
    UNIQUE KEY uq_reserva_pasajeros_reserva_asiento (reserva_id, asiento_codigo),
    INDEX idx_reserva_pasajeros_doc_norm (numero_documento_norm),
    INDEX idx_reserva_pasajeros_checkin_confirmado (checkin_confirmado_en),
    INDEX idx_reserva_pasajeros_asiento (reserva_id, asiento_codigo)
) ENGINE=InnoDB;

-- NEGOCIO CHECK-IN: TRAZABILIDAD DE EVENTOS DEL PROCESO
CREATE TABLE checkin_eventos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    reserva_id BIGINT UNSIGNED NOT NULL,
    reserva_pasajero_id BIGINT UNSIGNED NULL,

    tipo ENUM(
        'checkin_iniciado',
        'checkin_confirmado',
        'estado_actualizado',
        'tarjetas_emitidas',
        'correo_checkin_exito',
        'correo_checkin_fallo'
    ) NOT NULL,

    actor_tipo ENUM('usuario','invitado','sistema') NOT NULL DEFAULT 'sistema',
    actor_user_id BIGINT UNSIGNED NULL,
    actor_email VARCHAR(150) NULL,

    descripcion VARCHAR(255) NULL,
    meta JSON NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_checkin_evento_reserva
        FOREIGN KEY (reserva_id) REFERENCES reservas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_checkin_evento_pasajero
        FOREIGN KEY (reserva_pasajero_id) REFERENCES reserva_pasajeros(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_checkin_evento_actor_user
        FOREIGN KEY (actor_user_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    INDEX idx_checkin_eventos_reserva_fecha (reserva_id, created_at),
    INDEX idx_checkin_eventos_tipo (tipo)
) ENGINE=InnoDB;

CREATE TABLE cancelaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id BIGINT UNSIGNED NOT NULL,

    tipo ENUM('usuario','aerolinea') NOT NULL,
    motivo TEXT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_cancelaciones_reserva
        FOREIGN KEY (reserva_id) REFERENCES reservas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    UNIQUE KEY uq_cancelaciones_reserva (reserva_id)
) ENGINE=InnoDB;

CREATE TABLE reembolsos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id BIGINT UNSIGNED NOT NULL,

    estado ENUM('pendiente','completado','no_aplicable') NOT NULL DEFAULT 'no_aplicable',
    cantidad DECIMAL(10,2) NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reembolsos_reserva
        FOREIGN KEY (reserva_id) REFERENCES reservas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    UNIQUE KEY uq_reembolsos_reserva (reserva_id)
) ENGINE=InnoDB;

CREATE TABLE reserva_estado_historial (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id BIGINT UNSIGNED NOT NULL,
    estado_anterior VARCHAR(40) NULL,
    estado_nuevo VARCHAR(40) NOT NULL,
    motivo VARCHAR(255) NULL,
    changed_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reserva_historial_reserva
        FOREIGN KEY (reserva_id) REFERENCES reservas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_reserva_historial_reserva (reserva_id),
    INDEX idx_reserva_historial_fecha (changed_at)
) ENGINE=InnoDB;

CREATE TABLE pasajeros_frecuentes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,

    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,

    tipo_documento ENUM('DNI','PASAPORTE') NULL,
    numero_documento VARCHAR(20) NULL,
    numero_documento_norm VARCHAR(20) NULL,

    fecha_nacimiento DATE NOT NULL,

    pais VARCHAR(100) NULL,
    favorito TINYINT(1) NOT NULL DEFAULT 0,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pasajeros_frecuentes_user
        FOREIGN KEY (user_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    UNIQUE KEY uq_pf_user_doc_norm (user_id, tipo_documento, numero_documento_norm),
    INDEX idx_pasajeros_frecuentes_user (user_id),
    INDEX idx_pasajeros_frecuentes_doc_norm (numero_documento_norm)
) ENGINE=InnoDB;

-- INTEGRACION MODULOS: DESTINOS/OFERTAS + ESTADO VUELOS + PROCESO COMPRA

CREATE TABLE ciudades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    pais VARCHAR(120) NOT NULL,
    codigo_iata CHAR(3) NOT NULL,
    imagen VARCHAR(255) NULL,
    latitud DECIMAL(10,7) NULL,
    longitud DECIMAL(10,7) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_ciudades_codigo_iata (codigo_iata),
    INDEX idx_ciudades_nombre_pais (nombre, pais)
) ENGINE=InnoDB;

CREATE TABLE aerolineas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    codigo_iata CHAR(2) NULL,
    codigo_icao CHAR(3) NULL,
    logotipo VARCHAR(255) NULL,
    descripcion TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_aerolineas_nombre (nombre),
    INDEX idx_aerolineas_iata (codigo_iata),
    INDEX idx_aerolineas_icao (codigo_icao)
) ENGINE=InnoDB;

CREATE TABLE aeropuertos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    ciudad VARCHAR(120) NOT NULL,
    pais VARCHAR(120) NOT NULL,
    codigo_iata CHAR(3) NOT NULL,
    codigo_icao CHAR(4) NULL,
    ciudad_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_aeropuertos_ciudad
        FOREIGN KEY (ciudad_id) REFERENCES ciudades(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    UNIQUE KEY uq_aeropuertos_codigo_iata (codigo_iata),
    INDEX idx_aeropuertos_ciudad_id (ciudad_id)
) ENGINE=InnoDB;

CREATE TABLE estados_vuelo (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255) NULL,
    color_badge VARCHAR(20) NULL,
    icono VARCHAR(20) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_estados_vuelo_nombre (nombre)
) ENGINE=InnoDB;

CREATE TABLE rutas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aerolinea_id BIGINT UNSIGNED NULL,
    aeropuerto_origen_id BIGINT UNSIGNED NOT NULL,
    aeropuerto_destino_id BIGINT UNSIGNED NOT NULL,
    distancia_km DECIMAL(10,2) NULL,
    duracion_estimada INT UNSIGNED NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_rutas_aerolinea
        FOREIGN KEY (aerolinea_id) REFERENCES aerolineas(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_rutas_origen
        FOREIGN KEY (aeropuerto_origen_id) REFERENCES aeropuertos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_rutas_destino
        FOREIGN KEY (aeropuerto_destino_id) REFERENCES aeropuertos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    UNIQUE KEY uq_rutas_origen_destino (aeropuerto_origen_id, aeropuerto_destino_id)
) ENGINE=InnoDB;

CREATE TABLE tipos_aviones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    fabricante VARCHAR(100) NULL,
    capacidad_pasajeros INT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE aviones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    matricula VARCHAR(20) NOT NULL,
    tipo_avion_id BIGINT UNSIGNED NULL,
    aerolinea_id BIGINT UNSIGNED NULL,
    año_fabricacion YEAR NULL,
    horas_vuelo INT UNSIGNED NOT NULL DEFAULT 0,
    estado VARCHAR(20) NOT NULL DEFAULT 'activo',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_aviones_tipo
        FOREIGN KEY (tipo_avion_id) REFERENCES tipos_aviones(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_aviones_aerolinea
        FOREIGN KEY (aerolinea_id) REFERENCES aerolineas(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE vuelos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Campos usados por proceso_compra
    codigo VARCHAR(20) NULL,
    origen VARCHAR(100) NULL,
    destino VARCHAR(100) NULL,
    fecha_salida DATETIME NOT NULL,
    fecha_llegada DATETIME NULL,
    es_schengen TINYINT(1) NOT NULL DEFAULT 1,
    precio_base DECIMAL(10,2) NULL,

    -- Campos usados por destinos/ofertas
    origen_ciudad_id BIGINT UNSIGNED NULL,
    destino_ciudad_id BIGINT UNSIGNED NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    asientos_disponibles INT UNSIGNED NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    terminal VARCHAR(20) NULL,
    tipo_tarifa VARCHAR(20) NULL,

    -- Campos usados por estado de vuelos
    numero_vuelo VARCHAR(20) NULL,
    aerolinea_id BIGINT UNSIGNED NULL,
    ruta_id BIGINT UNSIGNED NULL,
    avion_id BIGINT UNSIGNED NULL,
    estado_id BIGINT UNSIGNED NULL,
    hora_salida_programada DATETIME NULL,
    hora_salida_real DATETIME NULL,
    hora_llegada_programada DATETIME NULL,
    hora_llegada_real DATETIME NULL,
    puerta_salida VARCHAR(20) NULL,
    puerta_llegada VARCHAR(20) NULL,
    terminal_salida VARCHAR(20) NULL,
    terminal_llegada VARCHAR(20) NULL,
    pasajeros_confirmados INT UNSIGNED NULL,
    tripulacion_cantidad INT UNSIGNED NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_vuelos_origen_ciudad
        FOREIGN KEY (origen_ciudad_id) REFERENCES ciudades(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_vuelos_destino_ciudad
        FOREIGN KEY (destino_ciudad_id) REFERENCES ciudades(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_vuelos_aerolinea
        FOREIGN KEY (aerolinea_id) REFERENCES aerolineas(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_vuelos_avion
        FOREIGN KEY (avion_id) REFERENCES aviones(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_vuelos_ruta
        FOREIGN KEY (ruta_id) REFERENCES rutas(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_vuelos_estado
        FOREIGN KEY (estado_id) REFERENCES estados_vuelo(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    INDEX idx_vuelos_fecha_salida (fecha_salida),
    INDEX idx_vuelos_numero_vuelo (numero_vuelo),
    INDEX idx_vuelos_ciudad_ruta (origen_ciudad_id, destino_ciudad_id),
    INDEX idx_vuelos_ruta_estado (ruta_id, estado_id)
) ENGINE=InnoDB;

CREATE TABLE ofertas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vuelo_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    descuento DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    precio_promocional DECIMAL(10,2) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    cupo INT UNSIGNED NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_ofertas_vuelo
        FOREIGN KEY (vuelo_id) REFERENCES vuelos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_ofertas_activa_fechas (activo, fecha_inicio, fecha_fin),
    INDEX idx_ofertas_vuelo (vuelo_id)
) ENGINE=InnoDB;

CREATE TABLE asientos_vuelo (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vuelo_id BIGINT UNSIGNED NOT NULL,
    codigo VARCHAR(10) NOT NULL,
    tipo ENUM('planit_plus', 'planit_one', 'planit_space', 'estandar') NOT NULL,
    ocupado TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_asientos_vuelo_vuelo
        FOREIGN KEY (vuelo_id) REFERENCES vuelos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    UNIQUE KEY uq_asientos_vuelo_codigo (vuelo_id, codigo)
) ENGINE=InnoDB;

CREATE TABLE pasajeros (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    fecha_nacimiento DATE NULL,
    tipo ENUM('adulto', 'menor', 'infante', 'nino', 'bebe') NOT NULL,
    documento_identidad VARCHAR(50) NULL,
    nacionalidad VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pasajeros_reserva
        FOREIGN KEY (reserva_id) REFERENCES reservas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_pasajeros_reserva (reserva_id)
) ENGINE=InnoDB;

CREATE TABLE pasajero_asiento (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pasajero_id BIGINT UNSIGNED NOT NULL,
    asiento_vuelo_id BIGINT UNSIGNED NOT NULL,
    vuelo_id BIGINT UNSIGNED NOT NULL,
    estado ENUM('pendiente', 'confirmado') NOT NULL DEFAULT 'pendiente',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pasajero_asiento_pasajero
        FOREIGN KEY (pasajero_id) REFERENCES pasajeros(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_pasajero_asiento_asiento
        FOREIGN KEY (asiento_vuelo_id) REFERENCES asientos_vuelo(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_pasajero_asiento_vuelo
        FOREIGN KEY (vuelo_id) REFERENCES vuelos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    UNIQUE KEY uq_pasajero_asiento_pasajero_vuelo (pasajero_id, vuelo_id),
    UNIQUE KEY uq_pasajero_asiento_asiento (asiento_vuelo_id)
) ENGINE=InnoDB;

CREATE TABLE equipajes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pasajero_id BIGINT UNSIGNED NOT NULL,
    vuelo_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('mano', 'facturado') NOT NULL,
    peso ENUM('20', '25', '30') NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_equipajes_pasajero
        FOREIGN KEY (pasajero_id) REFERENCES pasajeros(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_equipajes_vuelo
        FOREIGN KEY (vuelo_id) REFERENCES vuelos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    UNIQUE KEY uq_equipajes_pasajero_vuelo_tipo_peso (pasajero_id, vuelo_id, tipo, peso)
) ENGINE=InnoDB;

CREATE TABLE pagos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id BIGINT UNSIGNED NOT NULL,
    metodo ENUM('tarjeta', 'paypal') NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'completado', 'fallido') NOT NULL DEFAULT 'pendiente',
    fecha_pago DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pagos_reserva
        FOREIGN KEY (reserva_id) REFERENCES reservas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_pagos_reserva_estado (reserva_id, estado)
) ENGINE=InnoDB;

-- SEGURIDAD: AUDITORIA DE CAMBIOS
CREATE TABLE logs_cambios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,

    tabla_afectada VARCHAR(100) NULL,
    accion ENUM('INSERT','UPDATE','DELETE') NULL,
    descripcion TEXT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_logs_user (user_id),
    INDEX idx_logs_created_at (created_at),

    CONSTRAINT fk_logs_user
        FOREIGN KEY (user_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cache (
    `key` VARCHAR(255) NOT NULL,
    `value` MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB;

CREATE TABLE cache_locks (
    `key` VARCHAR(255) NOT NULL,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB;

CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX jobs_queue_index (queue)
) ENGINE=InnoDB;

CREATE TABLE job_batches (
    id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id),
    INDEX personal_access_tokens_expires_at_index (expires_at)
) ENGINE=InnoDB;

INSERT INTO roles (nombre, created_at, updated_at)
VALUES
    ('superadmin', NOW(), NOW()),
    ('admin', NOW(), NOW()),
    ('usuario', NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at);

SET FOREIGN_KEY_CHECKS = 1;
