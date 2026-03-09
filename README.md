# MySQL Supervisor — Gestion dynamique d'instances MySQL via Docker

Un outil en ligne de commande conçu pour créer, gérer, superviser et supprimer facilement des bases de données MySQL dans des conteneurs Docker, sans interface web. Idéal pour les développeurs ou les équipes techniques souhaitant isoler et manipuler plusieurs bases sans conflits.

---

## Fonctionnalités

- Déploiement d'instances MySQL isolées via Docker
- Attribution dynamique de ports (pas de conflits)
- Sauvegarde et purge complète d'instances
- Stockage local des configurations via SQLite
- Aucune interface exposée : usage via tunnel/VPN
- Sécurité locale : mots de passe, volumes, droits POSIX
- Compatible Linux, support partiel Mac (Docker Desktop)

---

## Commandes disponibles

```bash
python -m bdd_supervisor init            # Initialise l'environnement
python -m bdd_supervisor create          # Crée une nouvelle instance
python -m bdd_supervisor start <nom>     # Démarre une instance
python -m bdd_supervisor stop <nom>      # Stoppe une instance
python -m bdd_supervisor purge <nom>     # Supprime instance + volume + config
python -m bdd_supervisor status <nom>    # Affiche l'état Docker d'une instance
python -m bdd_supervisor logs <nom>      # Affiche les logs live
python -m bdd_supervisor backup <nom>    # Effectue un export SQL de la base
python -m bdd_supervisor list            # Affiche les instances connues
```

---

## Structure du projet

```
bdd_supervisor/
    __init__.py
    __main__.py
    cli.py
    core/
        manage_instance.sh
        supervisor.sh
        utils.sh
        schema.sql
    install/
        init_packages.sh
    templates/
        docker-compose.yml
```

---

## Sécurité

- Toutes les données sont locales : pas d'exposition réseau
- Accès à la base uniquement via localhost ou tunnel SSH/VPN
- Aucune donnée sensible dans les fichiers publics
- Permissions strictes appliquées sur chaque fichier créé

---

## Sauvegardes

```bash
python -m bdd_supervisor backup <nom>
```
- Génère un dump `.sql` dans `~/.mysql-manager/backups/`
- Format horodaté : `nom_YYYY-MM-DD_HH-MM.sql`

---

## Dépendances

- Docker & Docker Compose
- SQLite3 (installé par défaut sur Linux)
- Bash (>= 4)

---

## Astuces & Bonnes pratiques

- Utilisez un tunnel SSH ou VPN pour accéder à vos bases à distance
- Évitez de supprimer le fichier SQLite sauf si vous repartez de zéro
- Tous les ports sont entre 3300-3350 : vérifiez avec `docker ps`

---

## Limitations

- Non multi-utilisateur (tout est stocké dans `~/.mysql-manager`)
- Pas encore d'interface graphique ou d'API
- Les erreurs Docker ne sont pas toutes capturées proprement
- Ne gère que MySQL (MariaDB potentiellement compatible)

---

## Tests

Effectuez ces commandes dans l'ordre pour valider une instance :

```bash
python -m bdd_supervisor create
python -m bdd_supervisor start <nom>
python -m bdd_supervisor status <nom>
python -m bdd_supervisor logs <nom>
python -m bdd_supervisor backup <nom>
python -m bdd_supervisor stop <nom>
python -m bdd_supervisor purge <nom>
```

---

## Contributeurs

Projet développé pour simplifier la gestion d'environnements MySQL temporaires, reproductibles et isolés via Docker.

---

Pour toute suggestion ou bug : ouvrez une issue ou contactez le développeur principal.

