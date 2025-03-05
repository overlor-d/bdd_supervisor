<?php
require_once 'config.php';

// Récupère la requête envoyée en POST
$query = trim($_POST['query'] ?? '');
if (empty($query)) {
    echo "Aucune requête fournie.";
    exit;
}

// Pour la sécurité, on autorise uniquement les requêtes en lecture seule (commençant par SELECT)
if (stripos($query, 'select') !== 0) {
    echo "Seules les requêtes SELECT sont autorisées.";
    exit;
}

try {
    $stmt = $pdo->query($query);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$result) {
        echo "Aucun résultat.";
    } else {
        // Génère un tableau HTML
        $html = "<table class='table table-bordered'><thead><tr>";
        foreach(array_keys($result[0]) as $col) {
            $html .= "<th>" . htmlspecialchars($col) . "</th>";
        }
        $html .= "</tr></thead><tbody>";
        foreach($result as $row) {
            $html .= "<tr>";
            foreach($row as $cell) {
                $html .= "<td>" . htmlspecialchars($cell) . "</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
        echo $html;
    }
} catch (PDOException $e) {
    echo "Erreur lors de l'exécution de la requête : " . $e->getMessage();
}
