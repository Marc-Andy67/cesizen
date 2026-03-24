# Guide d'Installation "Pour les Nuls" — CESIZen 🟢

Bienvenue sur le projet **CESIZen** ! Ce guide a été conçu pour vous aider à lancer l'application sur votre ordinateur privé, sans utiliser de termes techniques compliqués. Prenez le temps de suivre chaque point !

---

## 🎒 Ce qu'il vous faut (Les outils de base)
Avant de commencer, vous avez besoin de 3 petits outils gratuits installés sur votre ordinateur Windows, Mac ou Linux :
1. **PHP et Composer** : C'est le moteur de notre site web. Téléchargez-le sur `getcomposer.org` (prenez l'installateur Windows par défaut).
2. **Node.js** : C'est pour que les couleurs et le style visuel fonctionnent. (Téléchargez-le sur `nodejs.org`).
3. **Docker Desktop** : C'est une application qui va contenir notre base de données sans rien "salir" sur votre PC. (Laissez-le tourner en arrière-plan une fois installé).
4. **Symfony CLI** : L'outil pour démarrer le site. (Tapez "Symfony CLI download" sur Google et suivez l'installation).

---

## 🏃‍♂️ Démarrer le projet étape par étape

**Ouvrez un terminal** (Sous Windows, tapez `cmd` ou `PowerShell` dans le menu Démarrer) :

### 1) Entrer dans le dossier
Allez dans le dossier où se trouve le projet CESIZen :
```text
cd Chemin\Vers\Votre\Dossier\cesizen
```

### 2) Télécharger le code des "fournisseurs"
Tapez ces deux commandes (chacune peut prendre 1 à 2 minutes) :
```text
composer install
npm install
```
*(Cela télécharge toutes les briques LEGO nécessaires pour construire le site).*

### 3) Allumer la Base de données
Assurez-vous que **Docker Desktop** est bien ouvert sur votre ordinateur. Tapez ensuite :
```text
docker compose up -d
```

### 4) Préparer la Base de données
Tapez ces trois commandes l'une après l'autre. S'il vous pose une question (yes/no), tapez `y` puis `Entrée` :
```text
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```
*(Ceci va créer la "mémoire" du site et injecter quelques exemples d'Administrateurs et de Quiz pour que le site ne soit pas vide au démarrage).*

### 5) Peindre les murs (Générer le style visuel)
Il faut que l'ordinateur construise le fichier contenant toutes les couleurs :
```text
npm run build
```

### 6) Allumer l'écran !
On lance enfin notre site :
```text
symfony serve
```

---

🎉 **Félicitations !**
Ouvrez votre navigateur (Chrome, Firefox, Safari...) et tapez : **http://localhost:8000** ou **http://127.0.0.1:8000**
Vous devriez voir le site CESIZen !

*Pour vous connecter en mode super-pouvoir (Admin), utilisez l'email `admin@cesizen.fr` avec le mot de passe fourni par votre équipe (souvent `password` ou `admin` sur les bases de test).*
