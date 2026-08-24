# Rapport des Tests Automatisés (CESIZen)

Le projet CESIZen intègre actuellement **13 tests automatisés** répartis en deux grandes catégories de tests gérés via **PHPUnit** : les tests unitaires et les tests fonctionnels (bout en bout). 

Voici le détail de ce qui est testé et validé automatiquement par le code :

## 1. Tests Fonctionnels (Interface et Navigation)
*Ces tests utilisent **Symfony Panther** pour simuler le comportement d'un vrai navigateur (clics, chargement de page, vérification d'éléments visuels).*

**Fichier :** `Tests/Functional/ApplicationFlowTest.php`
- ✅ `testHomePageLoads` :  Vérifie que la page d'accueil principale du site se charge correctement sans erreur 500 et affiche son composant `<header>`.
- ✅ `testLoginPageLoads` : Vérifie que la page de connexion s'affiche et contient bien le champ obligatoire pour saisir l'email (`input[type="email"]`). Utile pour s'assurer que le formulaire de connexion n'est pas cassé.
- ✅ `testDiagnosticIndexPage` : Vérifie que la page d'accueil du test de diagnostic s'affiche et contient bien le titre principal "Évaluez votre santé mentale".

## 2. Tests Unitaires (Cœur métier)
*Ces tests isolent le code métier pur hors de la base de données et du navigateur pour garantir que les calculs et la sécurité interne sont irréprochables.*

### A. Le Moteur de Diagnostic (DiagnosticService)
**Fichier :** `Tests/Unit/DiagnosticServiceTest.php`
- ✅ `testCalculateScoreWithEmptyResponses` : S'assure que si un utilisateur valide un questionnaire totalement vide, le moteur calcule bien un score de `0` sans planter.
- ✅ `testCalculateScoreWithValidResponses` : Simule plusieurs réponses choisies (ex: deux réponses valant 100 et 45 points) et vérifie que le moteur mathématique additionne parfaitement les scores (`145`).
- ✅ `testGetThresholdForScoreLowStress` : Vérifie que le moteur est capable de renvoyer le **bon palier** (seuil de stress faible) en fonction d'un score donné.
- ✅ `testGetThresholdForScoreHighStress` : Vérifie que le moteur associe le bon palier critique lorsque le score dépasse la limite supérieure.
- ✅ `testGetThresholdForScoreNoMatch` : Sécurité garantissant que le système gère proprement (renvoie `null`) si aucun palier ne correspond au résultat (empêche les bugs d'affichage sur la page finale).

### B. Gouvernance des Rôles & Accès (UserVoter)
**Fichier :** `Tests/Unit/UserVoterTest.php`
- ✅ `testAnonymousUserCannotDoAnything` : Garantit qu'un visiteur non connecté essuie toujours un "Accès Refusé" s'il tente d'éditer ou de supprimer des utilisateurs depuis le panneau d'administration.
- ✅ `testAdminCanEditAnyUser` : Garantit qu'un compte possédant le rôle `ROLE_ADMIN` a obligatoirement la permission d'éditer ou d'activer/désactiver le profil d'un autre utilisateur.
- ✅ `testCanDeleteNormalUser` : Vérifie qu'un administrateur a la capacité technique de supprimer le compte d'un utilisateur standard.
- ✅ `testCannotDeleteAdminUser` : **Sécurité critique** garantissant qu'il est *strictement impossible* pour quiconque de supprimer le profil d'un compte Administrateur (empêchant ainsi aux admins de supprimer tous les autres admins accidentellement).

---
**Note sur l'exécution** :
Lors du lancement de la commande `php bin/phpunit`, tous ces tests sont orchestrés. S'ils sont tous en vert, le cœur métier (Voter et Diagnostic) ainsi que l'interface primordiale sont garantis fonctionnels.
