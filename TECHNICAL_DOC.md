# Documentation Technique \& Architecture — CESIZen

Ce document décrit en détail l'architecture du projet **CESIZen** et l'utilité de chaque dossier ou fichier majeur. Il s'appuie sur le standard du framework **Symfony 8**, couplé à Tailwind CSS pour l'UI. 

Ce guide est votre "boussole" : il vous indique exactement où chercher selon le problème ou la fonctionnalité à modifier.

---

## 🏗 Architecture Globale

Le projet suit le motif de conception **MVC** (Modèle-Vue-Contrôleur) avec les spécificités de Symfony :
- **Modèle** : géré par Doctrine ORM (`src/Entity` et `src/Repository`).
- **Vue** : gérée par Twig (`templates/`).
- **Contrôleur** : gère les requêtes HTTP HTTP (`src/Controller`).

---

## 📁 Arborescence Principale (La Racine)

### `/assets`
**À quoi ça sert ?** C'est ici que vit le code Front-End (CSS, JavaScript) *avant* d'être compilé.
- **Où chercher ?** Si vous voulez modifier les styles globaux pur CSS ou la configuration Tailwind, regardez `assets/styles/app.css`. Si vous devez ajouter un comportement JavaScript, regardez `.js` ou `.js/controllers/` (Stimulus).

### `/bin`
**À quoi ça sert ?** Contient les exécutables.
- **Où chercher ?** Vous y utilisez principalement `bin/console` pour lancer des commandes Symfony (vider le cache, créer une entité, lancer des migrations).

### `/config`
**À quoi ça sert ?** Toute la configuration du framework et des bibliothèques externes.
- **Où chercher ?** 
  - `config/packages/` : Configuration de base de données (doctrine), de sécurité (security.yaml), de mail, etc.
  - `config/routes.yaml` : Si une route n'est pas définie sous forme d'Attribut dans le contrôleur (rare dans CESIZen).
  - `config/services.yaml` : L'injection de dépendances et la configuration des paramètres globaux.

### `/migrations`
**À quoi ça sert ?** Les fichiers générés automatiquement décrivant comment la base de données doit évoluer (création de tables, ajout de colonnes).
- **Où chercher ?** N'y touchez pas manuellement en général, laissez `make:migration` faire son travail.

### `/public`
**À quoi ça sert ?** Le seul dossier accessible publiquement par le navigateur (racine du serveur web nginx/apache).
- **Où chercher ?** 
  - L'entrée de l'application : `index.php`.
  - Les images statiques (logos, illustrations) : `public/images/`.
  - Les ressources compilées par Webpack/Tailwind : Ce dossier est auto-généré.

