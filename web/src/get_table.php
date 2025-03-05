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

try {
    // Récupération de toutes les lignes de la table
    $stmt = $pdo->query("SELECT * FROM `$table`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erreur lors de la récupération de la table.</div>';
    exit;
}

// Récupération des colonnes
if (!empty($rows)) {
    $columns = array_keys($rows[0]);
} else {
    $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
    $columnsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columns = [];
    foreach ($columnsData as $col) {
        $columns[] = $col['Field'];
    }
}
?>
<div class="mb-3">
  <button class="btn btn-primary" id="newRowBtn" data-table="<?= htmlspecialchars($table) ?>">Nouvelle ligne</button>
</div>
<h4 class="mt-3">Contenu de la table : <?= htmlspecialchars($table) ?></h4>
<?php if(empty($rows) && empty($columns)): ?>
    <div class="alert alert-warning">La table est vide ou n'existe pas.</div>
<?php else: ?>
    <div class="table-responsive">
    <table class="table table-bordered">
        <thead>
          <tr>
             <?php foreach($columns as $col): ?>
                <th><?= htmlspecialchars($col) ?></th>
             <?php endforeach; ?>
             <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $row): ?>
            <tr data-row='<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
              <?php foreach($columns as $col): ?>
                <td><?= htmlspecialchars($row[$col] ?? '') ?></td>
              <?php endforeach; ?>
              <td>
                <!-- Menu déroulant pour la ligne -->
                <div class="dropdown">
                  <button class="btn btn-link p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    ...
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item row-edit" href="#" data-table="<?= htmlspecialchars($table) ?>">Modifier</a></li>
                    <li><a class="dropdown-item row-delete" href="#" data-table="<?= htmlspecialchars($table) ?>">Supprimer</a></li>
                  </ul>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>

<!-- Modale pour ajouter une nouvelle ligne -->
<div class="modal fade" id="newRowModal" tabindex="-1" aria-labelledby="newRowModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="newRowForm">
        <div class="modal-header">
          <h5 class="modal-title" id="newRowModalLabel">Ajouter une nouvelle ligne dans <?= htmlspecialchars($table) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body" id="newRowBody">
          <!-- Le formulaire sera généré dynamiquement en JS à partir de la structure des colonnes -->
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Ajouter</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modale pour modifier une ligne -->
<div class="modal fade" id="editRowModal" tabindex="-1" aria-labelledby="editRowModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="editRowForm">
        <div class="modal-header">
          <h5 class="modal-title" id="editRowModalLabel">Modifier la ligne dans <?= htmlspecialchars($table) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body" id="editRowBody">
          <!-- Le formulaire sera généré dynamiquement avec les valeurs existantes -->
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){

  // Fonction pour générer un formulaire à partir des colonnes
  function generateForm(columns, data = {}) {
    let html = '';
    columns.forEach(function(col){
      // On pré-remplit la valeur si disponible dans data
      let value = data[col] || '';
      html += '<div class="mb-3">';
      html += '<label class="form-label">'+col+'</label>';
      html += '<input type="text" class="form-control" name="'+col+'" value="'+value+'" required>';
      html += '</div>';
    });
    return html;
  }

  // Gestion du bouton "Nouvelle ligne"
  $('#newRowBtn').click(function(){
    let table = $(this).data('table');
    // On utilise la structure des colonnes affichées dans le tableau
    let columns = [];
    $('#table-content table thead th').each(function(index){
      // Ignorer la dernière colonne "Actions"
      if(index < $('#table-content table thead th').length - 1){
         columns.push($(this).text().trim());
      }
    });
    // Générer le formulaire
    let formHtml = generateForm(columns);
    $('#newRowBody').html(formHtml);
    // Stocker le nom de la table dans le formulaire (en hidden)
    if($('#newRowForm input[name="table"]').length === 0){
      $('#newRowForm').prepend('<input type="hidden" name="table" value="'+table+'">');
    } else {
      $('#newRowForm input[name="table"]').val(table);
    }
    $('#newRowModal').modal('show');
  });

  // Soumission du formulaire d'ajout de ligne
  $('#newRowForm').submit(function(e){
    e.preventDefault();
    $.ajax({
      url: '/ajax_add_row',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(response){
        if(response.success){
          $('#newRowModal').modal('hide');
          // Recharger la table
          location.reload();
        } else {
          alert("Erreur : " + response.errors.join('\n'));
        }
      },
      error: function(){
        alert("Erreur AJAX lors de l'ajout de la ligne.");
      }
    });
  });

  // Gestion du clic sur "Modifier" pour une ligne
  $(document).on('click', '.row-edit', function(e){
    e.preventDefault();
    var table = $(this).data('table');
    // On récupère les données de la ligne via l'attribut data-row de la ligne
    var rowData = $(this).closest('tr').data('row');
    // On utilise la structure des colonnes comme pour la modale "Nouvelle ligne"
    let columns = [];
    $('#table-content table thead th').each(function(index){
      if(index < $('#table-content table thead th').length - 1){
         columns.push($(this).text().trim());
      }
    });
    let formHtml = generateForm(columns, rowData);
    $('#editRowBody').html(formHtml);
    // Stocker le nom de la table dans le formulaire
    if($('#editRowForm input[name="table"]').length === 0){
      $('#editRowForm').prepend('<input type="hidden" name="table" value="'+table+'">');
    } else {
      $('#editRowForm input[name="table"]').val(table);
    }
    // Pour identifier la ligne, on suppose que la première colonne est la clé primaire
    if($('#editRowForm input[name="id"]').length === 0){
      $('#editRowForm').prepend('<input type="hidden" name="id" value="'+rowData[columns[0]]+'">');
    } else {
      $('#editRowForm input[name="id"]').val(rowData[columns[0]]);
    }
    $('#editRowModal').modal('show');
  });

  // Soumission du formulaire d'édition de ligne
  $('#editRowForm').submit(function(e){
    e.preventDefault();
    $.ajax({
      url: '/ajax_edit_row',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(response){
        if(response.success){
          $('#editRowModal').modal('hide');
          location.reload();
        } else {
          alert("Erreur : " + response.errors.join('\n'));
        }
      },
      error: function(){
        alert("Erreur AJAX lors de la modification de la ligne.");
      }
    });
  });

  // Gestion du clic sur "Supprimer" pour une ligne
  $(document).on('click', '.row-delete', function(e){
    e.preventDefault();
    var table = $(this).data('table');
    // Pour identifier la ligne, on suppose que la première colonne est la clé primaire
    var rowData = $(this).closest('tr').data('row');
    // Demander confirmation
    if(!confirm("Voulez-vous vraiment supprimer cette ligne ?")) return;
    $.ajax({
      url: '/ajax_delete_row',
      method: 'POST',
      data: { table: table, id: rowData[Object.keys(rowData)[0]] },
      dataType: 'json',
      success: function(response){
        if(response.success){
          alert("Ligne supprimée avec succès.");
          location.reload();
        } else {
          alert("Erreur : " + response.errors.join('\n'));
        }
      },
      error: function(){
        alert("Erreur AJAX lors de la suppression de la ligne.");
      }
    });
  });
});
</script>
