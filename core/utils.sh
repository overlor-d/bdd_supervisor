#!/bin/bash

set -e

INSTALL_DIR="$HOME/.mysql-manager"
DB_FILE="$INSTALL_DIR/db.sqlite3"

# Lire une valeur depuis SQLite
function get_instance_value() {
    local name="$1"
    local field="$2"
    sqlite3 "$DB_FILE" "SELECT $field FROM instances WHERE name = '$name';"
}

# Vérifie l'existence d'une instance
function instance_exists() {
    local name="$1"
    local result
    result=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM instances WHERE name = '$name';")
    [[ "$result" -ne 0 ]]
}

# Liste les ports utilisés
function get_used_ports() {
    sqlite3 "$DB_FILE" "SELECT port FROM instances;"
}

# Port libre entre 3300–3350
function find_free_port() {
    local used_ports=$(get_used_ports)
    for port in $(seq 3300 3350); do
        if ! echo "$used_ports" | grep -q "^$port$"; then
            echo "$port"
            return
        fi
    done
    echo "Aucun port libre disponible entre 3300 et 3350." >&2
    exit 1
}

# Liste toutes les instances
function list_instances() {
    sqlite3 "$DB_FILE" <<EOF
.headers on
.mode column
SELECT name, port, status, container_name FROM instances ORDER BY name;
EOF
}

# Chemins utiles
function get_override_path() {
    echo "$INSTALL_DIR/instances/$1/docker-compose.override.yml"
}
function get_env_path() {
    echo "$INSTALL_DIR/instances/$1/.env"
}
