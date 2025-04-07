# MySQL Server Manager (via Docker)

## Commandes

- `./manage_db.sh start` – Lance le conteneur MySQL
- `./manage_db.sh stop` – Arrête le conteneur
- `./manage_db.sh purge` – Supprime le conteneur + les volumes
- `./manage_db.sh status` – Vérifie l'état du conteneur
- `./manage_db.sh logs` – Affiche les logs MySQL

## Configuration

Modifier le fichier `.env` avec :

- `MYSQL_ROOT_PASSWORD`
- `MYSQL_DATABASE`
- `MYSQL_USER`
- `MYSQL_PASSWORD`
