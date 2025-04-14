#!/bin/bash

# Utilitaires pour gestion d'instances MySQL Dockerisées

# Lire une variable dans un fichier .env
# $1 = nom du fichier .env
# $2 = nom de la variable
function load_env_var() {
    local env_file="$1"
    local var_name="$2"
    grep "^${var_name}=" "$env_file" | cut -d '=' -f2-
}

# Générer un port libre entre 3300 et 3350
function find_free_port() {
    for port in $(seq 3300 3350); do
        if ! lsof -iTCP:$port -sTCP:LISTEN >/dev/null 2>&1; then
            echo "$port"
            return
        fi
    done
    echo "Aucun port libre disponible entre 3300 et 3350." >&2
    exit 1
}

# Générer un nom de conteneur Docker à partir du nom de la base
function get_container_name() {
    local db_name="$1"
    echo "mysql_db_${db_name}"
}

# Générer un nom de volume Docker à partir du nom de la base
function get_volume_name() {
    local db_name="$1"
    echo "mysql_data_${db_name}"
}

# Créer le fichier .meta.json pour une instance
# $1 = dossier instance
# $2 = port
# $3 = status (running, stopped, etc.)
function create_meta_json() {
    local instance_dir="$1"
    local env_file="${instance_dir}/.env"
    local port="$2"
    local status="$3"

    local db_name
    db_name=$(load_env_var "$env_file" "MYSQL_DATABASE")
    local container_name
    container_name=$(get_container_name "$db_name")
    local volume_name
    volume_name=$(get_volume_name "$db_name")

    cat > "${instance_dir}/.meta.json" <<EOF
{
  "name": "$db_name",
  "port": $port,
  "container": "$container_name",
  "volume": "$volume_name",
  "status": "$status"
}
EOF
}

# Lire une valeur depuis un .meta.json
# $1 = instance_dir
# $2 = clé à lire
function read_meta_value() {
    local instance_dir="$1"
    local key="$2"
    jq -r ".${key}" "${instance_dir}/.meta.json"
}

# Vérifier si une instance existe
# $1 = nom de l'instance
function instance_exists() {
    local name="$1"
    if [ -d "./instances/$name" ] && [ -f "./instances/$name/.env" ]; then
        return 0
    else
        return 1
    fi
}

