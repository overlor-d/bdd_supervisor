<?php
require_once 'config.php';

$response = ['success' => false, 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldName = trim($_POST['old_name'] ?? '');
    $newName = trim($_POST['new_name'] ?? '');

    if (empty($oldName) || empty($newName)) {
        $response['errors'][] = "Veuillez spécifier l'ancien nom et le nouveau nom.";
    } else {
        // Empêcher la modification de users_bdd
        if ($oldName === 'users_bdd') {
            $response['errors'][] = "Action non autorisée sur cette table.";
        } else {
            try {
                $sql = "RENAME TABLE `$oldName` TO `$newName`";
                $pdo->exec($sql);
                $response['success'] = true;
            } catch (PDOException $e) {
                $response['errors'][] = "Erreur lors du renommage : " . $e->getMessage();
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
