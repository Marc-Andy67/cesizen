# Changelog

Toutes les modifications notables apportées à ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère à [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-03-16

### Ajouté
- **CI/CD** : Mise en place complète du pipeline GitHub Actions (`ci.yml`) adaptant la stack PHP 8.4, PostgreSQL 18, PHPUnit, Panther, PHPStan, Tailwind CSS, et ajoutant les scans de sécurité ZAP/Trivy.
- **Sécurité** : 
  - Configuration du hachage de mot de passe (`bcrypt`, cost 13).
  - Protection contre les attaques force brute via le rate limiter Symfony.
  - Implémentation du système RGPD (Export de données métier au format JSON et fonction de suppression avec anonymisation via `RgpdService`).
- **Comptes Utilisateurs** :
  - Création des entités User, formulaires d'inscription/connexion.
  - Page de profil permettant la modification des informations (Email, Nom), du mot de passe et l'historique des connexions.
  - Dashboard Administrateur sécurisé EasyAdmin pour la gestion globale des utilisateurs.
- **Frontend** :
  - Installation et configuration de Tailwind CSS via le bundle SymfonyCasts.
  - Design system, composants responsives, Flash Messages et Breadcrumb.
  - Intégration partielle de standards d'accessibilité (RGAA) avec Skip Link.
- **Module Documentaire** :
  - Création des entités `Category` et `Documentation`.
  - Intégration de l'éditeur riche Trumbowyg en back-office pour la rédaction des articles.
  - Vue catalogue pour le front-office.
- **Module Diagnostic (Holmes & Rahe)** :
  - Architecture des entités : `Quiz`, `Question`, `Response`, `Assessment`, `StressThreshold`.
  - Algorithmes de calcul des scores pondérés.
  - Formulaires complets de participation au front-office.
  - Affichage instantané du résultat et conseils associés selon la tranche du Stress.
  - Historique des scores passés généré visuellement en Chart.js depuis l'espace personnel de l'utilisateur.

### Modifié
- Refonte de la structure originale pour suivre le Design Pattern Modèle-Vue-Contrôleur-Service, en déportant la logique métier des contrôleurs dans des services dédiés (`DiagnosticService`, `RgpdService`).
