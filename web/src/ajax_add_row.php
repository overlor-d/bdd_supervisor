<?php
require_once 'config.php';
$response = ['success' => false, 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = trim($_POST['table'] ?? '');
    if(empty($table)){
        $response['errors'][] = "Table non spécifiée.";
    } else {
        // Récupérer les données envoyées (les noms de colonnes doivent correspondre à ceux du tableau)
        $data = $_POST;
        unset($data['table']);
        // Préparer la requête INSERT
        $cols = array_keys($data);
        $placeholders = array_map(function($col){ return ':' . $col; }, $cols);
        $sql = "INSERT INTO `$table` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            $response['success'] = true;
        } catch(PDOException $e) {
            $response['errors'][] = "Erreur lors de l'ajout de la ligne : " . $e->getMessage();
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
