# AXL-JDR

**À lire en premier, avant toute autre instruction.** Utilise un français
technique clair et contrôlé, inspiré de l'ASD-STE100 : phrases courtes et
explicites, syntaxe simple, voix active, un terme par concept. Évite les
synonymes inutiles, les ambiguïtés, les nominalisations et les idiomes.
Privilégie la formulation la plus simple qui conserve exactement le sens
technique.

Gestionnaire de campagne de jeu de rôle (univers de Rokugan). Symfony + Twig,
CSS custom dans `public/css/`, Bootstrap 4.6 en CDN pour quelques utilitaires.
Pas de build tool : les fichiers de `public/css/` sont servis tels quels.

**Ne jamais écrire de commentaire dans le code.** Ni pour justifier un choix, ni
pour documenter les paramètres d'un partial, ni pour signaler un piège : ça se
dit dans le compte rendu. Vaut aussi quand le fichier réécrit en contenait déjà
un. Si un cas semble vraiment justifier une exception, la demander avant.

**Une migration par entité**, écrite à la main, avec sa description : une
intervention qui touche trois tables produit trois fichiers, jamais un seul. Le
déploiement OVH lance `doctrine:migrations:migrate` sans SSH, donc chaque
migration doit passer sans échec sur une base déjà à jour.

## À lire avant d'intervenir

- [`.claude/code.md`](.claude/code.md) — **avant toute intervention sur le
  code**, à chaque fois : migrations, langue, arborescence, noms, conventions.
  Il prévaut sur les habitudes.
- [`.claude/design.md`](.claude/design.md) — **avant toute intervention** sur le
  responsive, l'UI/UX, le front end, un template Twig visuel ou le CSS. C'est le
  contexte à charger en amont dans ces cas, pas seulement en cas de doute.

## Réponses

Être concis. Les comptes rendus, comme toute explication, vont à l'essentiel :
ce qui change, et le pourquoi quand il n'est pas évident.

- Pas de récapitulatif fichier par fichier ni de reformulation de la demande.
- Pas de rappel des étapes intermédiaires ni des pistes écartées.
- Un détail technique ne se développe que s'il change une décision, ou s'il faut
  trancher quelque chose.
- Trois remarques moyennes valent moins qu'une seule qui compte.

## Philosophie de code

Ces règles passent avant tout le reste. Elles valent pour le CSS, le Twig et le
PHP.

### 1. Aller au plus simple et au plus concis

Le code doit rester lisible en premier lieu. Avant d'ajouter une règle ou une
classe, se demander si le résultat peut s'obtenir avec moins, et préférer une
seule valeur bien placée à plusieurs qui se compensent :

- une propriété plutôt que deux qui font le même effet visuel (`mb-5 pb-5` sur
  un bloc sans fond ni bordure : un seul des deux suffit) ;
- un `min-height` porté par le bon élément plutôt qu'un sur le parent et un sur
  l'enfant ;
- un utilitaire Bootstrap responsive (`mt-3 mt-lg-5`) plutôt qu'une règle CSS
  avec `!important` pour écraser un utilitaire ;
- un `:not()` explicite plutôt qu'une dépendance à l'ordre de déclaration.

Les noms de classe suivent la même règle : **deux mots séparés d'un tiret au
maximum** (`episode-h1`, `chapter-h2` ; `bg-stroke-container` quand un troisième
est vraiment nécessaire). Un état ou une variante se pose en classe à part et se
cible avec `&.reduced` imbriqué (`chapter-h2 reduced`, pas
`title-display-h1-reduced`).

### 2. Une règle CSS vit là où elle sert

Une règle qui ne concerne qu'un seul endroit va dans `public/css/pages/` (ex.
`pages/location.css`). Les fichiers globaux — `cards.css`, `banners.css`,
`components.css`… — sont chargés sur les 89 pages du site : ce qui n'y est pas
partagé y est du poids mort.

Le critère est l'usage réel, pas la parenté thématique : les filtres de la
rubrique Personnages sont un bloc de bannière, mais ne s'affichent que sur cette
page, donc ils vivent dans `pages/characters.css`. Avant de déplacer, vérifier
l'étanchéité — `grep` sur chaque nom de classe pour s'assurer qu'elle ne sert
qu'au composant visé — et emmener les règles voisines dont le bloc dépend (ex.
le `main.container-fluid { position: relative }` qui sert de repère aux filtres).
Le jour où un second gabarit réutilise le bloc, selon son volume : le remonter
dans le fichier global qui correspond, le regrouper avec d'autres du même genre,
ou lui créer son fichier dans `public/css/components/`.

