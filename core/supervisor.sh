#!/bin/bash

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/utils.sh"

INSTANCE_ROOT="${SCRIPT_DIR}/../instances"
MANAGER="${SCRIPT_DIR}/manage_instance.sh"

function create_instance() {
    local name="$1"
    local instance_dir="${INSTANCE_ROOT}/${name}"

    if [ -d "$instance_dir" ]; then
        echo "Une instance avec ce nom existe déjà."
        exit 1
    fi

    mkdir -p "$instance_dir"

    echo "Création de l'instance '$name'"

    read -p "Mot de passe ROOT MySQL : " root_pass
    read -p "Nom de la base : " db_name
    read -p "Utilisateur MySQL : " db_user
    read -p "Mot de passe utilisateur : " db_pass

    cat > "${instance_dir}/.env" <<EOF
MYSQL_ROOT_PASSWORD=$root_pass
MYSQL_DATABASE=$db_name
MYSQL_USER=$db_user
MYSQL_PASSWORD=$db_pass
EOF

    local port
    port=$(find_free_port)
    create_meta_json "$instance_dir" "$port" "stopped"

    echo "Instance '$name' créée avec succès."
    echo "Port alloué : $port"
    echo "Utilisez : ./core/supervisor.sh start $name"
}

function list_instances() {
    echo "📦 Instances disponibles :"
    for dir in "$INSTANCE_ROOT"/*; do
        [ -d "$dir" ] || continue
        name=$(basename "$dir")
        if [ -f "$dir/.meta.json" ]; then
            port=$(read_meta_value "$dir" "port")
            status=$(read_meta_value "$dir" "status")
            container=$(read_meta_value "$dir" "container")
            echo "- $name | port $port | $status | container: $container"
        else
            echo "- $name | (non initialisée)"
        fi
    done
}

function delegate_command() {
    local action="$1"
    local name="$2"
    local path="${INSTANCE_ROOT}/${name}"

    if ! instance_exists "$name"; then
        echo "Instance '$name' introuvable."
        exit 1
    fi

    "$MANAGER" "$action" "$path"
}

function info_instance() {
    local name="$1"
    local path="${INSTANCE_ROOT}/${name}"

    if ! instance_exists "$name"; then
        echo "Instance '$name' non trouvée."
        exit 1
    fi

    jq . "${path}/.meta.json"
}

function show_help() {
    cat <<EOF
Usage : $0 <commande> [nom_instance]

Commandes disponibles :
  create <name>       Créer une nouvelle instance
  list                Lister toutes les instances
  start <name>        Démarrer une instance
  stop <name>         Arrêter une instance
  purge <name>        Supprimer une instance (conteneur + volume)
  backup <name>       Sauvegarder une instance
  logs <name>         Afficher les logs de l'instance
  info <name>         Afficher les détails d'une instance
  help                Afficher cette aide
EOF
}

# Dispatcher
case "$1" in
  create)
    create_instance "$2"
    ;;
  list)
    list_instances
    ;;
  start|stop|purge|backup|logs)
    delegate_command "$1" "$2"
    ;;
  info)
    info_instance "$2"
    ;;
  help|"")
    show_help
    ;;
  *)
    echo "Commande inconnue : $1"
    show_help
    exit 1
    ;;
esac

