<?php
// config.php
session_start();

// Paramètres de connexion : 
// - L'hôte correspond au nom du service MySQL défini dans docker-compose
$host = 'bdd-mysql';
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
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit;
}
?>
