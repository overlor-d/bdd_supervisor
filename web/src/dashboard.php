<?php
require_once 'config.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /');
    exit;
}

// Récupération de la liste des tables
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_NUM);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
   <title>Dashboard - Supervision</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   <style>
     .left-panel {
         height: 100vh;
         overflow-y: auto;
         border-right: 1px solid #ccc;
         padding: 10px;
     }
     .tag {
         display: inline-block;
         background: #0d6efd;
         color: #fff;
         padding: 2px 6px;
         border-radius: 3px;
         margin-right: 5px;
         margin-bottom: 3px;
         font-size: 0.85em;
     }
     .tag a {
         color: #fff;
         text-decoration: none;
         margin-left: 3px;
     }
   </style>
   <script>
     $(document).ready(function(){
         // Chargement du contenu de table par AJAX
         $('.table-name').click(function(e){
             e.preventDefault();
             var tableName = $(this).data('table');
             $.ajax({
                 url: '/get_table',
                 method: 'GET',
                 data: { table: tableName },
                 success: function(data){
                     $('#table-content').html(data);
                 },
                 error: function(){
                     $('#table-content').html('<div class="alert alert-danger">Erreur lors du chargement de la table.</div>');
                 }
             });
         });

         // Soumission AJAX du formulaire de création de table
         $('#newTableForm').submit(function(e){
             e.preventDefault();
             $.ajax({
                 url: '/ajax_new_table',
                 method: 'POST',
                 data: $(this).serialize(),
                 dataType: 'json',
                 success: function(response){
                     if(response.success){
                         // Fermer la modale et recharger la page
                         $('#newTableModal').modal('hide');
                         location.reload();
                     } else {
                         var errorHtml = '<ul>';
                         $.each(response.errors, function(i, err){
                             errorHtml += '<li>' + err + '</li>';
                         });
                         errorHtml += '</ul>';
                         $('#newTableErrors').html(errorHtml).show();
                     }
                 },
                 error: function(){
                     $('#newTableErrors').html('Une erreur est survenue lors de la soumission.').show();
                 }
             });
         });

         // PARTIE ESSENTIELLE : colIndex pour garder le même index pour chaque colonne
         let colIndex = 1; // la première colonne sera index 0 (déjà dans le HTML), la suivante sera index 1

         // Ajout d'une nouvelle colonne
         $('#add-column').click(function(e){
             e.preventDefault();
             var html = `
             <div class="row mb-2 column-row">
               <div class="col-md-3">
                 <input type="text" name="columns[${colIndex}][name]" class="form-control" placeholder="Nom de colonne" required>
               </div>
               <div class="col-md-2">
                 <select name="columns[${colIndex}][type]" class="form-control" required>
                   <option value="VARCHAR">VARCHAR</option>
                   <option value="INT">INT</option>
                   <option value="DATE">DATE</option>
                   <option value="TEXT">TEXT</option>
                 </select>
               </div>
               <div class="col-md-2">
                 <input type="text" name="columns[${colIndex}][length]" class="form-control" placeholder="Longueur (optionnel)">
               </div>
               <div class="col-md-4">
                 <div class="options-container"></div>
                 <div class="dropdown mt-1">
                   <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                     Ajouter option
                   </button>
                   <ul class="dropdown-menu">
                     <li><a class="dropdown-item option-item" href="#" data-value="NOT NULL">NOT NULL</a></li>
                     <li><a class="dropdown-item option-item" href="#" data-value="UNIQUE">UNIQUE</a></li>
                     <li><a class="dropdown-item option-item" href="#" data-value="PRIMARY KEY">PRIMARY KEY</a></li>
                     <li><a class="dropdown-item option-item" href="#" data-value="AUTO_INCREMENT">AUTO_INCREMENT</a></li>
                   </ul>
                 </div>
               </div>
               <div class="col-md-1">
                 <button type="button" class="btn btn-danger remove-column">&times;</button>
               </div>
             </div>
             `;
             $('#columnsContainer').append(html);
             colIndex++;
         });

         // Supprimer une colonne
         $(document).on('click', '.remove-column', function(){
             $(this).closest('.column-row').remove();
         });

         // Gérer l'ajout d'une option via le dropdown
         $(document).on('click', '.option-item', function(e){
             e.preventDefault();
             var optionVal = $(this).data('value');
             var $optionsContainer = $(this).closest('.row').find('.options-container');
             if ($optionsContainer.find("span[data-value='"+optionVal+"']").length == 0) {
                 var tag = $('<span class="tag" data-value="'+optionVal+'">'+optionVal+' <a href="#" class="remove-tag">&times;</a></span>');
                 var indexRegex = /columns\[(\d+)\]\[name\]/;
                 // On remonte jusqu'à trouver l'index de la colonne
                 var $colRow = $(this).closest('.column-row');
                 // On cherche un input name="columns[X][name]"
                 var colNameInput = $colRow.find('input[name^="columns["][name$="[name]"]');
                 // On récupère l'index X
                 var fullName = colNameInput.attr('name'); // ex: columns[0][name]
                 var matches = fullName.match(/columns\[(\d+)\]\[name\]/);
                 if(matches){
                     var realIndex = matches[1];
                     // On crée l'input hidden "columns[X][options][]"
                     var input = $('<input type="hidden" name="columns['+realIndex+'][options][]" value="'+optionVal+'">');
                     $optionsContainer.append(tag).append(input);
                 }
             }
         });

         // Supprimer un tag
         $(document).on('click', '.remove-tag', function(e){
             e.preventDefault();
             var $tag = $(this).closest('.tag');
             var optionVal = $tag.data('value');
             var $optionsContainer = $(this).closest('.options-container');
             $optionsContainer.find("span[data-value='"+optionVal+"']").remove();
             $optionsContainer.find("input[value='"+optionVal+"']").remove();
         });

     });
   </script>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Colonne gauche : liste des tables et bouton "New" -->
    <div class="col-md-3 left-panel">
      <h4>Tables</h4>
      <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#newTableModal">New</button>
      <ul class="list-group">
        <?php foreach($tables as $t): 
            $tableName = $t[0];
        ?>
          <li class="list-group-item table-name" data-table="<?= htmlspecialchars($tableName) ?>" style="cursor:pointer;">
            <?= htmlspecialchars($tableName) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <!-- Colonne centrale : affichage du contenu de la table -->
    <div class="col-md-9" id="table-content">
      <h3 class="mt-3">Sélectionnez une table pour afficher son contenu</h3>
    </div>
  </div>
