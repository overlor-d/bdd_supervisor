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
        // Récupérer les données à modifier, en excluant table et id
        $data = $_POST;
        unset($data['table'], $data['id']);
        $set = [];
        foreach($data as $col => $val){
            $set[] = "`$col` = :$col";
        }
        $sql = "UPDATE `$table` SET " . implode(', ', $set) . " WHERE `" . array_keys($_POST)[1] . "` = :id";
        try {
            $stmt = $pdo->prepare($sql);
            $data['id'] = $id;
            $stmt->execute($data);
            $response['success'] = true;
        } catch(PDOException $e) {
            $response['errors'][] = "Erreur lors de la modification de la ligne : " . $e->getMessage();
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
