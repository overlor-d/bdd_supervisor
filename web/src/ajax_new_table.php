<?php
require_once 'config.php';

$response = ['success' => false, 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tableName = trim($_POST['table_name'] ?? '');
    $columns = $_POST['columns'] ?? [];

    if (empty($tableName)) {
        $response['errors'][] = "Le nom de la table est requis.";
    }
    if (empty($columns)) {
        $response['errors'][] = "Veuillez ajouter au moins une colonne.";
    }

    if (empty($response['errors'])) {
        $sql = "CREATE TABLE `$tableName` (";
        $cols_sql = [];
        $primary_keys = [];

        foreach ($columns as $col) {
            $colName = trim($col['name'] ?? '');
            $colType = trim($col['type'] ?? '');
            $colLength = trim($col['length'] ?? '');
            $colOptionsArray = $col['options'] ?? [];

            // Si name ou type sont vides, on ignore la colonne
            if (empty($colName) || empty($colType)) {
                continue;
            }

            $colDef = "`$colName` $colType";
            if (!empty($colLength)) {
                $colDef .= "($colLength)";
            }

            $filteredOptions = [];
            foreach ($colOptionsArray as $opt) {
                if (strtoupper($opt) === 'PRIMARY KEY') {
                    $primary_keys[] = "`$colName`";
                } else {
                    $filteredOptions[] = $opt;
                }
            }
            if (!empty($filteredOptions)) {
                $colDef .= " " . implode(" ", $filteredOptions);
            }

            $cols_sql[] = $colDef;
        }

        // Si on a des colonnes marquées PRIMARY KEY, on ajoute la clause
        if (!empty($primary_keys)) {
            $cols_sql[] = "PRIMARY KEY (" . implode(", ", $primary_keys) . ")";
        }

        if (empty($cols_sql)) {
            $response['errors'][] = "Aucune colonne valide n'a été définie.";
        } else {
            $sql .= implode(", ", $cols_sql) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            try {
                $pdo->exec($sql);
                $response['success'] = true;
            } catch (PDOException $e) {
                $response['errors'][] = "Erreur lors de la création de la table : " . $e->getMessage();
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
