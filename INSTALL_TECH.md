# Guide d'Installation Technique — CESIZen

Ce guide est destiné aux développeurs et administrateurs système souhaitant déployer ou contribuer au projet CESIZen. L'application est propulsée par Symfony 8, PHP 8.4, PostgreSQL, et Tailwind CSS.

## 🛠 Prérequis Systèmes
Assurez-vous d'avoir les outils suivants installés sur votre machine (ou serveur) :
- **Git** (pour cloner le dépôt)
- **PHP 8.2+** (avec les extensions : intl, pdo_pgsql, mbstring, xml, ctype, iconv)
- **Composer** (Gestionnaire de dépendances PHP)
- **Node.js & NPM/Yarn** (Pour la compilation des assets front-end Tailwind)
- **Docker & Docker Compose** (Optionnel mais recommandé pour la base de données PostgreSQL)
- **Symfony CLI** (Pour le serveur local de développement)

---

## 🚀 Étape 1 : Récupération du projet
Clonez le dépôt Git sur votre poste local :
```bash
git clone https://github.com/Marc-Andy67/cesizen.git
cd cesizen
```

## 📦 Étape 2 : Installation des dépendances
Installez les bibliothèques PHP et les paquets Node.js :
```bash
composer install
npm install
```

## ⚙️ Étape 3 : Configuration de l'environnement
1. Dupliquez le fichier d'environnement :
   ```bash
   cp .env .env.local
   ```
2. Éditez `.env.local` pour y renseigner les informations de connexion à votre base de données PostgreSQL :
   ```env
   DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
   ```

## 🐘 Étape 4 : Base de données (via Docker)
Démarrez les conteneurs définis dans le fichier `compose.yaml` (cela va lancer PostgreSQL) :
```bash
docker compose up -d
```

Puis, créez la base de données, lancez les migrations et chargez le jeu de données d'essai (fixtures) :
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n
```

## 🎨 Étape 5 : Compilation des ressources (Assets)
Compilez le code CSS (Tailwind) et JS (Turbo/Stimulus) :
```bash
# Pour le développement (recharge à chaud) :
npm run watch

# Pour la production :
npm run build
```

## 💻 Étape 6 : Lancement du serveur local
Si tout s'est bien passé, vous pouvez démarrer votre instance locale :
```bash
symfony serve -d
```
L'application est maintenant disponible sur : **http://127.0.0.1:8000**

---

## 🧪 Annexe : Lancement des tests automatisés
Pour garantir la non-régression de l'application (PHPUnit / Panther) :
```bash
php bin/console doctrine:database:create --env=test
php bin/console doctrine:schema:update --force --env=test
php bin/phpunit
```
