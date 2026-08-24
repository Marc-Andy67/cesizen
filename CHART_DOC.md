# Documentation de l'Implémentation du Graphique (Chart.js + Turbo)

Le graphique d'évolution du stress présent sur la page "Mon Profil" a été conçu pour allier fluidité visuelle et compatibilité technique avec **Symfony Turbo** (l'outil de Hotwire qui gère la navigation sans recharger la page complète).

Voici les étapes exactes de sa conception :

## 1. Préparation des données dans le Contrôleur (`ProfileController.php`)
Pour que le graphique en ligne (line chart) fonctionne, il a besoin de deux tableaux de données (axes X et Y) :
- **L'axe X (Les étiquettes)** : Les dates de passage des tests.
- **L'axe Y (Les valeurs)** : Le score de stress obtenu.

Dans le contrôleur, nous récupérons l'historique de l'utilisateur, l'organisons de manière chronologique (du plus ancien au plus récent), et formatons ces deux tableaux. Nous les transformons ensuite en chaînes de caractères JSON grâce à `json_encode()` pour les injecter facilement dans le Javascript côté vue (Twig).

## 2. L'élément Canvas (`profile/index.html.twig`)
Dans le template, nous avons placé un simple objet HTML5 `<canvas id="evolutionChart"></canvas>` à l'intérieur d'une carte stylisée. C'est sur cette "toile" que Chart.js va dessiner le rendu vectoriel du graphique.

## 3. L'intégration de Chart.js
Nous chargeons la librairie open-source **Chart.js** via un lien CDN. C'est elle qui transforme nos tableaux de données en courbe mathématique interactive.

Puis, nous configurons son style graphique :
- Courbe adoucie (`tension: 0.3`).
- Couleur Bleu DSFR pour le tracé (`#000091`).
- Option `fill: true` avec un fond semi-transparent pour donner un effet moderne et "liquide" sous la courbe.
- Points d'intersections ronds s'illuminant au passage de la souris.

## 4. La "Magie" derrière Symfony Turbo (Le vrai défi technique)
Normalement, un graphique s'initialise à la fin du chargement de la page (`DOMContentLoaded`). 
**Le problème :** Avec Symfony Turbo activé (Navigation SPA), le navigateur ne recharge plus réellement la page quand un utilisateur clique sur son profil. De ce fait, Javascript ne redessinait pas le graphique ou, pire, essayait de le dessiner par-dessus l'ancien provoquant l'erreur fatale : *"Canvas is already in use"*.

Voici le code exact codé pour régler ce conflit :

```javascript
// Variable globale pour stocker l'instance du graphique (en dehors de l'exécution immédiate)
let evolutionChart = null;

function initChart() {
    const ctx = document.getElementById('evolutionChart');
    if (!ctx) return; // Si la page n'a pas de canevas (ex: l'utilisateur n'a aucun historique), on arrête.

    // === FIX TURBO ===
    // Si un graphique existe déjà en mémoire (ex: l'utilisateur a cliqué sur Retour/Suivant),
    // on le détruit formellement avant d'en recréer un pour libérer le <canvas> !
    if (evolutionChart !== null) {
        evolutionChart.destroy();
    }

    // Instanciation de la nouvelle courbe avec les données JSON injectées depuis Symfony
    evolutionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: JSON.parse('{{ datesJson|raw }}'),
            datasets: [{
                label: 'Score de stress',
                data: JSON.parse('{{ scoresJson|raw }}'),
                // ... configuration visuelle
            }]
        },
        // ... options responsives
    });
}

// === LES ÉCOUTEURS D'ÉVÈNEMENTS ===
// 1. Initialise le graphique si on tape l'URL directement dans le navigateur
document.addEventListener('DOMContentLoaded', initChart);
// 2. Initialise le graphique si on arrive sur la page via un lien interne cliqué (géré par Turbo)
document.addEventListener('turbo:load', initChart);
// 3. Rafraîchit le graphique si on utilise les flèches "Précédent/Suivant" du navigateur
document.addEventListener('turbo:render', initChart);
```

### En résumé
L'astuce technique tourne autour de l'appel manuel de la méthode `.destroy()` et l'association de notre fonction de dessin `initChart()` aux écouteurs natifs créés spécialement par le framework Turbo de Symfony (`turbo:load` et `turbo:render`). Cela assure que le graphique est toujours parfaitement proportionné, interactif et dépourvu de bugs de mémoire, peu importe le chemin emprunté par l'utilisateur.