**Tout piloter depuis le CSS.** Quand une balise est déjà ciblée par un
sélecteur du projet, sa géométrie — marges, paddings, tailles — vit dans le CSS,
pas en utilitaires Bootstrap posés à côté de la classe. Sinon la même valeur se
lit à deux endroits, et comme les utilitaires sont en `!important`, aucune règle
CSS ne pourra la reprendre : on ajuste depuis le fichier de page, ça ne répond
pas, et rien ne dit pourquoi. Les utilitaires gardent leur place sur une balise
que rien ne cible, et pour ce qui n'est pas de la géométrie : visibilité
(`d-none d-lg-block`), ordre (`order-*`), colonnes (`col-*`). Les gabarits de
page (`element.html.twig`, `category.html.twig`, pages de texte) sont pilotés
depuis `components/element.css` et `components/category.css`.

### 3. Factoriser, et chercher activement où factoriser

À chaque intervention, vérifier si d'autres pages ou fichiers présentent le même
motif, et les traiter ensemble. Le but n'est pas seulement d'éviter la
duplication : c'est d'écrire moins de code, donc d'en garder moins à lire.

- Avant de corriger une page, chercher le même problème ailleurs (`grep` sur le
  motif, pas seulement sur le nom du fichier).
- Corriger dans la brique partagée quand elle existe (`element.html.twig`,
  `category.html.twig`, `parts/picture-grid.html.twig`), pas page par page.
- Signaler les duplications repérées en passant, même hors périmètre, pour
  décider ensemble de les traiter.
- Supprimer les règles mortes : media queries qui répètent le palier voisin,
  classes CSS jamais utilisées, variables Twig définies puis ignorées.
- Toutes les quelques tâches, refaire un tour d'ensemble du code pour repérer ce
  qui peut être factorisé.

### 4. Imbriquer le CSS, ne pas l'éparpiller

**Une classe ne se déclare qu'une fois, et ses descendants s'imbriquent dedans.**
Le réflexe à éviter est de poser les sélecteurs à plat, chacun de son côté, puis
une troisième règle pour les relier :

```css
/* non */
.ranking-card {}
.ranking-list {}
.ranking-card > .ranking-list {}

/* oui */
.ranking-card {
  .ranking-list {}
}
```

Vaut aussi quand la règle relationnelle est la seule qu'on écrit : un
`.parent > .enfant {}` isolé s'écrit `.parent { > .enfant {} }`. Et quand une
classe est déjà déclarée plus loin dans le même fichier, on fusionne les deux
blocs au lieu d'en ajouter un troisième.

Ce qu'on y gagne : tout ce qui concerne un bloc tient à quelques lignes d'écart
et se lit d'un coup d'œil, et aucune règle oubliée ne vient agir en silence
depuis l'autre bout de la feuille. C'est la raison qui fait aussi vivre une
variante dans le fichier de son composant (cf.
[`.claude/code.md`](.claude/code.md)) : un seul endroit à ouvrir.

## Vérifications

`php` n'est pas disponible sur l'hôte : tout passe par le conteneur `app`.

```bash
docker compose exec -T app php bin/console lint:twig templates
docker compose exec -T app php -l <fichier.php>
docker compose exec -T -u www-data app php bin/console cache:clear
```

**Toute commande qui écrit dans `var/` — `cache:clear`, une migration — se lance
en `www-data`**, sinon elle recrée le cache en root et Apache ne peut plus y
écrire : la page suivante tombe en 500 sur le répertoire du profiler.

Ne jamais démarrer ni arrêter les conteneurs Docker (`up`, `stop`, `restart`…)
de sa propre initiative — l'utilisateur gère leur cycle de vie. Si `app` n'est
pas up, le lint échouera : le signaler et le laisser le relancer.

Pas de vérification visuelle (Playwright, captures, navigateur headless) de sa
propre initiative : le lint Twig et `php -l` suffisent par défaut. Ne lancer ce
genre de test que si l'utilisateur le demande, ou si la fonctionnalité est
réellement complexe (logique JS interactive, mise en page qui ne s'évalue pas à
la lecture du CSS/Twig). Les fichiers produits pour vérifier un rendu sont
jetables : les effacer dès la tâche terminée, sans attendre qu'on le demande.
