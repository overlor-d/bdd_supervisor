<?php
// config.php

// Désactivation de l'affichage des erreurs pour l'utilisateur
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Gestionnaires d'erreurs personnalisés
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    error_log("[$errno] $errstr in $errfile on line $errline");
    header("Location: /error");
    exit;
}
set_error_handler("customErrorHandler");

function customExceptionHandler($exception) {
    error_log($exception);
    header("Location: /error");
    exit;
}
set_exception_handler("customExceptionHandler");

session_start();

// Paramètres de connexion à la BDD
$host = 'bdd-mysql'; // Nom du service défini dans docker-compose
$db   = 'ma_base';
$user = 'admin';
$pass = 'admin';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    header("Location: /error");
    exit;
}
?>
