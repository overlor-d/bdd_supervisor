#!/bin/bash

set -e

echo "[+] Vérification et installation des dépendances..."

function install_package() {
    if ! command -v "$1" &>/dev/null; then
        echo "[-] $1 non trouvé. Installation..."
        sudo apt update && sudo apt install -y "$2"
    else
        echo "[OK] $1 déjà installé."
    fi
}

install_package docker docker.io
install_package jq jq
install_package sqlite3 sqlite3

echo "[✓] Tous les paquets sont installés."
