#!/bin/bash

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$SCRIPT_DIR/.."
INSTALL_DIR="$HOME/.mysql-manager"
DB_FILE="$INSTALL_DIR/db.sqlite3"
TEMPLATE_COMPOSE="$BASE_DIR/templates/docker-compose.yml"

source "$SCRIPT_DIR/utils.sh"

COMMAND="$1"
INSTANCE_NAME="$2"

if [ -z "$INSTANCE_NAME" ]; then
    echo "Usage: $0 {start|stop|purge|status|logs|backup} <instance_name>"
    exit 1
fi

# Récupère les infos depuis SQLite
read_sqlite_value() {
    local field="$1"
    sqlite3 "$DB_FILE" "SELECT $field FROM instances WHERE name = '$INSTANCE_NAME';"
}

DB_NAME=$(read_sqlite_value "db_name")
DB_USER=$(read_sqlite_value "db_user")
DB_PASS=$(read_sqlite_value "db_password")
ROOT_PASS=$(read_sqlite_value "root_password")
PORT=$(read_sqlite_value "port")
CONTAINER_NAME=$(read_sqlite_value "container_name")
VOLUME_NAME=$(read_sqlite_value "volume_name")

INSTANCE_DIR="$INSTALL_DIR/instances/$INSTANCE_NAME"
mkdir -p "$INSTANCE_DIR"
chmod 700 "$INSTANCE_DIR"
chown "$USER:$USER" "$INSTANCE_DIR"

OVERRIDE_COMPOSE="$INSTANCE_DIR/docker-compose.override.yml"
ENV_FILE="$INSTANCE_DIR/.env"

# Prépare le fichier .env avec les bons droits
rm -f "$ENV_FILE"
touch "$ENV_FILE"
chmod 600 "$ENV_FILE"
chown "$USER:$USER" "$ENV_FILE"

# Génère le fichier .env pour docker-compose
cat > "$ENV_FILE" <<EOF
MYSQL_ROOT_PASSWORD=$ROOT_PASS
MYSQL_DATABASE=$DB_NAME
MYSQL_USER=$DB_USER
MYSQL_PASSWORD=$DB_PASS
EOF

# Génère docker-compose.override.yml
generate_override() {
    cat > "$OVERRIDE_COMPOSE" <<EOF
services:
  mysql:
    container_name: ${CONTAINER_NAME}
    ports:
      - "${PORT}:3306"
    volumes:
      - ${VOLUME_NAME}:/var/lib/mysql

volumes:
  ${VOLUME_NAME}:
EOF

    chmod 644 "$OVERRIDE_COMPOSE"
    chown "$USER:$USER" "$OVERRIDE_COMPOSE"
}

start_instance() {
    generate_override

    export $(grep -v '^#' "$ENV_FILE" | xargs)

    TMP_OVERRIDE="$BASE_DIR/override_${INSTANCE_NAME}.yml"
    TMP_ENV="$BASE_DIR/.env_${INSTANCE_NAME}"

    cp "$OVERRIDE_COMPOSE" "$TMP_OVERRIDE"
    cp "$ENV_FILE" "$TMP_ENV"

    docker compose -p "$INSTANCE_NAME" --env-file "$TMP_ENV" -f "$TEMPLATE_COMPOSE" -f "$TMP_OVERRIDE" up -d

    rm -f "$TMP_OVERRIDE" "$TMP_ENV"

    sqlite3 "$DB_FILE" "UPDATE instances SET status = 'running' WHERE name = '$INSTANCE_NAME';"
    echo "[\u2713] Instance '$INSTANCE_NAME' démarrée."
}

stop_instance() {
    generate_override

    TMP_OVERRIDE="$BASE_DIR/override_${INSTANCE_NAME}.yml"
    TMP_ENV="$BASE_DIR/.env_${INSTANCE_NAME}"

    cp "$OVERRIDE_COMPOSE" "$TMP_OVERRIDE"
    cp "$ENV_FILE" "$TMP_ENV"

    docker compose -p "$INSTANCE_NAME" --env-file "$TMP_ENV" -f "$TEMPLATE_COMPOSE" -f "$TMP_OVERRIDE" down

    rm -f "$TMP_OVERRIDE" "$TMP_ENV"

    sqlite3 "$DB_FILE" "UPDATE instances SET status = 'stopped' WHERE name = '$INSTANCE_NAME';"
    echo "Instance '$INSTANCE_NAME' arrêtée."
}

purge_instance() {
    read -p "Supprimer définitivement l'instance '$INSTANCE_NAME' ? (y/N): " confirm
    if [[ "$confirm" != "y" ]]; then
        echo "Annulé."
        exit 0
    fi

    generate_override

    TMP_OVERRIDE="$BASE_DIR/override_${INSTANCE_NAME}.yml"
    TMP_ENV="$BASE_DIR/.env_${INSTANCE_NAME}"

    cp "$OVERRIDE_COMPOSE" "$TMP_OVERRIDE"
    cp "$ENV_FILE" "$TMP_ENV"

    docker compose -p "$INSTANCE_NAME" --env-file "$TMP_ENV" -f "$TEMPLATE_COMPOSE" -f "$TMP_OVERRIDE" down -v

    rm -f "$TMP_OVERRIDE" "$TMP_ENV"
    rm -rf "$INSTANCE_DIR"

    sqlite3 "$DB_FILE" "DELETE FROM instances WHERE name = '$INSTANCE_NAME';"
    echo "Instance '$INSTANCE_NAME' supprimée (conteneur + volume)."
}

status_instance() {
    docker ps --filter "name=${CONTAINER_NAME}"
}

logs_instance() {
    docker logs -f "$CONTAINER_NAME"
}

backup_instance() {
    BACKUP_DIR="$INSTALL_DIR/backups"
    mkdir -p "$BACKUP_DIR"
    local FILE="${BACKUP_DIR}/${DB_NAME}_$(date +%F_%H-%M).sql"
    docker exec "$CONTAINER_NAME" sh -c \
        "mysqldump -u$DB_USER -p$DB_PASS $DB_NAME" > "$FILE"
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
    echo "Usage: $0 {start|stop|purge|status|logs|backup} <instance_name>"
    exit 1
    ;;
esac

