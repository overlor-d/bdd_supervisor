# 🐳 MySQL Supervisor — Gestion dynamique d'instances MySQL via Docker

Un outil en ligne de commande conçu pour créer, gérer, superviser et supprimer facilement des bases de données MySQL dans des conteneurs Docker, sans interface web. Idéal pour les développeurs ou les équipes techniques souhaitant isoler et manipuler plusieurs bases sans conflits.

---

## ✅ Fonctionnalités

- Déploiement d'instances MySQL isolées via Docker
- Attribution dynamique de ports (pas de conflits)
- Sauvegarde et purge complète d'instances
- Stockage local des configurations via SQLite
- Aucune interface exposée : usage via tunnel/VPN
- Sécurité locale : mots de passe, volumes, droits POSIX
- Compatible Linux, support partiel Mac (Docker Desktop)

---

## 📂 Arborescence du projet

```
bdd_supervisor/
├── core/                      # Scripts principaux
│   ├── supervisor.sh         # Point d'entrée CLI
│   ├── manage_instance.sh    # Logique par instance
│   └── utils.sh              # Fonctions utilitaires
├── install/
│   └── init_packages.sh      # Script optionnel pour dépendances
├── templates/
│   └── docker-compose.yml    # Template de base MySQL
├── README.md                 # Documentation
```

---

## ⚙️ Installation & Initialisation

```bash
./core/supervisor.sh init
```
- Crée le dossier `~/.mysql-manager`
- Initialise une base SQLite
- Propose de créer une première instance (nom, mots de passe, etc.)

---

## 🛠️ Commandes disponibles

```bash
./core/supervisor.sh create          # Crée une nouvelle instance
./core/supervisor.sh start <nom>     # Démarre une instance
./core/supervisor.sh stop <nom>      # Stoppe une instance
./core/supervisor.sh purge <nom>     # Supprime instance + volume + config
./core/supervisor.sh status <nom>    # Affiche l'état Docker d'une instance
./core/supervisor.sh logs <nom>      # Affiche les logs live
./core/supervisor.sh backup <nom>    # Effectue un export SQL de la base
./core/supervisor.sh list            # Affiche les instances connues
```

---

## 🔐 Sécurité

- Toutes les données sont locales : pas d'exposition réseau
- Accès à la base uniquement via localhost ou tunnel SSH/VPN
- Aucune donnée sensible dans les fichiers publics
- Permissions strictes appliquées sur chaque fichier créé

---

## 💾 Sauvegardes

```bash
./core/supervisor.sh backup <nom>
```
- Génère un dump `.sql` dans `~/.mysql-manager/backups/`
- Format horodaté : `nom_YYYY-MM-DD_HH-MM.sql`

---

## 📌 Dépendances

- Docker & Docker Compose
- SQLite3 (installé par défaut sur Linux)
- Bash (>= 4)

---

## 💡 Astuces & Bonnes pratiques

- Utilisez un tunnel SSH ou VPN pour accéder à vos bases à distance
- Intégrez vos backups dans une crontab pour automatiser
- Évitez de supprimer le fichier SQLite sauf si vous repartez de zéro
- Tous les ports sont entre 3300-3350 : vérifiez avec `docker ps`

---

## 🚧 Limitations connues

- Non multi-utilisateur (tout est stocké dans `~/.mysql-manager`)
- Pas encore d'interface graphique ou d'API
- Les erreurs Docker ne sont pas toutes capturées proprement
- Ne gère que MySQL (MariaDB potentiellement compatible)

---

## 📈 Roadmap

- [ ] Interface web Flask optionnelle
- [ ] Support PostgreSQL / Mongo
- [ ] Amélioration des messages d'erreur
- [ ] Export / import d'une configuration
- [ ] Commande `doctor` pour diagnostic complet

---

## 🧪 Tests

Effectuez ces commandes dans l'ordre pour valider une instance :

```bash
./core/supervisor.sh create
./core/supervisor.sh start <nom>
./core/supervisor.sh status <nom>
./core/supervisor.sh logs <nom>
./core/supervisor.sh backup <nom>
./core/supervisor.sh stop <nom>
./core/supervisor.sh purge <nom>
```

---

## 🧠 Contributeurs

Projet développé pour simplifier la gestion d'environnements MySQL temporaires, reproductibles et isolés via Docker.

---

Pour toute suggestion ou bug : ouvrez une issue ou contactez le développeur principal.

