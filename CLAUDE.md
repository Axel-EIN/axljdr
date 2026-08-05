# AXL-JDR

Gestionnaire de campagne de jeu de rôle (univers de Rokugan). Symfony + Twig,
CSS custom dans `public/css/`, Bootstrap 4.6 en CDN pour quelques utilitaires.
Pas de build tool : les fichiers de `public/css/` sont servis tels quels.

## Réponses

Être concis. Les comptes rendus de ce qui a été fait, comme toute explication,
vont à l'essentiel : ce qui change, et le pourquoi quand il n'est pas évident.

- Pas de récapitulatif exhaustif fichier par fichier ni de reformulation de la
  demande.
- Pas de rappel des étapes intermédiaires ni des pistes écartées.
- Un point de détail technique ne se développe que s'il change une décision,
  ou s'il faut trancher quelque chose.
- Trois remarques moyennes valent moins qu'une seule qui compte.

## Philosophie de code

Ces deux règles passent avant tout le reste. Elles s'appliquent au CSS, au
Twig et au PHP.

### 1. Aller au plus simple et au plus concis

Le code doit rester lisible en premier lieu. Avant d'ajouter une règle ou une
classe, se demander si le résultat peut s'obtenir avec moins. Préférer une
seule valeur bien placée à plusieurs qui se compensent.

Concrètement :

- Une propriété plutôt que deux qui font le même effet visuel (`mb-5 pb-5` sur
  un bloc sans fond ni bordure = un seul des deux suffit).
- Un `min-height` porté par le bon élément plutôt qu'un sur le parent et un
  sur l'enfant.
- Un utilitaire Bootstrap responsive (`mt-3 mt-lg-5`) plutôt qu'une règle CSS
  avec `!important` pour écraser un utilitaire.
- Un `:not()` explicite plutôt qu'une dépendance à l'ordre de déclaration.

Les noms de classe suivent la même règle : **deux mots séparés d'un tiret au
maximum** (`episode-h1`, `chapter-h2`, `bg-stroke-container` quand un troisième
est vraiment nécessaire). Un état ou une variante se pose en classe à part et
se cible avec `&.reduced` imbriqué, plutôt qu'en allongeant le nom de base
(`chapter-h2 reduced`, pas `title-display-h1-reduced`).

### 2. Une règle CSS vit là où elle sert

Une règle qui ne concerne qu'un seul endroit va dans `public/css/pages/` (ex.
`pages/location.css`), pas dans un fichier global. Les fichiers globaux
(`cards.css`, `banners.css`, `components.css`…) sont chargés sur les 89 pages
du site : tout ce qui n'y est pas partagé y est du poids mort.

Le critère est l'usage réel, pas la parenté thématique. Les filtres de la
rubrique Personnages sont un bloc de bannière, mais ils ne s'affichent que sur
cette page : ils vivent donc dans `pages/characters.css`, pas dans
`banners.css`. Même raisonnement pour les cartes d'épisode
(`pages/campaign.css`) et les cartes de personnage (`pages/characters.css`).

Avant de déplacer, vérifier l'étanchéité — chaque classe du bloc ne doit
apparaître que dans le composant visé (`grep` sur chaque nom de classe), et
les règles voisines dont il dépend doivent suivre (ex. le
`main.container-fluid { position: relative }` qui sert de repère aux filtres).

Le jour où un second gabarit réutilise le bloc, trois issues selon le volume :
remonter la règle dans le fichier global qui correspond, la regrouper avec
d'autres du même genre, ou — si elle est devenue conséquente — lui créer son
propre fichier dans `public/css/components/`.

**Tout piloter depuis le CSS.** Quand une balise est déjà ciblée par un
sélecteur du projet, sa géométrie — marges, paddings, tailles — vit dans le CSS,
pas en utilitaires Bootstrap posés à côté de la classe. Sinon la même valeur se
lit à deux endroits, et comme les utilitaires sont en `!important`, aucune règle
CSS ne pourra la reprendre : le jour où on ajuste depuis le fichier de page, ça
ne répond pas et rien ne dit pourquoi.

Les utilitaires gardent leur place sur une balise que rien ne cible, et pour ce
qui n'est pas de la géométrie : visibilité (`d-none d-lg-block`), ordre
(`order-*`), colonnes (`col-*`).

Les gabarits de page (`element.html.twig`, `category.html.twig`, pages de
texte) sont pilotés depuis `components/element.css`, `components/category.css`.

### 3. Factoriser, et chercher activement où factoriser

À chaque intervention, vérifier si d'autres pages ou fichiers présentent le
même motif, et les traiter ensemble. Le but n'est pas seulement d'éviter la
duplication : c'est d'écrire moins de code, donc d'en garder moins à lire.

Concrètement :

- Avant de corriger une page, chercher le même problème ailleurs
  (`grep` sur le motif, pas seulement sur le nom du fichier).
- Corriger dans la brique partagée quand elle existe
  (`element.html.twig`, `category.html.twig`, `parts/picture-grid.html.twig`)
  plutôt que page par page.
- Signaler les duplications repérées en passant, même hors du périmètre
  demandé, pour décider ensemble de les traiter.
- Supprimer les règles mortes : media queries qui répètent la valeur du palier
  voisin, classes CSS jamais utilisées, variables Twig définies puis ignorées.
- Toutes les quelques tâches, refaire un tour d'ensemble du code (pas
  seulement des fichiers du jour) pour repérer ce qui peut être factorisé.

## Architecture et nomenclature

L'arborescence du projet, les conventions de nommage et la bascule progressive
du code vers l'anglais sont dans [`.claude/code.md`](.claude/code.md). **Le lire
avant de créer un fichier, une classe CSS, une entité ou une route.**

## Design, Responsive, UI/UX, Front End

Toutes les règles de design, de gabarits et de responsive sont dans
[`.claude/design.md`](.claude/design.md). **Le lire avant toute intervention**
touchant au Responsive Design, à l'UI/UX, au Front End, à un template Twig
visuel, ou au CSS/style — c'est le contexte à charger en amont dans ces cas,
pas seulement en cas de doute.

## Vérifications

```bash
docker compose exec -T app php bin/console lint:twig templates
docker compose exec -T app php -l <fichier.php>
```

`php` n'est pas disponible sur l'hôte : tout passe par le conteneur `app`.

Ne jamais démarrer/arrêter les conteneurs Docker (`docker compose up`,
`stop`, `restart`...) de sa propre initiative — l'utilisateur gère leur
cycle de vie lui-même. Si `app` n'est pas up, le lint ci-dessus échouera :
le signaler et laisser l'utilisateur le relancer, ne pas le faire à sa place.

Pas de vérification visuelle (Playwright, captures d'écran, navigateur headless)
de sa propre initiative : le lint Twig et `php -l` suffisent par défaut. Ne
lancer ce genre de test que si l'utilisateur le demande explicitement, ou si
la fonctionnalité est réellement complexe (logique JS interactive, mise en
page qui ne peut pas s'évaluer à la lecture du CSS/Twig).

### Fichiers temporaires

Les captures d'écran et autres fichiers produits pour vérifier un rendu sont
jetables : les effacer dès la tâche terminée, sans attendre qu'on le demande.
