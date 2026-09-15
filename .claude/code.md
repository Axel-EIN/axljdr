# Code, architecture et nomenclature

Ici : les migrations, la langue, l'arborescence, les noms et les conventions du
projet. La philosophie de code — aller au plus simple, une règle vit là où elle
sert, factoriser, imbriquer le CSS — et l'interdiction d'écrire des commentaires
sont dans [CLAUDE.md](../CLAUDE.md).

## Migrations

La règle — **une par entité**, écrite à la main, avec sa description, et
rejouable sur une base déjà à jour — est dans [CLAUDE.md](../CLAUDE.md). Ce
qu'elle implique en écrivant le fichier : le nom porte l'horodatage
(`Version20260915140001`), les derniers chiffres ordonnant les migrations d'une
même passe ; un `CREATE TABLE` renuméroté a besoin d'un garde `tablesExist()`
suivi d'un `return` — jamais `skipIf`, qui n'enregistre pas la version.

Elles se comptent par centaines ; les plus récentes portent le palier d'accès et
son remaniement en plusieurs passes, la table `character_unlock`, le statut du
personnage et la table `found_object`.

## Priorité haute : les variantes d'un composant vivent dans son fichier

**Ne jamais surcharger un composant depuis le CSS d'une page.** Quand une vue a
besoin qu'un composant partagé se comporte autrement, la tentation est d'écrire
dans `pages/<page>.css` un sélecteur qui vient l'écraser. Le composant se met
alors à exister en morceaux éparpillés, et son fichier ne dit plus quelles
formes il peut prendre. La variante s'écrit **dans le fichier du composant**,
imbriquée dans sa règle, sous une classe à part :

```css
/* badges.css */
.badge-new {
  top: 1rem;
  left: 0;
  border-radius: 0 0.5rem 0.5rem 0;

  &.rounded { border-radius: 0.5rem; }
}
```

La page n'écrit alors **aucun CSS** : elle pose `badge-new rounded` dans son
gabarit, et c'est tout.

**Ça ne contredit pas « une règle vit là où elle sert »** : cette règle-là
départage ce qui est partagé de ce qui ne l'est pas. Un bloc qui n'appartient
qu'à une page va dans `pages/` ; une variante d'un composant partagé appartient
au composant, pas à la page qui l'utilise en premier — la seconde page qui en
aura besoin la trouvera au lieu de la réécrire.

L'éparpillement est aujourd'hui **partout dans le projet** : chaque fois qu'on
croise une page qui cible un composant pour l'écraser, on rapatrie la variante.
Même logique à l'intérieur d'un fichier — une classe ne s'y déclare qu'une fois
et ses blocs fusionnent (règle dans [CLAUDE.md](../CLAUDE.md)) : `.session-card`
est encore déclarée deux fois dans `pages/news.css`.

## Langue : le code passe à l'anglais

Le projet a démarré en français — entités `Personnage` et `Lieu`, classes CSS
`vignette` et `grille-vide`, paramètres Twig `titre` et `par_ligne`. La cible est
un code entièrement en anglais, atteinte au fil de l'eau et non en une passe.

- **Tout nom nouveau est en anglais** : fichier, classe CSS, variable CSS,
  paramètre de partial, variable Twig, entité, champ, route, service, méthode.
- **Un nom existant se traduit quand on réécrit ce qui l'entoure**, jamais parce
  qu'on passe à côté : renommer oblige à suivre tous les appels, ça se décide.
- **Un renommage se tranche au coût.** Aucun impact base ni migration, et moins
  d'une dizaine d'occurrences à reprendre : on le fait dans la foulée, fichier
  compris. Au-delà, on remonte le compte et l'utilisateur décide.
- **Le français reste pour ce que lit l'utilisateur** — libellés d'interface,
  textes, back-office — et pour la documentation.

Déjà en anglais : les nouvelles entités (`Development`, `Unlock`, `Access`,
`Status`, `PartsTrait`, `PublishableTrait`), tout `src/Service`, les partials
refaits (`parts/picture-grid`, `parts/history`, `parts/badge-access`, `btns/*`,
`rule-lore-library/*`) et les gabarits de page des rubriques (`empire/location`,
`regles/dojo-detail`, `regles/class-detail`, `personnages/character-profil`…).

Attendent leur tour : les entités historiques et leurs champs (chantier avec
migration de tables, entité pilote d'abord — `Personnage`, `Lieu`, `Ecole`…),
les **routes et leurs préfixes d'URL** (`/personnages`, `/regles`, `empire_lieu`,
`admin_ecole`), le **dossier `templates/personnages/`** et les utilitaires CSS
globaux (`translucide`, `grayscale`…). Un dossier de templates et une route se
renomment ensemble ou pas du tout : le premier coûte un `grep`, la seconde casse
les liens externes.

## Arborescence

