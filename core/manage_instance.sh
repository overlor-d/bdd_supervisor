#!/bin/bash

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/utils.sh"

# Vérifie que le dossier de l'instance existe
if [ -z "$2" ] || [ ! -d "$2" ]; then
    echo "Erreur : dossier d'instance invalide ou manquant."
    echo "Usage: $0 <commande> <instance_dir>"
    exit 1
fi

COMMAND="$1"
INSTANCE_DIR="$2"
ENV_FILE="${INSTANCE_DIR}/.env"
META_FILE="${INSTANCE_DIR}/.meta.json"
BASE_COMPOSE="${SCRIPT_DIR}/../templates/docker-compose.yml"
OVERRIDE_COMPOSE="${INSTANCE_DIR}/docker-compose.override.yml"

# Charge les infos nécessaires
DB_NAME=$(load_env_var "$ENV_FILE" "MYSQL_DATABASE")
CONTAINER_NAME=$(get_container_name "$DB_NAME")
VOLUME_NAME=$(get_volume_name "$DB_NAME")

# Génère docker-compose.override.yml
function generate_override() {
    local port
    if [ -f "$META_FILE" ]; then
        port=$(read_meta_value "$INSTANCE_DIR" "port")
    else
        port=$(find_free_port)
        create_meta_json "$INSTANCE_DIR" "$port" "stopped"
    fi

    cat > "$OVERRIDE_COMPOSE" <<EOF
services:
  mysql:
    container_name: ${CONTAINER_NAME}
    ports:
      - "${port}:3306"
    volumes:
      - ${VOLUME_NAME}:/var/lib/mysql

volumes:
  ${VOLUME_NAME}:
EOF
}

function start_instance() {
    generate_override
    docker compose --env-file "$ENV_FILE" -f "$BASE_COMPOSE" -f "$OVERRIDE_COMPOSE" up -d
    create_meta_json "$INSTANCE_DIR" "$(read_meta_value "$INSTANCE_DIR" "port")" "running"
    echo "Instance '${DB_NAME}' démarrée."
}

function stop_instance() {
    docker compose --env-file "$ENV_FILE" -f "$BASE_COMPOSE" -f "$OVERRIDE_COMPOSE" down
    create_meta_json "$INSTANCE_DIR" "$(read_meta_value "$INSTANCE_DIR" "port")" "stopped"
    echo "Instance '${DB_NAME}' arrêtée."
}

function purge_instance() {
    read -p "Supprimer définitivement l'instance '${DB_NAME}' ? (y/N): " confirm
    if [[ "$confirm" != "y" ]]; then
        echo "Annulé."
        exit 0
    fi

    docker compose --env-file "$ENV_FILE" -f "$BASE_COMPOSE" -f "$OVERRIDE_COMPOSE" down -v
    rm -f "$OVERRIDE_COMPOSE" "$META_FILE"
    echo "Instance '${DB_NAME}' supprimée (conteneur + volume)."
}

function status_instance() {
    docker ps --filter "name=${CONTAINER_NAME}"
}

function logs_instance() {
    docker logs -f "$CONTAINER_NAME"
}

function backup_instance() {
    local USER PASS
    USER=$(load_env_var "$ENV_FILE" "MYSQL_USER")
    PASS=$(load_env_var "$ENV_FILE" "MYSQL_PASSWORD")
    local BACKUP_DIR="./backups"
    mkdir -p "$BACKUP_DIR"
    local FILE="${BACKUP_DIR}/${DB_NAME}_$(date +%F_%H-%M).sql"
    docker exec "$CONTAINER_NAME" sh -c \
        "mysqldump -u$USER -p$PASS $DB_NAME" > "$FILE"
    echo "Backup sauvegardé : $FILE"
}

case "$COMMAND" in
  start)
    start_instance
    ;;
  stop)
    stop_instance
    ;;
  purge)
    purge_instance
    ;;
  status)
    status_instance
    ;;
  logs)
    logs_instance
    ;;
  backup)
    backup_instance
    ;;
  *)
    echo "Commande inconnue : $COMMAND"
    echo "Usage: $0 {start|stop|purge|status|logs|backup} <instance_dir>"
    exit 1
esac