</div>

<!-- Modale de création de table -->
<div class="modal fade" id="newTableModal" tabindex="-1" aria-labelledby="newTableModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="newTableForm">
        <div class="modal-header">
          <h5 class="modal-title" id="newTableModalLabel">Créer une nouvelle table</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <div id="newTableErrors" class="alert alert-danger" style="display:none;"></div>
          <div class="mb-3">
            <label for="table_name" class="form-label">Nom de la table</label>
            <input type="text" id="table_name" name="table_name" class="form-control" required>
          </div>
          <h5>Colonnes</h5>
          <div id="columnsContainer">
            <!-- Première colonne par défaut : index 0 -->
            <div class="row mb-2 column-row">
              <div class="col-md-3">
                <input type="text" name="columns[0][name]" class="form-control" placeholder="Nom de colonne" required>
              </div>
              <div class="col-md-2">
                <select name="columns[0][type]" class="form-control" required>
                  <option value="VARCHAR">VARCHAR</option>
                  <option value="INT">INT</option>
                  <option value="DATE">DATE</option>
                  <option value="TEXT">TEXT</option>
                </select>
              </div>
              <div class="col-md-2">
                <input type="text" name="columns[0][length]" class="form-control" placeholder="Longueur (optionnel)">
              </div>
              <div class="col-md-4">
                <div class="options-container"></div>
                <div class="dropdown mt-1">
                  <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Ajouter option
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item option-item" href="#" data-value="NOT NULL">NOT NULL</a></li>
                    <li><a class="dropdown-item option-item" href="#" data-value="UNIQUE">UNIQUE</a></li>
                    <li><a class="dropdown-item option-item" href="#" data-value="PRIMARY KEY">PRIMARY KEY</a></li>
                    <li><a class="dropdown-item option-item" href="#" data-value="AUTO_INCREMENT">AUTO_INCREMENT</a></li>
                  </ul>
                </div>
              </div>
              <div class="col-md-1">
                <button type="button" class="btn btn-danger remove-column">&times;</button>
              </div>
            </div>
          </div>
          <button id="add-column" class="btn btn-secondary">Ajouter une colonne</button>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Créer la table</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
