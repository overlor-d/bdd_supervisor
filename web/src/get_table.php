<?php
require_once 'config.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

$table = $_GET['table'] ?? '';
if(!$table) {
   echo 'Table non spécifiée.';
   exit;
}

// Pour plus de sécurité, vous pouvez vérifier que le nom de table est valide (ex. en le comparant à la liste obtenue avec SHOW TABLES).

try {
    // Récupérer toutes les données de la table demandée
    $stmt = $pdo->query("SELECT * FROM `$table`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erreur lors de la récupération de la table.</div>';
    exit;
}

// Si la table est vide ou si on n’a pas pu récupérer les colonnes, tenter de récupérer la structure de la table
if (empty($rows)) {
    $columns = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $columns = array_keys($rows[0]);
}
?>
<h4 class="mt-3">Contenu de la table : <?= htmlspecialchars($table) ?></h4>
<?php if(empty($rows) && empty($columns)): ?>
    <div class="alert alert-warning">La table est vide ou n'existe pas.</div>
<?php else: ?>
    <div class="table-responsive">
    <table class="table table-bordered">
        <thead>
          <tr>
             <?php foreach($columns as $col): ?>
                <th><?= htmlspecialchars(is_array($col) ? $col['Field'] : $col) ?></th>
             <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $row): ?>
            <tr>
              <?php foreach($row as $cell): ?>
                <td><?= htmlspecialchars($cell) ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
