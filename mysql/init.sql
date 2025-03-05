-- mysql/init.sql
CREATE DATABASE IF NOT EXISTS ma_base;
USE ma_base;

CREATE TABLE IF NOT EXISTS users_bdd (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Insertion d'un utilisateur admin par défaut.
-- Remarque : en production, pensez à stocker un mot de passe hashé.
INSERT INTO users_bdd (username, password)
VALUES ('admin', 'admin');
