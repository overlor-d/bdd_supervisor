<?php
// login.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
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

    // Si les identifiants sont par défaut, rediriger vers register
    if ($username === 'admin' && $password === 'admin') {
        header('Location: /register');
        exit;
    } else {
        header('Location: /dashboard');
        exit;
    }
} else {
    // Échec : rediriger vers index avec un paramètre d'erreur
    header('Location: /?error=1');
    exit;
}
?>
