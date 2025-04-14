# 🐳 MySQL Dynamic Manager – Superviseur multi-instances

Un outil shell simple et évolutif pour créer, superviser et gérer plusieurs instances de bases de données **MySQL sous Docker**, avec génération automatique de ports, fichiers de configuration et compatibilité future avec une API web (Flask, etc.).

---

## ✅ Objectif

Gérer dynamiquement plusieurs bases MySQL sur un même hôte, sans conflits, sans fichiers manuels à modifier, et sans interface graphique. Idéal pour le développement, les tests, ou des environnements isolés.

---

## 📦 Fonctionnalités principales

- Création automatisée d’instances MySQL (port, container, volume, fichiers)
- Isolation parfaite entre les bases
- Commandes shell simples et documentées
- Structure de fichiers prête pour une intégration future en API (Flask)
- Sauvegardes SQL faciles
- Aucun service exposé publiquement (accès par VPN ou tunnel SSH)

---

## 📁 Structure du projet

```
mysql-manager/
├── core/                        # Scripts de gestion
│   ├── manage_instance.sh       # Gère une instance unique
│   ├── supervisor.sh            # Orchestrateur multi-instance
│   └── utils.sh                 # Fonctions communes (env, ports...)
│
├── instances/                   # Une base = un dossier avec sa config
│   └── mydb/
│       ├── .env
│       └── .meta.json
│
├── backups/                     # Dumps SQL horodatés
├── templates/                   # docker-compose.yml de base
└── README.md
```

---

## 🛠️ Commandes du superviseur (`supervisor.sh`)

| Commande | Description |
|----------|-------------|
| `create <name>` | Crée une nouvelle instance avec prompts interactifs |
| `list` | Liste toutes les bases gérées |
| `start <name>` | Démarre l’instance `<name>` |
| `stop <name>` | Arrête l’instance `<name>` |
| `purge <name>` | Supprime le conteneur, le volume et les métadonnées |
| `backup <name>` | Sauvegarde la base en `.sql` |
| `info <name>` | Affiche les détails d'une instance |
| `logs <name>` | Affiche les logs du conteneur |
| `help` | Rappelle les commandes disponibles |

---

## 🔄 Fichiers utilisés

### `.env` – Configuration utilisateur
Contient les variables nécessaires pour déployer l’instance MySQL.

```env
MYSQL_ROOT_PASSWORD=supersecret
MYSQL_DATABASE=mydb
MYSQL_USER=admin
MYSQL_PASSWORD=adminpass
```

### `.meta.json` – Métadonnées système (généré automatiquement)
Contient le port attribué, le nom du conteneur, le statut, etc.

```json
{
  "name": "mydb",
  "port": 3307,
  "container": "mysql_db_mydb",
  "volume": "mysql_data_mydb",
  "status": "running"
}
```

---

## 🔒 Sécurité

- Aucun port ouvert publiquement par défaut
- Connexion via VPN, SSH ou tunnel
- Compatible avec des outils comme **MySQL Workbench**, **DBeaver**, etc.

---

## 📌 Pré-requis

- Docker + Docker Compose
- Bash 4+
- `jq` (pour gérer les fichiers JSON)

---

## 🧱 Roadmap

- [x] Shell complet multi-instance
- [ ] Intégration API Flask
- [ ] Interface web sécurisée
- [ ] Gestion multi-utilisateur (auth, droits)
- [ ] Exports/Imports portables

