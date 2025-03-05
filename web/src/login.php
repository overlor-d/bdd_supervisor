<?php
// login.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Requête sécurisée avec PDO
$stmt = $pdo->prepare("SELECT * FROM users_bdd WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && $user['password'] === $password) {
    // Authentification réussie
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    
    // Si les identifiants sont par défaut, rediriger vers register.php
    if ($username === 'admin' && $password === 'admin') {
        header('Location: register.php');
        exit;
    } else {
        header('Location: dashboard.php');
        exit;
    }
} else {
    // Authentification échouée, redirection avec message d'erreur
    header('Location: index.php?error=1');
    exit;
}
?>
