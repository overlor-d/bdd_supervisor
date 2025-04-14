# 🐳 MySQL Manager - Gestionnaire Multi-Instances Docker

Un outil en ligne de commande complet pour créer, déployer, superviser et sauvegarder dynamiquement des instances MySQL dans des conteneurs Docker. Idéal pour les devs, les environnements de test ou la gestion multi-projets.

---

## ✅ Fonctionnalités principales

- Initialisation automatique de l'environnement local
- Gestion centralisée via une base SQLite (plus de fichiers `.env` partout)
- Création, démarrage, arrêt, suppression, sauvegarde de bases
- Attribution automatique de ports entre 3300 et 3350
- Conteneurs, ports et volumes totalement isolés
- Sauvegardes SQL générées dans `~/.mysql-manager/backups`

---

## 📁 Structure du projet

```bash
mysql-manager/
├── core/                     # Scripts principaux
│   ├── supervisor.sh         # CLI principale (point d'entrée)
│   ├── manage_instance.sh    # Gestion d'une instance à partir de la BDD
│   ├── utils.sh              # Fonctions partagées
│   └── schema.sql            # Schéma de la base SQLite
│
├── templates/
│   └── docker-compose.yml    # Docker Compose de base
│
├── install/
│   └── init_packages.sh      # Installation des dépendances (Docker, jq...)
│
├── data/
│   └── db.sqlite3            # Base locale (générée par `init`)
│
├── backups/                  # Contient les exports SQL (.sql)
└── README.md
```

---

## 🛠 Installation et initialisation

### 1. Cloner le projet
```bash
git clone https://github.com/ton_user/mysql-manager.git
cd mysql-manager
```

### 2. Lancer l'initialisation
```bash
./core/supervisor.sh init
```
Cette commande :
- Vérifie les dépendances (`docker`, `jq`, `sqlite3`)
- Crée `~/.mysql-manager/` et la base SQLite
- Te propose de créer une première instance

---

## 🚀 Commandes disponibles

```bash
./core/supervisor.sh init          # Initialise le projet
./core/supervisor.sh create        # Crée une nouvelle instance (interactif)
./core/supervisor.sh list          # Liste toutes les instances gérées
./core/supervisor.sh start <nom>  # Démarre une instance
./core/supervisor.sh stop <nom>   # Arrête une instance
./core/supervisor.sh logs <nom>   # Affiche les logs du conteneur
./core/supervisor.sh status <nom> # Affiche l'état du conteneur
./core/supervisor.sh backup <nom> # Sauvegarde la base en SQL
./core/supervisor.sh purge <nom>  # Supprime une instance (conteneur + volume + métadonnées)
```

---

## 🔒 Sécurité

- Toutes les informations sont stockées dans `~/.mysql-manager/db.sqlite3`
- Aucun mot de passe en clair dans des fichiers `env` suivis par Git
- Connexion uniquement en local ou via VPN / SSH

---

## 🧪 Exemple de test rapide

```bash
./core/supervisor.sh create
./core/supervisor.sh start nom-instance
./core/supervisor.sh list
mysql -u user -p -h 127.0.0.1 -P <port>
./core/supervisor.sh backup nom-instance
./core/supervisor.sh stop nom-instance
./core/supervisor.sh purge nom-instance
```

---

## 🧼 .gitignore recommandé

```gitignore
.env
*.env
*.override.yml
*.log
*.sqlite3
*.sql
backups/
~/.mysql-manager/
```

---

## 💡 Prochaine évolution

- Intégration d'une API Flask ou interface web
- Export/Import d'instances
- Ajout de support PostgreSQL

---

## 🧑‍💻 Auteur
Projet développé par **@over** — libre d’utilisation et d’amélioration !

