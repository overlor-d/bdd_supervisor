#!/bin/bash

# Fichier: manage.sh
# Ce script permet de gérer les conteneurs Docker via docker-compose.
# Usage: ./manage.sh {start|stop|restart|status|logs}

COMPOSE_FILE="docker-compose.yml"

function start() {
  echo "Démarrage des conteneurs..."
  docker-compose -f "$COMPOSE_FILE" up -d
  echo "Les conteneurs ont été démarrés."
}

function stop() {
  echo "Arrêt des conteneurs..."
  docker-compose -f "$COMPOSE_FILE" down
  echo "Les conteneurs ont été arrêtés."
}

function restart() {
  echo "Redémarrage des conteneurs..."
  stop
  start
}

function status() {
  echo "Statut des conteneurs:"
  docker-compose -f "$COMPOSE_FILE" ps
}

function logs() {
  echo "Affichage des logs (Ctrl+C pour quitter):"
  docker-compose -f "$COMPOSE_FILE" logs -f
}

function usage() {
  echo "Usage: $0 {start|stop|restart|status|logs}"
  exit 1
}

if [ $# -ne 1 ]; then
  usage
fi

case "$1" in
  start)
    start
    ;;
  stop)
    stop
    ;;
  restart)
    restart
    ;;
  status)
    status
    ;;
  logs)
    logs
    ;;
  *)
    usage
    ;;
esac

