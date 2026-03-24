# Déclaration et Pratiques d'Accessibilité (RGAA)

Le projet **CESIZen** a été conçu en intégrant dès le départ des bonnes pratiques d'accessibilité numérique, en s'inspirant des recommandations du **Référentiel Général d'Amélioration de l'Accessibilité (RGAA)** et du Système de Design de l'État (DSFR).

Voici les mesures techniques et conceptuelles mises en place pour garantir l'accessibilité de l'application :

## 1. Contraste et Typographie
- **Respect des ratios de contraste** : Utilisation de la couleur primaire `dsfr-blue` (`#000091`) et de teintes neutres profondes pour le texte garantissant un rapport de contraste supérieur à 4.5:1 (Niveau AA du WCAG).
- **Lisibilité** : Implémentation de la typographie *Marianne* (font-family officielle) avec des tailles de police adaptables (`text-base`, `text-lg`) et non bloquées en pixels stricts, permettant le redimensionnement par le navigateur de l'utilisateur.

## 2. Structure Sémantique (HTML5)
- **Hiérarchie des titres** : Respect strict de l'ordre documentaire (`<h1>` unique par page, suivi de `<h2>`, `<h3>`, etc.) sans saut de niveau.
- **Landmarks (Points de repère)** : Utilisation rigoureuse des balises sémantiques (`<header>`, `<nav>`, `<main>`, `<footer>`) pour permettre aux lecteurs d'écran (NVDA, JAWS, VoiceOver) de naviguer rapidement par blocs.

## 3. Navigation au Clavier et Focus
- **Visibilité du focus** : Tous les éléments interactifs (boutons, liens, champs de formulaire) possèdent des styles `:focus` ou `:focus-visible` explicites (ex: `focus:ring-2 focus:ring-offset-2 focus:ring-dsfr-blue`), évitant l'utilisation du `outline-none` par défaut.
- **Ordre logique** : La structure DOM suit le cheminement visuel logique de gauche à droite et de haut en bas, permettant une navigation à la touche `Tab` cohérente.

## 4. Attributs ARIA et Lecteurs d'Écran
- **Labels Aria (`aria-label`)** : Ajout sur tous les boutons ou icônes ne possédant pas de texte visible explicitant l'action (par exemple, les boutons avec icône "Corbeille" pour la suppression de réponses : `aria-label="Supprimer cette réponse"`).
- **Icônes décoratives** : Les SVG purement décoratifs utilisent `aria-hidden="true"` ou sont configurés sans balise `<title>` pour ne pas polluer la lecture vocale.
- **Régions dynamiques (`aria-live`, `role="alert"`)** : Les messages flash (succès, erreur) informatifs utilisent des rôles d'alerte pour être annoncés immédiatement aux utilisateurs non-voyants.

## 5. Formulaires Accessibles
- **Liaison Label/Input** : Grâce à Symfony Form, tous les `<label>` sont programmatiquement liés à leur `<input>` ou `<textarea>` via l'attribut `for="id"`.
- **Messages d'erreurs clairs** : Les erreurs de validation ne se basent pas uniquement sur la couleur (rouge). L'erreur est décrite textuellement en dessous du champ concerné (`<div class="text-error">`).
- **Anticipation des erreurs** : Les champs requis affichent visuellement leur état d'obligation et possèdent les contraintes HTML5 nécessaires.

## 6. Animations et Mouvements
- Les transitions d'interface (DaisyUI/Tailwind) sont subtiles et rapides. Elles ne déclenchent pas d'effets stroboscopiques ou de flashs pouvant gêner les utilisateurs atteints de troubles vestibulaires ou d'épilepsie.

---
*Ces implémentations garantissent une base solide pour se conformer au RGAA. Un audit complet et manuel avec des outils comme Axe DevTools ou Wave est recommandé pour valider un niveau de conformité exact.*
