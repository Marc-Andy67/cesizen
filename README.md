# CESIZen

**CESIZen** est une plateforme dédiée à la santé mentale et à la gestion du stress, développée pour le compte du Ministère de la Santé et de la Prévention français. Elle propose des ressources documentaires et un outil d'évaluation du stress basé sur l'échelle de réajustement social de Holmes et Rahe. 

## Prérequis

- PHP 8.4 ou supérieur
- PostgreSQL 18
- Node.js & npm (pour Tailwind CSS)
- Composer

## Installation

1. Clonez le dépôt :
```bash
git clone https://github.com/votre-org/cesizen.git
cd cesizen
```

2. Installez les dépendances PHP :
```bash
composer install
```

3. Installez les dépendances front-end (Tailwind) :
```bash
npm install
npm run build
```

4. Configurez l'environnement :
Copiez le fichier `.env` en `.env.local` et adaptez la variable `DATABASE_URL` pour pointer vers votre instance PostgreSQL 18 locale.

```env
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/cesizen?serverVersion=18&charset=utf8"
```

5. Initialisez la base de données et les fixtures :
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n
```

6. Lancez le serveur local Symfony :
```bash
symfony server:start -d
```
L'application est maintenant accessible sur `https://127.0.0.1:8000`.

## Fonctionnalités Principales

### Comptes Utilisateurs & Sécurité
- Inscription et authentification sécurisées (bcrypt cost 13).
- Protection contre les attaques par force brute (Login Throttling).
- Espace personnel avec gestion des données, exportation JSON et anonymisation (conformité RGPD).
- Dashboard Administrateur (rôles `ROLE_ADMIN`).

### Documentation
- Base de connaissances classée par catégories.
- Éditeur de texte riche (Trumbowyg) en back-office.
- Formats de lecture optimisés.

### Diagnostic de Stress (Holmes & Rahe)
- Questionnaire complet interactif.
- Calcul côté serveur basé sur un système de pondération stricte.
- Algorithme de catégorisation du risque (Low, Moderate, High Stress).
- Sauvegarde historique des scores avec graphiques (Chart.js) pour visualiser l'évolution.

## Outils Qualité & CI/CD
Le projet utilise un workflow GitHub Actions strict (voir `.github/workflows/ci.yml`) incluant :
- Tests Unitaires locaux (PHPUnit)
- Tests Fonctionnels End-to-End (Panther + ChromeDriver)
- Linters (PHP CS Fixer) et Analyse statique (PHPStan lvl 6)
- Couverture de code obligatoire (≥ 70%)
- Scan de sécurité dynamique (ZAP) et analyse des packages (Trivy).

## Auteurs
Développé par [Votre nom/Votre équipe] dans le cadre du projet ECF CESI.
