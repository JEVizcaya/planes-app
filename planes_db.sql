CREATE DATABASE planes_db;

USE planes_db;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE planes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255),
    descripcion TEXT,
    fecha DATE,
    lugar VARCHAR(255),
    capacidad INT,
    creador_id INT,
    FOREIGN KEY (creador_id) REFERENCES usuarios(id)
);

CREATE TABLE participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    plan_id INT,
    UNIQUE(usuario_id, plan_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (plan_id) REFERENCES planes(id)
);
