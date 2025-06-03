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

# -------------------------
# Init Supervisor
# -------------------------
function init_project() {
    echo "[+] Initialisation de l'environnement MySQL Manager..."

    for cmd in docker jq sqlite3; do
        if ! command -v $cmd &>/dev/null; then
            echo "La commande '$cmd' est manquante. Veuillez l'installer."
            exit 1
        fi
    done

    mkdir -p "$INSTALL_DIR"

    if [ ! -f "$DB_FILE" ]; then
        echo "[+] Création de la base SQLite..."
        sqlite3 "$DB_FILE" < "$SCRIPT_DIR/schema.sql"
    else
        echo "[i] Base existante détectée."
    fi

    echo "[✓] Initialisation terminée."

    read -p "Créer une première instance maintenant ? (y/n): " confirm
    if [[ "$confirm" == "y" ]]; then
        create_instance_prompt
    fi
}

# -------------------------
# Création interactive
# -------------------------
function create_instance_prompt() {
    read -p "Nom de l'instance (unique) : " name
    read -p "Nom de la base MySQL : " dbname
    read -p "Utilisateur MySQL : " dbuser
    read -p "Mot de passe utilisateur : " dbpass
    read -p "Mot de passe ROOT MySQL : " rootpass

    port=$(find_free_port)
    container="mysql_db_${name}"
    volume="mysql_data_${name}"

    sqlite3 "$DB_FILE" "INSERT INTO instances (name, db_name, db_user, db_password, root_password, port, container_name, volume_name, status)
    VALUES ('$name', '$dbname', '$dbuser', '$dbpass', '$rootpass', $port, '$container', '$volume', 'stopped');"

    echo "[+] Instance '$name' ajoutée. Port assigné : $port"
}

# -------------------------
# Delegation
# -------------------------
function delegate() {
    if ! instance_exists "$INSTANCE_NAME"; then
        echo "Instance '$INSTANCE_NAME' introuvable."
        exit 1
    fi
    "$SCRIPT_DIR/manage_instance.sh" "$COMMAND" "$INSTANCE_NAME"
}

# -------------------------
# Affiche la liste
# -------------------------
function list_instances_cmd() {
    list_instances
}

# -------------------------
# HELP
# -------------------------
function show_help() {
    echo "Usage : $0 <commande> [instance]"
    echo ""
    echo "Commandes disponibles :"
    echo "  init                 Initialise l'environnement"
    echo "  create               Crée une nouvelle instance"
    echo "  list                 Affiche la liste des instances"
    echo "  start <name>         Démarre une instance"
    echo "  stop <name>          Arrête une instance"
    echo "  purge <name>         Supprime une instance"
    echo "  backup <name>        Sauvegarde une base"
    echo "  logs <name>          Affiche les logs"
    echo "  status <name>        Affiche l'état du conteneur"
}

# -------------------------
# Dispatcher
# -------------------------
case "$COMMAND" in
  init)
    init_project
    ;;
  create)
    create_instance_prompt
    ;;
  list)
    list_instances_cmd
    ;;
  start|stop|purge|backup|status|logs)
    delegate
    ;;
  *)
    show_help
    ;;
esac
