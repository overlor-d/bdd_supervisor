<?php
require_once 'config.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /');
    exit;
}

// Récupération de la liste des tables
$stmt = $pdo->query("SHOW TABLES");
$allTables = $stmt->fetchAll(PDO::FETCH_NUM);
// Filtrer pour ne pas afficher users_bdd
$tables = array_filter($allTables, function($t) {
    return $t[0] !== 'users_bdd';
});
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
    html, body {
      height: 100%;
      margin: 0;
      overflow: hidden;
    }
    /* Navbar en haut */
    .navbar-custom {
      background-color: #0d6efd;
    }
    .navbar-custom .navbar-brand,
    .navbar-custom .nav-link {
      color: #fff;
    }
    /* Conteneur principal en flex vertical */
    .vertical-container {
      display: flex;
      flex-direction: column;
      height: calc(100% - 56px); /* 56px = hauteur de la navbar */
    }
    /* Zone de contenu principal (panneaux gauche et droit) */
    .content-area {
      flex: 1;
      display: flex;
      overflow: hidden;
    }
    /* Panneau gauche */
    .left-panel {
      width: 250px;
      border-right: 1px solid #ccc;
      padding: 10px;
      overflow-y: auto;
    }
    /* Panneau droit (contenu principal) */
    .right-panel {
      flex: 1;
      padding: 10px;
      overflow-y: auto;
    }
    /* Zone terminal en bas, avec hauteur initiale */
    #terminal-container {
      height: 200px;
      background: #000;
      color: #0f0;
      font-family: monospace;
      overflow-y: auto;
      position: relative;
    }
    /* Handle pour redimensionner le terminal */
    #resizer {
      height: 5px;
      background: #666;
      cursor: ns-resize;
    }
    /* Terminal SQL (zone d'affichage et textarea) */
    #sql-terminal {
      padding: 10px;
    }
    #sqlQuery {
      width: 100%;
      font-family: monospace;
    }
    /* Style pour les tags d'options */
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
      // Exemples de gestion des actions sur les tables et modales (votre code existant reste ici)
      $('.table-name').click(function(e){
        e.preventDefault();
        var tableName = $(this).data('table');
        $.ajax({
          url: '/get_table',
          method: 'GET',
          data: { table: tableName },
          success: function(data){
            $('.right-panel').html(data);
          },
          error: function(){
            $('.right-panel').html('<div class="alert alert-danger">Erreur lors du chargement de la table.</div>');
          }
        });
      });
      // ... (le reste de vos actions sur tables, modales, etc.)

      // Terminal SQL : exécution de requête
      $('#executeSQL').click(function(){
        var query = $('#sqlQuery').val();
        $.ajax({
          url: '/ajax_sql_query',
          method: 'POST',
          data: { query: query },
          dataType: 'html',
          success: function(data){
            $('#sql-terminal').html(data);
          },
          error: function(){
            $('#sql-terminal').html('<div class="text-danger">Erreur lors de l\'exécution de la requête.</div>');
          }
        });
      });

      // Gestion du redimensionnement du terminal
      var isResizing = false,
          lastDownY = 0,
          container = document.querySelector('.vertical-container'),
          resizer = document.getElementById('resizer'),
          contentArea = document.querySelector('.content-area'),
          terminalContainer = document.getElementById('terminal-container');

      resizer.addEventListener('mousedown', function(e) {
          isResizing = true;
          lastDownY = e.clientY;
          e.preventDefault();
      });

      document.addEventListener('mousemove', function(e) {
          if (!isResizing) return;
          var offset = e.clientY - lastDownY;
          var newHeight = terminalContainer.offsetHeight - offset;
          // Limites minimales et maximales
          if(newHeight < 100) newHeight = 100;
          if(newHeight > container.offsetHeight - 100) newHeight = container.offsetHeight - 100;
          terminalContainer.style.height = newHeight + 'px';
          lastDownY = e.clientY;
      });

      document.addEventListener('mouseup', function(e) {
          isResizing = false;
      });
    });
  </script>
</head>
<body>
  <!-- Navbar en haut -->
  <nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Ma Supervision</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
              aria-controls="navbarNav" aria-expanded="false" aria-label="Basculer la navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <!-- Liens à ajouter ultérieurement -->
          <li class="nav-item">
            <a class="nav-link" href="#">Accueil</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Conteneur principal en flex vertical -->
  <div class="vertical-container">
    <!-- Zone de contenu principal (panneaux gauche et droit) -->
    <div class="content-area">
      <!-- Panneau gauche -->
      <div class="left-panel">
        <h4>Tables</h4>
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#newTableModal">New</button>
        <ul class="list-group">
          <?php foreach($tables as $t): 
            $tableName = $t[0];
          ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span class="table-name" data-table="<?= htmlspecialchars($tableName) ?>" style="cursor:pointer;">
                <?= htmlspecialchars($tableName) ?>
              </span>
              <div class="dropdown">
                <button class="btn btn-link text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                  ...
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item action-edit" href="#" data-table="<?= htmlspecialchars($tableName) ?>">Modifier</a></li>
                  <li><a class="dropdown-item action-delete" href="#" data-table="<?= htmlspecialchars($tableName) ?>">Supprimer</a></li>
                </ul>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <!-- Panneau droit -->
      <div class="right-panel">
        <h3>Sélectionnez une table pour afficher son contenu</h3>
      </div>
    </div>

    <!-- Séparateur redimensionnable -->
    <div id="resizer"></div>

    <!-- Zone Terminal SQL fixée en bas -->
    <div id="terminal-container">
      <div id="sql-terminal">
        <div class="mb-2">
          <textarea id="sqlQuery" class="form-control" rows="2" placeholder="Entrez votre requête SQL ici"></textarea>
        </div>
        <button id="executeSQL" class="btn btn-primary">Exécuter la requête</button>
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
              <!-- Première colonne par défaut (index 0) -->
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

  <!-- Modale d'édition de table (renommage) -->
  <div class="modal fade" id="editTableModal" tabindex="-1" aria-labelledby="editTableModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="editTableForm">
          <div class="modal-header">
            <h5 class="modal-title" id="editTableModalLabel">Modifier la table</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="old_name" name="old_name" value="">
            <div class="mb-3">
              <label for="new_name" class="form-label">Nouveau nom</label>
              <input type="text" id="new_name" name="new_name" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modale pour l'ajout d'une nouvelle ligne -->
  <div class="modal fade" id="newRowModal" tabindex="-1" aria-labelledby="newRowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="newRowForm">
          <div class="modal-header">
            <h5 class="modal-title" id="newRowModalLabel">Ajouter une nouvelle ligne</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body" id="newRowBody">
            <!-- Le formulaire sera généré dynamiquement via AJAX selon la structure de la table -->
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ajouter</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modale pour l'édition d'une ligne -->
  <div class="modal fade" id="editRowModal" tabindex="-1" aria-labelledby="editRowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="editRowForm">
          <div class="modal-header">
            <h5 class="modal-title" id="editRowModalLabel">Modifier la ligne</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body" id="editRowBody">
            <!-- Le formulaire d'édition sera généré dynamiquement -->
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
</body>
</html>
