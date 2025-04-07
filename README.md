# 🐳 MySQL Server Manager (Docker + Shell)

Un outil simple et puissant pour déployer et gérer dynamiquement des bases de données MySQL dans des conteneurs Docker, avec une configuration automatisée via un fichier `.env`.

---

## ✅ Fonctionnalités

- Création automatique de conteneurs et volumes basés sur le nom de la base (`MYSQL_DATABASE`)
- Port et nom de conteneur générés dynamiquement (pas de conflit entre instances)
- Démarrage / arrêt / suppression d'une base en une ligne de commande
- Connexion sécurisée via VPN (aucune exposition publique requise)
- Aucun besoin d’interface web : connectez-vous via MySQL Workbench, DBeaver, etc.

---

## 📂 Arborescence du projet

```
mysql-dynamic-db/
├── docker-compose.yml            # Base commune Docker Compose
├── .env                          # Configuration de l'instance
├── docker-compose.override.yml   # Généré dynamiquement par le script
├── manage_db.sh                  # Script de gestion
└── README.md
```

---

## ⚙️ Configuration

Crée un fichier `.env` :

```env
MYSQL_ROOT_PASSWORD=motdepasseUltraSolide!
MYSQL_DATABASE=ma_base
MYSQL_USER=admin
MYSQL_PASSWORD=admin_pass
```

Chaque base différente doit avoir un nom unique dans `MYSQL_DATABASE`.

---

## 🛠️ Commandes disponibles

Utilise le script `manage_db.sh` :

```bash
./manage_db.sh start    # Démarre le conteneur MySQL (et crée override.yml)
./manage_db.sh stop     # Arrête le conteneur
./manage_db.sh purge    # Supprime le conteneur + son volume de données
./manage_db.sh status   # Affiche l'état du conteneur MySQL
./manage_db.sh logs     # Affiche les logs MySQL
```

---

## 🔄 Changer de base de données

1. Modifie le fichier `.env` avec un nouveau nom de base, user, mot de passe
2. Lance :

```bash
./manage_db.sh start
```

🎉 Une nouvelle instance MySQL sera automatiquement configurée avec :
- Nom de conteneur : `mysql_db_<nom_base>`
- Volume : `mysql_data_<nom_base>`
- Port : généré automatiquement (dans la plage 3300–3350)

---

## 🔐 Sécurité

- Aucune interface web exposée
- Connexion uniquement via VPN ou tunnel SSH
- Utilise un outil desktop sécurisé : **MySQL Workbench**, **DBeaver**, **Beekeeper Studio**

---

## 🖥️ Connexion à la base

Depuis ton poste local (via VPN ou tunnel SSH) :

- **Host** : IP VPN du serveur
- **Port** : (voir `docker ps` ou script `status`)
- **Utilisateur** : `MYSQL_USER`
- **Mot de passe** : `MYSQL_PASSWORD`
- **Base** : `MYSQL_DATABASE`

---

## 💡 Astuces

- Gère plusieurs bases sur le même serveur sans conflit
- Pas besoin de modifier les fichiers Compose manuellement
- Fonctionne très bien avec des outils comme **cron** pour backups, scripts automatisés, etc.

---

## 🚜 Nettoyage complet

Si tu veux supprimer une base (conteneur + données) :

```bash
./manage_db.sh purge
```

⚠️ Cette commande supprime aussi le volume Docker, donc les données sont définitivement perdues.

---

## 📌 TODO (facultatif)

- Ajouter des scripts SQL d’init
- Ajouter des backups automatiques
- Dockeriser phpMyAdmin en option

