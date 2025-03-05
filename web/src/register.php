<?php
// register.php
require_once 'config.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['new_username'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');

    if (!empty($new_username) && !empty($new_password)) {
        // Mise à jour des identifiants dans la base
        $stmt = $pdo->prepare("UPDATE users_bdd SET username = ?, password = ? WHERE id = ?");
        $stmt->execute([$new_username, $new_password, $_SESSION['user_id']]);

        // Mise à jour de la session
        $_SESSION['username'] = $new_username;

        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier vos identifiants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4">
        <h2 class="card-title text-center">Modifier vos identifiants</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form action="register.php" method="post">
            <div class="mb-3">
                <label for="new_username" class="form-label">Nouveau nom d'utilisateur</label>
                <input type="text" class="form-control" id="new_username" name="new_username" required autofocus>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Mettre à jour</button>
        </form>
    </div>
</body>
</html>
