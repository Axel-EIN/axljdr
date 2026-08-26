# Code, architecture et nomenclature

La philosophie de code — aller au plus simple, une règle vit là où elle sert,
factoriser — est dans [CLAUDE.md](../CLAUDE.md). Ici : la langue, l'arborescence,
les noms, les commentaires et les conventions du projet.

## Langue : le code passe à l'anglais

Le projet a démarré en français — entités `Personnage` et `Lieu`, classes CSS
`vignette` et `grille-vide`, paramètres Twig `titre` et `par_ligne`. La cible est
un code entièrement en anglais, atteinte au fil de l'eau et non en une passe.

- **Tout nom nouveau est en anglais** : fichier, classe CSS, variable CSS,
  paramètre de partial, variable Twig, entité, champ, route, service, méthode.
- **Un nom existant se traduit quand on réécrit ce qui l'entoure**, jamais parce
  qu'on passe à côté : renommer oblige à suivre tous les appels, ça se décide.
- **Le français reste pour ce que lit l'utilisateur** — libellés d'interface,
  textes, back-office — et pour les commentaires et la documentation.

Attendent leur tour : les entités et leurs champs (chantier avec migration de
tables, entité pilote d'abord), les utilitaires CSS globaux (`translucide`,
`grayscale`…), les partials non encore touchés.

## Arborescence

**PHP.** `src/Entity` une entité par fichier, en annotations Doctrine (le projet
est homogène, pas d'attributs) ; `src/Repository` les requêtes nommées, rien de
métier ; `src/Controller` un contrôleur par rubrique publique et un
`Admin<Entity>Controller` par entité éditable ; `src/Form` un `Admin<Entity>Type`
pour le MJ, un `Joueur<Entity>Type` quand le joueur édite ; `src/Service` le
calcul qui n'est ni du contrôleur ni de l'entité ; `src/Twig/AppExtension.php`
les filtres et fonctions de gabarit.

**Templates.** `base.html.twig` porte le socle et **les constantes d'images**
(`NA_*`, `LOCKED_ICO`…), posées en `set` hors bloc. Trois gabarits :
`element.html.twig` (page de détail), `category.html.twig` (page de rubrique),
`other.html.twig` (page de texte). `parts/` les composants réutilisables,
`<rubrique>/` les pages.

**CSS.** Pas de build : les fichiers de `public/css/` sont servis tels quels et
chaque page déclare ce qu'elle charge. À la racine les fichiers globaux, chargés
sur les 89 pages — n'y mettre que du partagé ; `components/` un composant
réutilisé par plusieurs pages, chargé par elles ou par leur gabarit ; `pages/`
ce qui ne sert qu'à une page.

## Noms

- **Fichiers CSS et partials** : le nom du composant, deux mots séparés d'un
  tiret (`picture-grid`, `element-locked`).
- **Classes CSS** : deux mots au maximum, une variante ou un état en classe à
  part (`.picture-grid.emblem`, `a.locked`), cf. CLAUDE.md.
- **Variables CSS** : `--<domaine>-<propriété>` (`--grid-min`, `--cols-desktop`,
  `--image-max`).
- **Routes** : `<rubrique>_<entity>` (`empire_lieu`, `regles_ecole`) et
  `admin_<entity>[_create|_edit|_delete]`.
- **Paramètres de partial** : `items` pour la collection et `item` dans la
  boucle, `variant` pour le motif, puis `title`, `limit`, `empty_message`,
  `wrapper`, `per_row`, `min_size`.

**Un partial ne lit rien du contexte de l'appelant** : tout ce qu'il utilise
arrive en paramètre. C'est ce qui rendait l'ancien `element-vignette` illisible —
il pêchait `category`, `entity`, `ratio` et `size` dans le contexte de la page.

## Commentaires

**Ne jamais écrire de commentaire dans le code.** Deux exceptions : quand
l'utilisateur en demande un, et une information critique à ne pas oublier qui ne
se déduit pas du code. Dans ce cas, **une ligne**, jamais plus.

## Conventions

**Le back-office est piloté par des chaînes.** `back_office/list-element.html.twig`
lit le `table_cols` déclaré par le contrôleur, au format
`champ:Libellé:format:extra` — formats `bool`, `boolInt`, `number`, `symbol`,
`image`, `color`. Un point dans le champ traverse une relation (`clan.nom`).

**Une migration par entité**, écrite à la main, avec sa description. Le
déploiement OVH lance `doctrine:migrations:migrate` tout seul et il n'y a pas de
SSH : une migration doit passer sans échec sur une base déjà à jour. Un
`CREATE TABLE` renuméroté a besoin d'un garde `tablesExist()` suivi d'un
`return` — jamais `skipIf`, qui n'enregistre pas la version.

**L'accès se contrôle dans le contrôleur, pas dans le gabarit.** `@IsGranted`
pour une rubrique entière, une garde explicite qui court-circuite pour un élément
verrouillé. Le gabarit décide de l'affichage, jamais du droit.

**`|default()` ne sait pas distinguer `false` de « pas fourni ».** En Twig,
`false`, `''`, `null` et `[]` sont tous « empty » : `label|default(true)` rend
`true` alors que l'appelant a passé `label: false`. Pour un booléen, écrire
`label is defined ? label : true`.