**PHP.** `src/Entity` une entité par fichier, en annotations Doctrine (le projet
est homogène, pas d'attributs), plus ce qu'elles partagent : un trait par
comportement — `PublishableTrait` pour le palier de publication et ses dates,
`PartsTrait` pour un document en parties — et des classes de constantes sans
table, `Access` et `Status` (vivant / disparu / mort). `src/Repository` les
requêtes nommées, rien de métier ; `src/Controller` un contrôleur par rubrique
publique et un `Admin<Entity>Controller` par entité éditable, plus
`VisibilityTrait` pour la garde d'accès des pages de détail ; `src/Form` un
`Admin<Entity>Type` pour le MJ, un `Joueur<Entity>Type` quand le joueur édite,
et `PublishableFields` pour le bloc de publication commun aux treize entités
publiables ; `src/Service` le calcul qui n'est ni du contrôleur ni de l'entité ;
`src/Command` les commandes de reprise de données (`app:unlocks:backfill`) ;
`src/Twig/AppExtension.php` les filtres et fonctions de gabarit.

**Les services, et ce que chacun porte.** `Visibility` la règle de lecture et
les huit états d'accès ; `Unlocker` l'écriture des débloquages ;
`EntityRegistry` le registre clé → classe / route / libellé ; `ElementUrl`
l'URL d'un élément à partir de sa clé ; `CurrentPlayer` le personnage du
visiteur ; `NewsFeed` les fils de l'accueil ; `ClasseurXP` et
`ClasseurHistorique` les calculs d'XP et le tri de l'historique ; `Baliseur` les
`[Prénom]` et `{Lieu}` ; `Numeroteur` la renumérotation ;
`ParticipationHandler` les participations d'une scène ; `Uploader`,
`FileHandler` et `ImageNormalizer` les fichiers téléversés.

**Templates.** `base.html.twig` porte le socle et **les constantes d'images**
(`NA_*`, `LOCKED_ICO`…), posées en `set` hors bloc. Trois gabarits :
`element.html.twig` (détail), `category.html.twig` (rubrique),
`other.html.twig` (texte). À la racine aussi les deux variantes d'état rendues à
la place d'une page : `element-locked.html.twig` (listé mais pas lisible) et
`element-hidden.html.twig` (ni listé ni lisible, rendu en 404). `parts/` les
composants réutilisables, `btns/` les boutons, `rule-lore-library/` ce que
partagent Règle, Lore et Bibliothèque, `back_office/` le panneau du MJ,
`<rubrique>/` les pages.

**CSS.** Chaque page déclare ce qu'elle charge. À la racine les fichiers
globaux, chargés sur toutes les pages par `parts/head.html.twig` (cf.
[sitemap.md](sitemap.md)) — n'y mettre que du partagé ; `components/` un
composant réutilisé par plusieurs pages, chargé par elles ou par leur gabarit ;
`pages/` ce qui ne sert qu'à une page.

Sont globaux, dans cet ordre : `base`, `metrics`, `colors`, `fonts`, `texts`,
`links`, `lists`, `positions`, `header`, `banners`, `titles`, `components`,
`icons`, `buttons`, `badges`, `cards`, `effects`, `animations`, `flex`,
`footer`. **`forms.css` n'en fait pas partie** : il n'est chargé que par les
pages qui portent un formulaire.

## Noms

- **Fichiers CSS et partials** : le nom du composant, deux mots séparés d'un
  tiret (`picture-grid`, `element-locked`).
- **Classes CSS** : deux mots au maximum, une variante ou un état en classe à
  part (`.picture-grid.emblem`, `a.locked`) — détail dans
  [CLAUDE.md](../CLAUDE.md).
- **Variables CSS** : `--<domaine>-<propriété>` (`--grid-min`, `--cols-desktop`,
  `--image-max`).
- **Routes** : `<rubrique>_<entity>` (`empire_lieu`, `regles_ecole`) et
  `admin_<entity>[_create|_edit|_delete]`.
- **Paramètres de partial** : `items` pour la collection et `item` dans la
  boucle, `variant` pour le motif, puis `title`, `limit`, `empty_message`,
  `wrapper`, `per_row`, `min_size`.

**Un partial ne lit rien du contexte de l'appelant** : tout ce qu'il utilise
arrive en paramètre. Dette connue : `btns/btn-icon-edit.html.twig` et
`btns/btn-line.html.twig` pêchent encore `entity`, `un_element`, `redirect`,
`color` et `size` dans le contexte — le premier a fait tomber l'accueil le jour
où un appelant ne posait pas `color` ; `aventure/episode-card.html.twig` est à
mi-chemin, l'accueil lui passe ses paramètres, `chapter-detail` les lui laisse
pêcher.

## Conventions

**Le back-office est piloté par des chaînes.** `back_office/list-element.html.twig`
lit le `table_cols` déclaré par le contrôleur, au format
`champ:Libellé:format:extra` — formats `string` (défaut), `number`, `symbol`,
`image`, `bool`, `boolInt`, `color`, `access`, `date`, `status`. Un point dans le
champ traverse une relation (`clan.nom`). Détail de chaque format dans
[design.md](design.md), « Colonnes des listes du back-office ».

**L'accès se contrôle dans le contrôleur, pas dans le gabarit.** `@IsGranted`
pour une rubrique entière, et `VisibilityTrait::accessGuard()` pour un élément :
il rend `element-locked.html.twig` — la page « à découvrir » — si l'élément est
listé sans être lisible, et `element-hidden.html.twig` en 404 s'il n'est même pas
listé. Le service `Visibility` porte cette règle et lui seul ; les gabarits
l'interrogent par `element_state`, `element_readable` et `element_listed` pour
décider de l'affichage, jamais du droit.

**`|default()` ne sait pas distinguer `false` de « pas fourni ».** En Twig,
`false`, `''`, `null` et `[]` sont tous « empty » : `label|default(true)` rend
`true` alors que l'appelant a passé `label: false`. Pour un booléen, écrire
`label is defined ? label : true`.
