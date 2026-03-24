# Guide de Style CSS & UI — CESIZen

Ce document explique comment l'intégration visuelle de **CESIZen** est construite. Le design repose sur un triptyque puissant : **CSS natif**, **Tailwind CSS v4** et la librairie de composants **DaisyUI**. 

Un point d'honneur a été mis pour respecter (librement) l'esthétique du Système de Design de l'État (DSFR).

---

## 🛠 L'Écosystème Technique

### 1. Tailwind CSS v4
CESIZen utilise la **version 4** de Tailwind CSS. Contrairement aux anciennes versions, Tailwind v4 ne nécessite presque plus de fichier `tailwind.config.js`. 
Toute la personnalisation du thème se fait **directement dans le CSS** via la directive `@theme`.

### 2. DaisyUI
DaisyUI est un plugin Tailwind qui fournit des composants prêts à l'emploi (boutons, cartes, modales, navbar, tables) en utilisant uniquement des classes HTML (ex: `btn`, `card`). DaisyUI lit les variables Tailwind définies pour s'adapter automatiquement à la charte graphique.

---

## 🎨 La Charte Graphique (Le Thème)

La source de vérité des couleurs et de la police se trouve dans **`assets/styles/app.css`**.

### 🔤 Typographie
La police principale de l'application est **Marianne** (la police officielle de la République Française). 
Elle est importée dynamiquement depuis les serveurs de l'État dans le `app.css`.
- **Classe Tailwind à utiliser :** `font-sans` (Appliquée par défaut sur le `<body class="font-sans">`).

### 🌈 Couleurs Sémantiques (Les fondations DaisyUI)
Les noms ci-dessous correspondent aux classes Tailwind et DaisyUI. Par exemple, pour utiliser la couleur vive, on écrit `text-primary` ou `bg-primary`.

| Nom de la variable | Code Hexa | Rendu / Utilisation |
| --- | --- | --- |
| **`primary`** | `#003189` | Bleu Marianne (Le fameux `dsfr-blue`). Utilisé pour les boutons principaux et les titres importants. |
| **`secondary`** | `#0063CB` | Bleu plus clair. Utilisé pour les survols (hover) ou les éléments d'interaction secondaires. |
| **`accent`** | `#2D6A4F` | Vert sapin. Pour contraster certains appels à l'action. |
| **`neutral`** | `#161616` | Noir absolu (presque). Utilisé pour le texte principal (`text-base-content`). |

### 🏢 Couleurs de fond (Base)
Les couleurs de "base" définissent le fond de l'application et l'alternance des gris/bleutés.

| Nom de la variable | Rendu / Utilisation |
| --- | --- |
| **`base-100`** | `#F5F5FE` (Fond principal très légèrement bleuté. Presque blanc.) |
| **`base-200`** | `#EBEBFB` (Fond pour les en-têtes de tableau, les bandeaux de surbrillance.) |
| **`base-300`** | `#DDDDDD` (Gris pour les bordures légères, ex: `border-base-300`). |

### 🚦 Couleurs d'état
- `bg-info` / `text-info` : `#0063CB` (Bleu informatif, focus ring)
- `bg-success` / `text-success` : `#18753C` (Vert de validation)
- `bg-warning` / `text-warning` : `#B34000` (Orange/Marron d'avertissement)
- `bg-error` / `text-error` : `#E1000F` (Rouge destructeur, utilisé pour la zone de danger)

---

## 📐 Comment coder l'UI dans CESIZen ?

Si vous devez ajouter une nouvelle Vue Twig, suivez ces principes :

### 1. Structure globale
Chaque page doit idéalement être encadrée dans le squelette standard (qui limite la largeur) :
```html
<div class="py-8 md:py-12 max-w-7xl mx-auto px-4">
    <!-- Votre contenu -->
</div>
```

### 2. Les En-têtes de section
Reproduisez l'apparence des titres existants en utilisant les classes de typographie massives :
```html
<h1 class="text-3xl md:text-4xl font-extrabold text-primary tracking-tight">Titre Principal</h1>
<p class="text-base-content/70 mt-2 font-medium">Description secondaire (en gris transparent à 70%).</p>
```

### 3. Utiliser les Composants
Ne réinventez pas la roue. Beaucoup d'éléments sont déjà des composants réutilisables inclus dans `templates/components/`.

- **Les Boutons** (via Twig include) :
  ```twig
  {{ include('components/_button.html.twig', {
      label: 'Sauvegarder',
      variant: 'primary',
      icon: '<svg>...</svg>'
  }) }}
  ```
- *Si vous devez* coder un bouton en pur HTML (à cause d'un `<button type="submit">`), utilisez DaisyUI :
  `<button class="btn btn-primary rounded-sm shadow-sm font-bold">Sauvegarder</button>`

### 4. Les Cartes et Conteneurs
Pour séparer l'information, utilisez le système de cartes de border en "rounded-sm" (bords très légèrement arrondis, style institutionnel) :
```html
<div class="card bg-base-100 shadow-sm border border-base-200 rounded-sm overflow-hidden">
    <div class="card-body p-6">
        Informations ici.
    </div>
</div>
```

### 5. L'Animation et l'Accessibilité
- L'application utilise souvent une classe `.animate-fade-in` qui gère l'apparition douce de la page pour une sensation "SPA" (Single Page Application).
- **Accessibilité (A11Y)** : Dans `app.css`, il y a une condition `@media (prefers-reduced-motion: reduce)`. Elle supprime toutes les animations Tailwind et Chart.js pour les utilisateurs ayant désactivé les animations au niveau matériel. Pensez à toujours garder des "focus-visible" apparents (`focus-visible:ring-2 focus-visible:ring-info`).
