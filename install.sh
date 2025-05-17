#!/bin/bash

set -e
tput civis

echo "[1] Vérification des packages installés"
echo

cleanup() {
    echo "Interruption détectée. Nettoyage en cours..."
    tput cnorm
    exit 1
}

trap cleanup INT

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

spinner() {
    local pid=$1
    local delay=0.1
    local spinstr='|/-\'
    while kill -0 $pid 2>/dev/null; do
        local temp=${spinstr#?}
        printf "[%c] Installation en cours\r" "$spinstr"
        spinstr=$temp${spinstr%"$temp"}
        sleep $delay
    done
    printf "\r"
    tput el
}

echo "-------------------------------"
while read -r pkg; do
    [[ -z "$pkg" || "$pkg" =~ ^# ]] && continue

    if ! dpkg -s "$pkg" &>/dev/null; then
        sudo DEBIAN_FRONTEND=noninteractive apt-get install -y "$pkg" > /dev/null 2>&1 &
        pid=$!
        spinner "$pid"
        wait $pid
        if [ $? -eq 0 ]; then
            echo "[✓] Package installé : $pkg"
        else
            echo "[✗] Échec de l'installation : $pkg" >&2
            tput cnorm
            exit 1
        fi
    else
        echo "[✓] Déjà installé : $pkg"
    fi
done < "$SCRIPT_DIR/var/dependances.list"

echo "-------------------------------"

echo
echo "[1] Tous les packages ont été traités"
echo 
echo "[2] Installation du module sur le système"




tput cnorm