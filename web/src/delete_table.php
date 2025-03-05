<?php
require_once 'config.php';

$response = ['success' => false, 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = trim($_POST['table'] ?? '');
    if (empty($table)) {
        $response['errors'][] = "Nom de table manquant.";
    } else {
        // Pour éviter de supprimer "users_bdd"
        if ($table === 'users_bdd') {
            $response['errors'][] = "Action non autorisée sur cette table.";
        } else {
            try {
                $sql = "DROP TABLE `$table`";
                $pdo->exec($sql);
                $response['success'] = true;
            } catch (PDOException $e) {
                $response['errors'][] = "Erreur lors de la suppression : " . $e->getMessage();
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
