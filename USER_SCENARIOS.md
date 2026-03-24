# Scénarios Utilisateurs (Cas de Test) — CESIZen

Afin de couvrir l'intégralité des fonctionnalités de l'application avec un minimum de redondance, les fonctionnalités ont été condensées en **3 scénarios principaux**.

---

## 🟢 Scénario 1 : Le Visiteur Anonyme (Découverte et Diagnostic libre)
*Ce scénario valide la consultation publique, la passation de test de stress non sauvegardée et la liaison vers la littérature.*

1. **Découverte** : L'utilisateur arrive sur la page d'accueil, découvre l'application CESIZen et clique sur "Auto-diagnostic" dans le menu.
2. **Passation du test** : Il sélectionne un questionnaire (ex: Holmes & Rahe) et remplit les questions avec des cases à cocher / boutons radios.
3. **Résultat Anonyme** : Il soumet le formulaire. L'application calcule son score LCU et affiche :
   - Le score exact.
   - Le profil de stress (Seuil de stress) et les recommandations associées.
   - Un bandeau d'avertissement indiquant que, comme il n'est pas connecté, le résultat ne sera pas sauvegardé dans un historique.
4. **Redirection intelligente** : Il clique sur le bouton "Voir les ressources adaptées". Il est redirigé vers la page de la Documentation, automatiquement filtrée pour n'afficher que les catégories associées aux thématiques de son questionnaire.

---

## 🔵 Scénario 2 : L'Utilisateur Connecté (Inscription, Santé mentale, et Données Personnelles)
*Ce scénario valide le tunnel d'authentification, la persistance des scores, le tableau de bord de progression, la sécurisation du compte et la conformité RGPD.*

1. **Inscription Sécurisée** : L'utilisateur clique sur "Créer un compte libre". Il renseigne ses informations. Le formulaire valide la robustesse de son mot de passe en temps réel (majuscules, caractères spéciaux).
2. **Connexion** : Redirigé vers la page de connexion après succès, il se connecte avec son nouveau compte.
3. **Diagnostic** : Il effectue à nouveau un test de stress et le valide. Cette fois-ci, aucun avertissement n'apparaît : le résultat est persisté en base de données de façon sécurisée.
4. **Mon Espace (Historique)** : Il se rend sur "Mon Espace".
   - Il y observe en haut de la carte son historique récent sous forme de **Tableau** (Date, Résultat, Actions).
   - Juste en dessous, il visualise l'évolution chronologique (de gauche à droite) de son niveau de stress sur un **Graphique interactif** (Chart.js).
5. **Modification de Profil & Mot de passe** : 
   - Il modifie son adresse email et son pseudo.
   - Il accède à "Changer mon mot de passe", renseigne son ancien mot de passe, puis le nouveau en double saisie pour valider la mise à jour.
6. **RGPD & Suppression** : 
   - Il clique sur "Télécharger l'archive" pour récupérer ses données de santé en format de portabilité (JSON).
   - Il clique enfin sur "Supprimer définitivement" (Zone de danger). Une modale JS lui demande confirmation. Son compte est supprimé, ses données médicales sont anonymisées, et il est redirigé (déconnecté) vers l'accueil.

---

## 🟣 Scénario 3 : L'Administrateur (Back-office et Gestion de Contenu)
*Ce scénario couvre toute la gestion CRUD, l'intégrité des données, et le pilotage du contenu.*

1. **Accès Back-office** : L'utilisateur se connecte avec un compte ayant le rôle `ROLE_ADMIN` et clique sur le bouton "Administration" dans la barre de navigation.
2. **Vue d'ensemble** : Il atterrit sur le Dashboard affichant les statistiques globales (nombre de documentations actives, nombre de quiz).
3. **Création de contenu indépendant** :
   - **Catégorie** : Il crée une nouvelle catégorie "Bien-être au travail".
   - **Documentation** : Il rédige un nouvel article, le rattache à la catégorie fraîchement créée, et le publie.
   - **Question** : Il crée une ou deux nouvelles questions pour le diagnostic.
   - **Seuil de Stress** : Il crée un nouveau seuil (ex: Score de 150 à 300) avec recommandation via le nouveau bouton de l'en-tête. Le formulaire l'empêche (Message d'erreur) d'entrer un score minimum négatif ou un score minimum supérieur au score maximum.
4. **Assemblage dans le Quiz** : Il se rend dans la gestion des Quiz. Il en crée un nouveau, et grâce aux modales DaisyUI, il y "attache" :
   - Les questions créées précédemment.
   - Le seuil de stress créé plus tôt.
   - Les catégories (qui serviront au bouton de recommandation final).
5. **Intégrité de la base de données** : 
   - Il retourne sur l'onglet *Catégories* et tente de **Supprimer** la catégorie "Bien-être au travail".
   - Étant donné qu'elle est liée à la documentation nouvellement publiée, une alerte flash rouge lui indique qu'"*Il est impossible de la supprimer car elle est utilisée*".
   - Il retourne sur la *Documentation*, la supprime (succès), puis parvient enfin à supprimer la catégorie.
