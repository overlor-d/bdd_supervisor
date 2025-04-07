#!/bin/bash

set -e

ENV_FILE=".env"
BASE_COMPOSE="docker-compose.yml"
OVERRIDE_COMPOSE="docker-compose.override.yml"

function load_env_var() {
    VAR_NAME="$1"
    VALUE=$(grep "^$VAR_NAME=" "$ENV_FILE" | cut -d '=' -f2-)
    echo "$VALUE"
}

function generate_override_compose() {
    DB_NAME=$(load_env_var "MYSQL_DATABASE")
    PORT=$(generate_port_from_name "$DB_NAME")
    VOLUME_NAME="mysql_data_${DB_NAME}"
    CONTAINER_NAME="mysql_db_${DB_NAME}"

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
}

function generate_port_from_name() {
    local name="$1"
    local hash=$(echo -n "$name" | md5sum | cut -c1-4)
    local port=$(( 3300 + 0x${hash:0:2} % 50 )) # Génère un port entre 3300-3350
    echo $port
}

function start() {
    generate_override_compose
    docker compose --env-file "$ENV_FILE" -f "$BASE_COMPOSE" -f "$OVERRIDE_COMPOSE" up -d
}

function stop() {
    generate_override_compose
    docker compose --env-file "$ENV_FILE" -f "$BASE_COMPOSE" -f "$OVERRIDE_COMPOSE" down
}

function purge() {
    generate_override_compose
    docker compose --env-file "$ENV_FILE" -f "$BASE_COMPOSE" -f "$OVERRIDE_COMPOSE" down -v
    rm -f "$OVERRIDE_COMPOSE"
}

function status() {
    docker ps | grep "mysql_db_"
}

function logs() {
    DB_NAME=$(load_env_var "MYSQL_DATABASE")
    docker logs -f "mysql_db_${DB_NAME}"
}

case "$1" in
  start)
    start
    ;;
  stop)
    stop
    ;;
  purge)
    purge
    ;;
  status)
    status
    ;;
  logs)
    logs
    ;;
  *)
    echo "Usage: $0 {start|stop|purge|status|logs}"
    exit 1
esac