### `/templates`
**À quoi ça sert ?** Contient toutes les Vues HTML (Moteur Twig).
- **Où chercher ?**
  - `admin/` : Les pages du back-office (CRUD).
  - `front/` : Les pages de l'application publique (Accueil, Espace Mon Profil, Le Diagnostic).
  - `components/` : Les éléments réutilisables (Boutons, messages d'erreurs, barre de navigation, Footer).
  - `base.html.twig` : Le "squelette" principal de toutes les pages du site (contient le `<head>` et le `<footer>` global).

### `/tests`
**À quoi ça sert ?** Les scripts de tests automatisés (PHPUnit / Panther).
- **Où chercher ?** Si les GitHub Actions échouent, c'est ici qu'il faut regarder pour corriger le code des tests.

### `/var`
**À quoi ça sert ?** Les fichiers temporaires (Cache et Logs).
- **Où chercher ?** En cas de bug "invisible" sur le serveur, regardez `var/log/dev.log` ou `var/log/prod.log`.

---

## 🧠 Le cœur du réacteur : Le dossier `/src`

Le dossier `src` contient tout votre code métier PHP (le cerveau de l'application). C'est ici que 90% du développement back-end s'effectue.

### `src/Controller/`
**À quoi ça sert ?** Ce sont les "Aiguilleurs". Ils reçoivent l'URL demandée par l'utilisateur, appellent la base de données, et renvoient une Vue Twig ou une redirection.
- **Sous-dossiers clés** :
  - `Admin/` : Contrôleurs du back-office (ex: `QuizController.php` pour gérer les questions). Gérés par les routes `/admin`.
  - `Front/` : Contrôleurs côté public (ex: `DiagnosticController.php`, `ProfileController.php`).

### `src/Entity/`
**À quoi ça sert ?** Les Classes PHP qui représentent directement les Tables de votre base de données (Le Modèle).
- **Où chercher ?** Si vous voulez ajouter un champ (ex: "âge" sur un Utilisateur), c'est dans `src/Entity/User.php`. (On utilise souvent `php bin/console make:entity` pour générer le code).
- **Note CESIZen** : Contient toutes les validations (`#[Assert\...]`) qui empêchent, par exemple, les scores négatifs (`StressThreshold.php`).

### `src/Repository/`
**À quoi ça sert ?** Gère les requêtes complexes vers la base de données (Les "SELECT").
- **Où chercher ?** Si vous devez chercher des utilisateurs spécifiques, ou trier des historiques par date, c'est ici que vous définirez vos "QueryBuilders".

### `src/Form/`
**À quoi ça sert ?** La définition des formulaires HTML (champs, types de champs, options).
- **Où chercher ?** Si vous voulez ajouter une case à cocher, changer le label d'un champ ou rendre un champ obligatoire. (Exemple : `ChangePasswordFormType.php`).

### `src/Service/`
**À quoi ça sert ?** Les "Travailleurs de l'ombre". Ce sont des classes métier isolées pour ne pas surcharger les Contrôleurs. C'est l'essence même de votre logique d'entreprise.
- **Exemples dans CESIZen** :
  - `DiagnosticService.php` : C'est LUI qui s'occupe de l'algorithme qui calcule le score total du questionnaire de stress.
  - `RgpdService.php` : C'est LUI qui extrait les données JSON de l'utilisateur pour la portabilité.

### `src/DataFixtures/`
**À quoi ça sert ?** Du faux contenu généré pour remplir la base de tests ou de développement.
- **Où chercher ?** C'est ici que sont créés par défaut le quiz "Holmes & Rahe" et le compte "admin@cesizen.fr" lors de l'installation du projet.

### `src/EventSubscriber/` & `src/Security/`
**À quoi ça sert ?** Fonctionnalités très poussées pour intercepter le comportement de Symfony.
- **Où chercher ?** Si vous devez modifier comment marche la Connexion (Authenticator), les autorisations, ou agir juste avant/après qu'une page se charge.

---

## 📄 Les fichiers vitaux à la racine

- **`.env` / `.env.local`** : Les variables d'environnement. C'est là que se trouve le mot de passe de la Base de Données. (Ne jamais mettre de vrais mots de passe dans `.env` qui est envoyé sur Github ! Utiliser `.env.local`).
- **`compose.yaml` / `Dockerfile`** : Fichiers liés à Docker. Ils disent comment monter le container PostgreSQL avec lequel Symfony discute.
- **`tailwind.config.js`** : La configuration des couleurs et styles. C'est là que se trouvent les couleurs personnalisées comme le "bleu de la république" (`dsfr-blue`).
- **`package.json`** : La liste des dépendances Javascript (Tailwind, Chart.js...).
- **`composer.json`** : La liste des dépendances PHP (Symfony, Doctrine, Twig...).

---

## 🎯 En résumé (Cheat Sheet)

| Vous voulez modifier... | Où chercher ? |
| --- | --- |
| L'apparence, l'HTML d'une page | `/templates/...` |
| Les couleurs, le logo, l'espacement global | `tailwind.config.js` et `assets/styles/app.css` |
| La logique derrière un écran (Ce qu'il se passe au "clic") | `src/Controller/...` |
| Les colonnes d'une table SQL | `src/Entity/...` |
| Les filtres et les listes déroulantes de saisie | `src/Form/...` |
| Le calcul de score ou une fonction métier complexe | `src/Service/...` |
| Retrouver un log d'erreur complet | `var/log/dev.log` |
