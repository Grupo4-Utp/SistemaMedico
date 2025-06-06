-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
 
CREATE DATABASE IF NOT EXISTS sistema_medico;
USE sistema_medico;

-- Tabla Paciente
CREATE TABLE Paciente (
    ID_Paciente INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(100),
    Apellido VARCHAR(100),
    Edad INT,
    Genero VARCHAR(20),
    Direccion VARCHAR(255),
    Telefono VARCHAR(30),
    Correo_Electronico VARCHAR(100),
    Fecha_Registro DATE
);

-- Tabla Medico
CREATE TABLE Medico (
    ID_Medico INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(100),
    Especialidad VARCHAR(100),
    Telefono VARCHAR(30),
    Correo_Electronico VARCHAR(100),
    Disponibilidad VARCHAR(50)
);

-- Tabla Programa_Medico
CREATE TABLE Programa_Medico (
    ID_Programa INT PRIMARY KEY AUTO_INCREMENT,
    Nombre_Programa VARCHAR(100),
    Especialidad VARCHAR(100),
    Enfermedad_Condicion VARCHAR(100),
    Descripcion VARCHAR(255),
    Año INT,
    Meta_Anual INT
);

-- Tabla Consulta_Programada
CREATE TABLE Consulta_Programada (
    ID_Consulta INT PRIMARY KEY AUTO_INCREMENT,
    ID_Paciente INT,
    ID_Medico INT,
    Fecha_Consulta DATE,
    Motivo VARCHAR(255),
    Estado VARCHAR(50),
    FOREIGN KEY (ID_Paciente) REFERENCES Paciente(ID_Paciente) ON DELETE CASCADE,
    FOREIGN KEY (ID_Medico) REFERENCES Medico(ID_Medico) ON DELETE CASCADE
);

-- Tabla Visita_Medica
CREATE TABLE Visita_Medica (
    ID_Visita INT PRIMARY KEY AUTO_INCREMENT,
    ID_Paciente INT,
    ID_Medico INT,
    Fecha_Visita DATE,
    Motivo VARCHAR(255),
    Diagnostico VARCHAR(255),
    Recomendaciones VARCHAR(255),
    FOREIGN KEY (ID_Paciente) REFERENCES Paciente(ID_Paciente) ON DELETE CASCADE,
    FOREIGN KEY (ID_Medico) REFERENCES Medico(ID_Medico) ON DELETE CASCADE
);

-- Tabla Procedimiento_Medico
CREATE TABLE Procedimiento_Medico (
    ID_Procedimiento INT PRIMARY KEY AUTO_INCREMENT,
    ID_Paciente INT,
    ID_Medico INT,
    Tipo_Procedimiento VARCHAR(100),
    Fecha DATE,
    Resultados VARCHAR(255),
    Observaciones VARCHAR(255),
    FOREIGN KEY (ID_Paciente) REFERENCES Paciente(ID_Paciente) ON DELETE CASCADE,
    FOREIGN KEY (ID_Medico) REFERENCES Medico(ID_Medico) ON DELETE CASCADE
);

-- Tabla Procedimiento_Medico_Actualizado
CREATE TABLE Procedimiento_Medico_Actualizado (
    ID_Procedimiento_Actualizado INT PRIMARY KEY AUTO_INCREMENT,
    ID_Paciente INT,
    ID_Medico INT,
    ID_Programa INT,
    Tipo_Procedimiento VARCHAR(100),
    Fecha DATE,
    Resultados VARCHAR(255),
    Observaciones VARCHAR(255),
    FOREIGN KEY (ID_Paciente) REFERENCES Paciente(ID_Paciente) ON DELETE CASCADE,
    FOREIGN KEY (ID_Medico) REFERENCES Medico(ID_Medico) ON DELETE CASCADE,
    FOREIGN KEY (ID_Programa) REFERENCES Programa_Medico(ID_Programa) ON DELETE CASCADE
);

-- Tabla Meta_Anual
CREATE TABLE Meta_Anual (
    ID_Meta INT PRIMARY KEY AUTO_INCREMENT,
    ID_Programa INT,
    Año INT,
    Tipo_Procedimiento VARCHAR(100),
    Cantidad_Esperada INT,
    FOREIGN KEY (ID_Programa) REFERENCES Programa_Medico(ID_Programa) ON DELETE CASCADE
);

-- Tabla Reporte_Programa
CREATE TABLE Reporte_Programa (
    ID_Reporte INT PRIMARY KEY AUTO_INCREMENT,
    ID_Programa INT,
    Año INT,
    Procedimientos_Realizados INT,
    Meta_Anual INT,
    Cumplimiento DECIMAL(5,2),
    Estado VARCHAR(50),
    FOREIGN KEY (ID_Programa) REFERENCES Programa_Medico(ID_Programa) ON DELETE CASCADE
);

-- Tabla Reporte_Procedimientos
CREATE TABLE Reporte_Procedimientos (
    ID_Reporte INT PRIMARY KEY AUTO_INCREMENT,
    ID_Meta INT,
    Año INT,
    Tipo_Procedimiento VARCHAR(100),
    Cantidad_Realizada INT,
    Cantidad_Esperada INT,
    Cumplimiento DECIMAL(5,2),
    FOREIGN KEY (ID_Meta) REFERENCES Meta_Anual(ID_Meta) ON DELETE CASCADE
);

-- Tabla Acceso_Movil
CREATE TABLE Acceso_Movil (
    ID_Usuario INT PRIMARY KEY AUTO_INCREMENT,
    ID_Paciente INT NULL,
    ID_Medico INT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    tipo_usuario VARCHAR(50) NOT NULL,
    email VARCHAR(100) NULL,
    ultimo_acceso DATETIME NULL,
    token_recuperacion VARCHAR(64) NULL,
    token_expira DATETIME NULL,
    FOREIGN KEY (ID_Paciente) REFERENCES Paciente(ID_Paciente) ON DELETE SET NULL,
    FOREIGN KEY (ID_Medico) REFERENCES Medico(ID_Medico) ON DELETE SET NULL,
    CHECK (
        (ID_Paciente IS NOT NULL AND ID_Medico IS NULL) OR
        (ID_Paciente IS NULL AND ID_Medico IS NOT NULL)
    )
);


-- Tabla Seguridad_Acceso
CREATE TABLE Seguridad_Acceso (
    ID_Log INT PRIMARY KEY AUTO_INCREMENT,
    ID_Usuario INT,
    Fecha_Hora DATETIME,
    Accion_Realizada VARCHAR(100),
    IP_Dispositivo VARCHAR(45),
    FOREIGN KEY (ID_Usuario) REFERENCES Acceso_Movil(ID_Usuario) ON DELETE CASCADE
);

-- Tabla Notificacion
CREATE TABLE Notificacion (
    ID_Notificacion INT PRIMARY KEY AUTO_INCREMENT,
    ID_Paciente INT,
    Mensaje VARCHAR(255),
    Fecha_Programada DATE,
    FOREIGN KEY (ID_Paciente) REFERENCES Paciente(ID_Paciente) ON DELETE CASCADE
);

-- Tabla Historial_Medico
CREATE TABLE Historial_Medico (
    ID_Historial INT PRIMARY KEY AUTO_INCREMENT,
    ID_Paciente INT,
    Diagnostico VARCHAR(255),
    Alergias VARCHAR(255),
    Enfermedades_Cronicas VARCHAR(255),
    Notas_Medicas VARCHAR(255),
    FOREIGN KEY (ID_Paciente) REFERENCES Paciente(ID_Paciente) ON DELETE CASCADE
);

COMMIT;
