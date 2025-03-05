<?php
require_once 'config.php';
$response = ['success' => false, 'errors' => []];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $table = trim($_POST['table'] ?? '');
    $id = trim($_POST['id'] ?? '');
    if(empty($table) || empty($id)){
        $response['errors'][] = "Table ou identifiant manquant.";
    } else {
        // Supposons que la première colonne est "id"
        $sql = "DELETE FROM `$table` WHERE `" . array_keys($_POST)[1] . "` = :id";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $response['success'] = true;
        } catch(PDOException $e) {
            $response['errors'][] = "Erreur lors de la suppression de la ligne : " . $e->getMessage();
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
