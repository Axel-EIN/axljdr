# Specs de vue

Ce que chaque vue doit être, écrit au moment de sa revue. Sert de garde-fou plus
tard : une modification qui casse un de ces points est une régression, même si
elle paraît anodine ailleurs.

Ce qui vaut pour toutes les pages d'un même gabarit est décrit une fois dans la
section du gabarit ; les sections de page ne gardent que ce qui leur est propre.

Cf. [checklist.md](checklist.md) pour l'avancement de la revue.

Cf. [newfeatures.md](newfeatures.md) pour ce qui a été écarté et pourquoi.

---

## Gabarit Back-office — `/back-office` et `/admin/{entité}`

**Accès réservé au MJ.** Tout le panneau est derrière `ROLE_MJ` ; la section des
comptes utilisateurs demande en plus `ROLE_ADMIN`, et le titre de la page devient
« Panneau d'Administration » au lieu de « Panneau du Maître ».

Quatre fichiers portent tout le panneau : `back_office/index.html.twig` en est le
gabarit, `list-element.html.twig` rend n'importe quelle liste, `create.html.twig`
et `edit.html.twig` font cinq lignes chacun et vont chercher le formulaire de
l'entité. **Une entité n'écrit donc qu'un fichier, son `_form.html.twig`.** C'est
la garantie à ne pas casser : ajouter une entité ne doit pas ajouter de vue.

Sous le titre, une **navigation** liste toutes les entités ; chaque lien mène à
la liste de cette entité seule. Elle est présente sur toutes les pages du
panneau, listes et formulaires compris.

### Accueil : le résumé par entité

Une carte de résumé par entité, groupées en sections **dans l'ordre de la
navigation visiteur — Aventure, Personnages, Empire, Règles** — puis Comptes
Utilisateurs pour les administrateurs. Chaque section porte son titre, une phrase
de description, et sa rangée de cartes.

Les quatre sections sont une seule boucle sur une table de quatre entrées : leur
libellé et leur clé de catégorie sont la seule chose qui les distingue.

**Quatre cartes par ligne au-dessus de 992px, trois entre 576 et 992px, deux
en dessous.** Les trois paliers tombent sur des paliers Bootstrap, donc les
colonnes suffisent — `col-6 col-sm-4 col-lg-3`, aucune règle CSS à écrire.

Chaque carte est un `back_office/card-element-resume.html.twig` et montre, de
haut en bas :

- le **nombre d'entrées** de l'entité et son libellé, avec en coin deux liens
  d'icône : voir la liste, et ajouter ;
- puis, sur une nouvelle ligne, la **dernière entrée ajoutée** — sa vignette,
  cliquable vers son formulaire d'édition, avec une icône crayon en coin, et son
  titre sous la vignette.

Une entité vide affiche « Il n'y a pas encore de … » à la place de la vignette.

### Liste d'une entité

Même gabarit, avec un tableau et un bouton de création en haut à droite. **Les
colonnes changent d'une entité à l'autre**, en nombre comme en nature : chaque
contrôleur les décrit en chaînes `path:Label:format:extra`, et
`list-element.html.twig` s'occupe du rendu. Sept formats sont disponibles —
`string`, `number`, `symbol`, `image`, `bool`, `boolInt`, `color` — plus des
jetons `{genre}` ou `{anneau}` résolus depuis l'élément pour choisir le bon
placeholder.

Ce qui est commun à toutes les listes : **les deux dernières colonnes sont
toujours l'icône d'édition et l'icône de suppression**, et quand l'entité a des
images, elles occupent les colonnes de gauche et sont cliquables vers le
formulaire d'édition.

### Formulaires

Un par entité, de présentation libre — l'entité dicte ses champs, de 22 lignes
pour un Avantage à 215 pour une Scène. Ce qu'ils ont tous : la navigation du
panneau au-dessus, un `div.col-12` qui les enveloppe, et
`parts/btn-form.html.twig` en pied, dont le libellé passe à « Créer » ou
« Modifier » selon la page.

### Divergence connue

La liste des **Comptes Utilisateurs** est encore un tableau écrit à la main, hors
de `list-element.html.twig`. Ses boutons de création et d'édition sont passés au
squelette partagé ; son tableau reste à migrer, ce qui demande un format pour la
colonne des rôles — c'est un tableau, pas une valeur.

## Aventure › Accueil — `/` et `/aventure/{id}`

Page d'accueil du site pour l'instant : `/` redirige vers la saison courante.
Elle cédera la place à une rubrique « Quoi de neuf » qui reste à définir.

La page est un **fil vertical des chapitres d'une saison**, un bandeau par
chapitre, du plus récent au plus ancien. « Le plus récent » n'est pas une date :
il n'y en a pas en base, c'est le **numéro le plus élevé de la saison**, donné
par le MJ. Aucun défilement automatique n'est en jeu — la page s'ouvre sur le
dernier chapitre parce qu'il est rendu en premier, rien de plus.

### Barre de saison

Une bande horizontale à la couleur de la saison ouvre la page, avec une flèche
de chaque côté vers la saison précédente et suivante. Les deux flèches gardent
le même gabarit qu'elles soient actives ou non, sinon le titre entre elles n'est
plus centré.

Elle annonce si la saison est en cours, et **la saison en cours n'affiche jamais
son titre** — « SAISON 3 en cours » plutôt que « SAISON 2 - LES VOILES DE LA
PROPHÉTIE ». C'est délibéré : le titre en dit trop sur ce qui n'est pas encore
joué. Même règle dans `parts/historique.html.twig`, qui écrit « Saison en cours »
à la place du titre sur la bande de la saison courante.

« En cours » se déduit de l'absence de saison suivante — pas de brouillon ni de
publication, cf. [newfeatures.md](newfeatures.md).

### Bandeau de chapitre

Un `header` sur fond d'image du chapitre, assombri par un dégradé — du texte sur
une image ne va jamais sans assombrissement (cf. design.md). L'image et la
couleur arrivent en variables CSS sur la balise, l'assombrissement vit dans
`pages/campaign.css` ; il est propre à ce bandeau et **ne se partage pas** avec le
fond de la page Épisode, qui couvre une tout autre hauteur. Le header porte, de
haut en bas :

- **« CHAPITRE N »** en haut à gauche, suivi d'un `hr` ;
- les **icônes des lieux** traversés pendant le chapitre, en haut à droite,
  cliquables vers la page du lieu ;
- le **titre du chapitre** en gros au centre, en `heading-chapter` ;
- la **citation** juste en dessous, encadrée par deux grilles de portraits en
  absolute : les **joueurs à gauche**, les **non-joueurs à droite**, cliquables
  vers le profil du personnage ;
- un bouton **« Voir les Episodes »** qui déplie les cartes d'épisode. Il ne
  défile pas : c'est un `button`, pas une ancre — le défilement gênait plus
  qu'il n'aidait.

**Sur mobile, les lieux et les deux grilles de portraits disparaissent** : le
bandeau passe en mode compact et l'ancrage absolu chevaucherait le titre.

### Cartes d'épisode

Dépliées sous le bandeau, une par épisode : image, titre avec le numéro
d'épisode, court résumé, et lien vers le détail de l'épisode. Si le visiteur
connecté a joué l'épisode avec un de ses personnages, le portrait de celui-ci
apparaît en surimpression avec l'XP gagné — ou « MORT ».

### Boutons MJ

Un bouton d'insertion de chapitre en haut de chaque bandeau — « Ajouter » sur le
dernier, « Insérer » ailleurs. Un bouton d'édition à gauche de « CHAPITRE N ».
Une fois les épisodes dépliés, chaque carte porte son bouton d'édition, et une
grande icône `+` ajoute un épisode.

Le bouton « Voir les Episodes » s'affiche aussi pour le MJ quand aucun épisode
n'est encore visible, pour qu'il puisse atteindre ses propres boutons.

---

## Règles › Rubrique — `/regles`

Gabarit Category, quatre sections : **Règles de Base**, **Les Classes**, **Les
Bibliothèques**, **Règles Annexes**. Comme sur la rubrique Empire, chacune montre
tout et porte à droite de son titre son bouton « Ajouter X » pour le MJ, réduit à
son icône sous 576px.

**Trois d'entre elles sont des grilles de vignettes** (`parts/vignette-liste`),
toutes **en ratio 4:3** : c'est ce format qui signale une règle, quelle que soit
la taille de la vignette.

- **Règles de Base** — quatre par ligne, donc de grandes vignettes. La taille
  tranche volontairement avec les archives et les lieux de la rubrique Empire,
  qui sont en six par ligne.
- **Les Bibliothèques** — également quatre par ligne, mêmes grandes vignettes.
  Ce sont des regroupements : sorts, compétences, avantages, objets.
- **Règles Annexes** — six par ligne, donc des vignettes plus petites, mais le
  ratio 4:3 reste. La section mêle règles et bibliothèques annexes, fusionnées
  côté contrôleur.

**Les Classes** est une grille d'emblèmes (`variant: 'emblem'`), **six par
ligne**, avec le nom sous chaque icône — des emblèmes en noir sur blanc, pas des
illustrations.

**Sur mobile, les quatre sections passent à deux par ligne.**

## Empire › Rubrique — `/empire`

Gabarit Category, quatre sections : **Les Archives**, **Les Factions**, **Les
Lieux**, **Lore**.

**Chaque section affiche tous ses éléments, sans limite** : aucune sous-page ne
les liste, donc il n'y a rien vers quoi renvoyer. Et chacune porte à droite de son
titre un bouton « Ajouter X » pour le MJ, qui perd son libellé mais garde son
icône sous 576px.

### Archives, Lieux et Lore

Les trois passent par `parts/vignette-liste.html.twig` — la grille de vignettes
seule, sans carte, le gabarit Category posant déjà le titre et son divider.
**Six par ligne, deux sous 576px.**

Elles héritent donc de tout ce que porte `parts/picture-grid.html.twig` : le
nom sous la vignette, l'**état verrouillé** — cadenas battant au centre,
désaturation et nom remplacé par « ??? » pour un joueur ; simple cadenas de coin
et vignette intacte pour le MJ —, et le **badge PDF** en haut à droite pour les
Lore qui en sont un.

Les Lieux montrent leur illustration et non leur icône (`force_image`). **Au
survol, l'illustration s'assombrit et l'icône du lieu apparaît au centre** — le
bâtiment tel qu'il figure sur la carte. Le mécanisme vit dans `picture-grid`,
donc toute entité qui a une icône et montre son illustration en profite.

### Factions

Deux sous-colonnes, larges deux tiers / un tiers :

**Clans Majeurs** — un texte d'introduction, puis les emblèmes **quatre par
ligne, deux sous 576px**, chacun avec son nom sous lui.

**Autres** — les clans mineurs, la Confrérie, les familles impériales, les
ennemis. Un texte d'introduction, puis les emblèmes **deux par ligne, trois sous
576px**, nettement plus petits et sans libellé : le nom est en infobulle.

Les deux passent par `parts/picture-grid.html.twig`, la même brique que Les
Classes de la rubrique Règles : un nombre de colonnes par palier, la propriété
qui porte l'image, l'affichage ou non du nom, et une largeur maxi d'emblème.

**Au survol, un emblème zoome et un reflet le balaie une fois**, comme celui qui
tourne en boucle sur l'emblème d'une page Clan. Le reflet est masqué à la
silhouette de l'emblème, sinon la bande blanche traverserait aussi le rectangle
transparent autour de lui.

## Personnages › Rubrique — `/personnages`

Gabarit Category, avec deux sections : **Personnages Joueurs** puis
**Personnages non-joueurs**, ces derniers découpés en sous-sections par clan,
avec un groupe « Autres » en fin.

### Le bloc de filtres

Il s'insère avant la zone de contenu et **mord vers le haut sur le bandeau
imagé** — un cartouche à fond texturé qui chevauche la frontière. Trois filtres
en trois colonnes : Saison, Chapitre, Épisode. Chaque colonne empile son icône et
son libellé, puis sa liste déroulante en dessous.

Choisir un filtre restreint les personnages à ceux qui ont participé à la saison,
au chapitre ou à l'épisode retenu. **Par défaut, le chapitre le plus récent** —
dernière saison, dernier chapitre, tous ses épisodes. Un chapitre qui
n'appartient pas à la saison choisie, ou un épisode hors du chapitre choisi, est
ignoré plutôt que d'être appliqué de travers.

Une icône en haut à droite du cartouche remet les filtres à l'état initial ; elle
n'apparaît que lorsqu'un filtre est actif.

**Sur mobile les trois filtres s'empilent**, chacun sur une ligne — icône,
libellé, liste. Une marge est réservée à droite pour que le bouton de
réinitialisation respire. Et **le cartouche déborde jusqu'aux bords de l'écran** :
ses bords déchirés sortent du cadre, ce qui donne l'impression d'une coupure. Le
débordement est piloté par une seule valeur, `--bleed`, qui reprend le padding du
`.container` et décale d'autant le contenu pour qu'il reste aligné sur le reste
de la page.

### Les cartes de personnage

`personnages/card-personnage.html.twig`. Une carte verticale remplie par
l'illustration du personnage, avec **l'emblème de son clan accroché en haut à
gauche, légèrement débordant**, un cartouche pour son nom et prénom, et en
dernière ligne le pseudo de son joueur. Un dégradé noir vers le bas rend le tout
lisible. Au survol, la carte zoome.

Deux états se posent par-dessus : **mort** — cartouche en rouge, icône de mort,
carte translucide qui redevient opaque au survol — et **verrouillé** — carte
grisée et translucide, cadenas battant, non cliquable pour un joueur.

**Sur mobile la carte devient une bande horizontale** : le ratio 9:16 est ramené
au quart de sa hauteur et l'illustration est recadrée sur le visage, situé vers
16% du haut. Le nom passe en bas à gauche, le pseudo du joueur en bas à droite,
les titres seuls à la ligne suivante. L'emblème reste accroché en haut à gauche.

Les surimpressions de mort et de cadenas sont recalibrées : réglées pour un
portrait étroit et haut, elles dépassaient la hauteur d'une bande.

### Sous-sections par clan

Les libellés des clans — « Clan du Crabe », « Familles Impériales » — sont une
table dans le gabarit : la base ne connaît que `Crabe`, pas sa forme longue.
**Un clan absent de cette table voit ses PNJ rejoindre « Autres »**, jamais
disparaître.

## Compte et authentification

Six pages, **toutes sur le gabarit Other** : Connexion, Inscription, Mot de passe
oublié, Mot de passe, Changer d'avatar, Mon compte. Chacune n'écrit que son titre
et son contenu ; le fil d'Ariane, les messages flash, le titre et sa carte
viennent du gabarit.

Les cinq qui portent un formulaire le posent dans un `.formulaire-etroit` — un
formulaire n'a pas besoin de toute la largeur d'une carte de page.

**Mon compte** est la seule à surcharger `cartes` : deux cartes côte à côte,
moitié-moitié. À gauche les **informations personnelles** — pseudo, e-mail, mot
de passe, statut de vérification, rôles, avatar — chacune avec son lien de
modification quand elle en a un. À droite **Mes Personnages**, avec pour chacun
son portrait cliquable vers son profil et, s'il en a une, le lien vers sa fiche
privée. Cette grille lui est propre : c'est le seul endroit du site où un
personnage est montré avec sa fiche privée.

**Deux routes, une page** : `/reset_pass/{token}` rend le même template que
`/mon_compte/password/edit`.

**La modale de connexion vient de `base.html.twig`**, jamais d'une page — deux
pages l'incluaient en plus, ce qui la dupliquait avec son `id`.

## Variantes d'état

**Élément verrouillé** — `parts/element-locked.html.twig`, rendu à la place du
contenu quand un élément est marqué `locked` et que le visiteur n'est pas MJ.
Il reprend le gabarit Element : une carte de page, un placeholder grisé surmonté
d'un cadenas qui bat, puis un texte d'attente propre à l'entité — un lieu, une
archive et un personnage n'attendent pas la même chose.

**Aucune saison** — `aventure/aucune-saison.html.twig`, sur le gabarit Other, quand
la base n'a encore aucune saison. Un texte d'attente et, pour le MJ, le bouton de
création de la première saison.

Les deux textes d'attente partagent la classe `texte-attente` : centrée et en
italique, contrairement au texte justifié d'une carte ordinaire.

## Gabarit Element

`element.html.twig` et tout ce qui l'étend. CSS dans
`components/element.css`.

**Tête de page** — fil d'Ariane, puis titre `h1` suivi de son divider. Le fil
disparaît sous 576px, le divider sous 992px.

**Carte de page** — une `card card-page` qui prend toute la largeur du
`.container`. C'est la carte principale de la page ; elle porte le rayon du site
et l'espace sous son dernier bloc.

**Image de tête** — `parts/card-top-image.html.twig`, en haut de la carte, sur
toute sa largeur, sans être rognée par un padding :

- ratio **16/9** (`crop-169`) ;
- **desktop** : aucun dégradé noir vers le bas — le titre reste au-dessus de
  l'image, avant le divider, le dégradé n'aurait rien à rendre lisible ;
- **sous 992px** : un dégradé noir vers le bas apparaît et le titre s'affiche
  dessus en blanc. Le titre est **dupliqué** dans le paramètre `titre` du
  composant plutôt que déplacé — choix assumé de stabilité, cf. design.md ;
- **sous 576px** : l'image fixe sa hauteur à **42vh** pour ne pas trop réduire
  ses détails et rester mise en valeur ; en échange elle se rogne sur les côtés.

**Zone de texte** — `card-description`, en version compacte par défaut, ou
`aerated` quand la page a de la place à donner au texte.

**Fin de carte** — le composant d'ornement, qui sert de divider et de décoration
de fin.

**Cartes empilées** — chaque carte suivante s'ouvre pleine largeur sous la
précédente, au gabarit des cartes à titre : titre suivi de son divider, puis le
contenu. L'écart entre cartes vient de la grille, aucune carte ne le déclare.

**Grille de vignettes** — `parts/picture-grid.html.twig` en `variant: 'thumbnail'` :
images aux **coins arrondis en diagonale** (haut-gauche et bas-droite) avec le
titre dessous. **6 colonnes** au-delà de 992px, **3** entre 768 et 992, **2** en
dessous.

**Élément verrouillé** — un élément marqué `locked` n'est pas accessible aux
joueurs : le contrôleur rend la vue `parts/element-locked.html.twig`, qui reprend
le même gabarit avec un placeholder grisé, un cadenas qui pulse et un texte
d'attente. Le MJ, lui, voit la page normale.

**Vignettes verrouillées** — dans les grilles, un élément verrouillé apparaît
grisé et translucide, non cliquable, son titre remplacé par « ??? », avec un
cadenas qui pulse et une infobulle « À débloquer ». Pour le MJ il reste en
couleur et cliquable, avec un simple cadenas en coin signalant qu'il est caché
aux joueurs.

---

## Gabarit Other

Pages qui ne sont que du texte : À propos, CGU, Mentions Légales, Politique de
Confidentialité. `templates/other.html.twig`, plus `components/other.css`.

C'est le **gabarit Element sans ses accessoires**. Il en reprend exactement le
DOM — `main.container-fluid.element-page`, puis
`.container > section > .row > .col-12 > .card.card-page` — et hérite donc de
tout ce que la section Gabarit Element décrit : bord-à-bord sous 992px, coins et
bordure qui tombent au même palier, marge basse qui fusionne avec le footer,
`hr-title` masqué sur mobile, ombre de carte.

Ce qu'il n'a pas, et n'aura pas : image de tête, boutons MJ, sommaire, plusieurs
colonnes, carte « Autres X ». Une page qui en aurait besoin n'est pas une page
Other, c'est une page Element.

### Ce qu'une page écrit

Son titre et son contenu, rien d'autre :

```twig
{% extends 'other.html.twig' %}

{% set titre = 'Mentions Légales' %}

{% block contenu %} … {% endblock %}
```

Une page qui veut **plusieurs cartes, ou des colonnes**, surcharge `cartes`
plutôt que `contenu` : elle reprend alors la main sur la rangée et pose ses
`col-*` elle-même. C'est le cas de « Mon compte », qui en met deux côte à côte.
Le fil d'Ariane, les messages flash, le titre et le divider restent au gabarit.

**Le titre est écrit une seule fois.** Le gabarit le lit pour le `<title>`, pour
le fil d'Ariane — un seul niveau, sans lien parent — et pour le `h1`
(`heading-elementdetail`, suivi de son `hr`). Un titre recopié est une
régression : c'est comme ça que « A propos » et « À propos » avaient divergé.

### Contenu

Du texte à nu dans la carte, pas de `card-description` : c'est donc **la carte
qui porte le padding** (3rem, 1rem sous 992px), là où les pages Element le font
porter à leur zone de texte.

Chaque titre de section interne est un `h2.heading-cardtitle` suivi de son `hr`
— un titre de section porte toujours son divider (cf. design.md). Tout ce
qui appartient au document vit dans la carte, y compris une ligne de date comme
le « Dernière mise à jour » des CGU.

**Aucun utilitaire Bootstrap de géométrie** dans ces pages : le gabarit et son
CSS pilotent tout, y compris la marge basse du dernier bloc, qui s'ajouterait
sinon au padding de la carte. Les utilitaires restent légitimes sur ce qui n'est
pas de la géométrie — un `text-muted`, une classe de visibilité.

### Contact n'est pas une page Other

`/contact` porte un formulaire, pas un document, et garde son gabarit propre.
Elle n'a donc ni la classe de titre, ni le divider, ni la carte, ni le
bord-à-bord mobile des quatre autres. C'est assumé, pas un oubli.

---

## Empire › Archive — `/empire/archive/{id}`

Page la plus simple du gabarit : une combinaison directe des briques ci-dessus,
sans rien de spécifique en dehors de trois points.

**Composition** — `card-page`, image de tête, texte en `aerated`, ornement de
clôture ; puis une seconde carte pleine largeur « Autres Archives » contenant la
grille de vignettes des autres archives.

**Texte en version aérée** — la page n'a rien d'autre à afficher, le texte
descriptif est donc écrit un peu plus gros et avec plus d'air.

**Auteur** — s'il est renseigné, il apparaît dans la carte, sous le texte.

**Liste des autres archives** — toutes sont listées, sans plafond, contrairement
aux autres blocs « Autres X » du site qui coupent à 12.

---

## Empire › Clan — `/empire/clan/{id}`

Gabarit Element. La page présente un clan : son emblème, sa description, sa
citation, ses familles, ses écoles, ses territoires, ses lieux, ses personnages,
et une section « Autres » pour le reste.

Le MJ peut éditer depuis la page : une icône crayon dans la bannière, une par
bloc de famille, et deux boutons d'ajout — une famille, une école.

### Bandeau d'intro

Un bloc dédié, `parts/clan-banner.html.twig`, dont le socle est partagé avec la
page Classe (`components/banner-intro.css`) ; seules la vidéo, le
champion et le badge « Clan Majeur » sont propres au clan.

En fond, **une vidéo qui tourne en boucle**, muette, recouverte d'un dégradé qui
la teinte à la couleur du clan et l'assombrit — sans quoi rien ne serait lisible
par-dessus (cf. design.md). Sans vidéo, l'illustration du clan tient le
même rôle.

À gauche, **l'emblème en grand**, parcouru d'un reflet qui repasse en boucle. Au
survol il révèle une icône de lecture : un clic rend la vidéo visible et
audible, et une icône permet d'y couper le son. Sous l'emblème, la citation puis
**le champion du clan** s'il est désigné, cliquable vers son profil. À droite, le
texte de description.

Tout en bas du bandeau, **une ligne d'onglets vers les autres clans** — majeurs
comme mineurs et autres factions — pour passer de l'un à l'autre sans repasser
par la rubrique. L'onglet actif porte la couleur du clan, qui souligne aussi
toute la bannière.

### Blocs, dans l'ordre

Le bandeau n'est pas collé à ce qui suit : les cartes gardent l'écart du gabarit
Element, piloté par `--gap`, horizontalement comme verticalement.

1. **Familles** (majorité de la largeur) et **Écoles** (le reste) côte à côte ;
2. **Territoires**, pleine largeur ;
3. **Lieux**, pleine largeur ;
4. **Autres** (même largeur que Familles) et **Personnages** (même largeur
   qu'Écoles) côte à côte.

Familles/Écoles et Autres/Personnages partagent donc le même rapport de largeurs.
Si le clan n'a pas de texte « Autres », Personnages prend toute la largeur.

**Familles** — une ligne par famille, séparée de la suivante par un divider :
l'emblème de la famille à gauche, avec sous lui le personnage qui la dirige s'il
y en a un — son Daimyo —, et à droite le nom, le bonus de trait, puis la
description.

**Écoles** — une liste de vignettes, **une seule par ligne**, chacune menant à la
page de l'école. Le nom de l'école s'écrit sous la vignette : il n'est pas sur
l'image. C'est le même `parts/picture-grid.html.twig` que les blocs « Autres X »,
avec `per_row: 1`.

**Territoires** — une carte du territoire en grande image, et un texte.

**Lieux** — le même rendu que les blocs « Autres X », mais sur toute la largeur
et sans limite : tous les lieux du clan, nom sous la vignette, cliquables vers la
page du lieu. **Quatre par ligne en desktop, trois sous 992px, deux sous 576px.**

**Personnages** — le composant partagé `parts/picture-grid.html.twig`,
**deux vignettes carrées par ligne**, sans nom, cliquables vers le profil.

### Mobile

**Le bandeau d'intro occupe à peu près la hauteur de l'écran.** Tout s'y empile
verticalement pour tenir dans une vue : le titre du clan, l'emblème en grand, la
citation, le champion, le texte de description, et la ligne d'onglets qui devient
un menu déroulant. Le fond vidéo et son dégradé restent.

Le titre quitte le flux et vient se poser en haut du bandeau, comme sur Profil et
Fiche Personnage.

En dessous, **les six cartes s'empilent sur toute la largeur**, dans le même
ordre qu'en desktop : Familles, Écoles, Territoires, Lieux, Autres, Personnages.

## Règles › Classe — `/regles/classe/{id}`

Gabarit Element, **même structure que la page Clan en plus simple** : bandeau
d'intro, puis une seule rangée de deux cartes. Elle partage le socle du bandeau
(`components/banner-intro.css`) et la mécanique de grille.

### Bandeau d'intro

`parts/classe-banner.html.twig`. Une image de fond assombrie par un dégradé, le
logo de la classe, une citation, le texte de description, et tout en bas la ligne
d'onglets vers les autres classes — qui devient un menu déroulant sous 992px.

Pas de vidéo ni de champion, ce sont des champs du Clan. Le bandeau clan est
prêt à en accueillir une le jour où une classe en aura : les règles de la vidéo
vivent dans le composant, pas dans la page.

**Sur mobile, le bandeau occupe à peu près la hauteur de l'écran** et tout s'y
empile verticalement, comme sur Clan. Le titre quitte le flux et se pose en haut
du bandeau.

### Les deux cartes

Une rangée, deux cartes, **le même rapport de largeurs que Familles/Écoles sur la
page Clan** — la majorité de l'espace à gauche, le reste à droite. Elles
s'empilent pleine largeur sous 992px, et l'écart entre elles vient du `--gap` du
gabarit.

**Écoles de la classe** (à gauche) — `parts/picture-grid.html.twig`,
**quatre par ligne, deux sous 576px**.

**Personnages** (à droite) — `parts/picture-grid.html.twig`, portraits carrés
sans nom, **deux par ligne**, quatre entre 576 et 992px où la carte est pleine
largeur. Limité à 16.

Rien d'autre : la page s'arrête là.

## Empire › Lieu — `/empire/lieu/{id}`

Même gabarit qu'Archive, avec un contenu plus riche : une carte de page longue,
puis « Autres Lieux » en bas. Ce qui lui est propre :

**Surimpressions sur l'image, dès le desktop** — à gauche l'icône du lieu et son
surnom, à droite le mon du clan cliquable, le type et la population.

**Le dégradé est donc nécessaire dès le desktop**, pour rendre ce texte lisible :
l'image passe `gradient-desktop`. C'est l'application directe de la règle du
design system — jamais de texte sur une image sans assombrissement.

**Sur mobile, ces informations descendent dans le contenu** : sous l'image, un
bloc centré avec l'icône du lieu à gauche et, à droite, la liste
« Clan / Type / Habitants », le clan en lien avec son mon.

**Texte descriptif en `aerated`**, comme sur Archive.

**Sections internes, séparées par l'ornement décoratif** :

- **Carte** — la carte interactive, **masquée sur mobile** pour l'instant. Aura
  sa propre spec.
- **Plan** — l'image du plan du lieu, pleine largeur.
- **Quartiers** — texte optionnel, en `aerated` lui aussi.

**Titres de ces sections : variante décorée.** Contrairement aux titres de carte
du reste du site, alignés à gauche et suivis d'un divider, ceux-ci sont centrés
sous leur ornement et sans divider — c'est l'ornement qui fait la séparation.
Classe `heading-cardtitle decorated`.

**Fin de la carte de page** — un dernier ornement, puis le bouton MJ.

**Carte « Historique des visites »** — affiche le composant partagé `historique`,
le même que le Profil personnage.

**Carte « Autres Lieux »** — grille de vignettes comme Archive, mais limitée à
12 lieux tirés au hasard, et en forçant l'illustration plutôt que l'icône.

---

## Personnages › Profil — `/personnages/profil/{id}`

Gabarit Element, **mais sans image de tête** : l'illustration du personnage vit
dans une carte, pas en bandeau.

### Desktop

Deux rangées de deux cartes, la large à gauche puis l'étroite à droite, avec
l'écart du gabarit (`--gap`).

1. **Informations** (un tiers) — l'illustration du personnage en haut, bord à
   bord dans la carte qui la découpe à son rayon, puis empilés : l'XP de création
   et de progression avec le total, le clan et la famille avec leurs mon, l'école
   avec l'icône de sa classe et le rang, le joueur, et un bouton vers la fiche de
   personnage. Chaque entrée mène à la page correspondante.
2. **Description** (deux tiers) — les titres du personnage en cartouche, puis son
   texte en `card-description compact`, la même mise en page que le résumé d'une
   scène sur la page Épisode.
3. **Historique** (deux tiers) — `parts/historique.html.twig`, **du plus récent au
   plus ancien**, puis en dernière ligne la création du personnage avec son XP de
   départ, qui est l'événement le plus ancien.

   Faute d'horodatage en base, « récent » se lit sur les numéros donnés par le MJ,
   du plus élevé au plus bas, à chacun des quatre niveaux : saison, chapitre,
   épisode, scène. Le tri vit dans `ClasseurHistorique`, donc les pages Lieu et
   Profil le partagent.

   Chaque ligne d'épisode porte **le total d'XP gagné dedans**, à droite, replié
   comme le reste ; les scènes dépliées détaillent ce total. Rien ne s'affiche
   quand il vaut zéro, ce qui est toujours le cas sur la page Lieu — un lieu ne
   gagne pas d'XP.
4. **Autres PJs** ou **Autres PNJs** selon le personnage (un tiers) —
   `parts/picture-grid.html.twig`, **deux par ligne**, limité à 12.

### Mobile

L'ordre change, et c'est le seul endroit du site où il change : **l'illustration
passe en premier**, en bandeau de 125px collé au haut de la page, avec un dégradé
noir vers le bas pour que **le nom du personnage s'y pose en blanc**. Le `h1` du
gabarit quitte le flux et vient s'aligner en bas de ce bandeau.

Suivent, empilées pleine largeur : la **description**, puis les
**informations**, l'**historique**, et les **autres personnages** — toujours deux
portraits par ligne.

L'illustration est donc écrite deux fois dans le gabarit, une fois en bandeau et
une fois dans la carte : les deux ne vivent pas dans le même parent, aucune règle
CSS ne peut faire passer un élément de l'un à l'autre. C'est l'inverse des
descriptions de famille sur la page Clan, où les deux places étaient dans la même
grille et une seule occurrence suffisait.

### Personnage verrouillé

Un personnage marqué `locked` rend `parts/element-locked.html.twig` à la place de
tout le contenu, comme les autres entités du gabarit Element.

## Personnages › Fiche — `/personnages/fiche/{id}`

Gabarit Element, **sans image de tête** comme Profil. Toute la fiche tient dans
**une seule carte de page**, dont l'intérieur est subdivisé en deux colonnes —
deux tiers à gauche, un tiers à droite, comme partout ailleurs.

Le plus gros gabarit du site, et le seul dont le contenu est presque entièrement
calculé : rangs de traits, franchises de rang gratuit selon la famille et
l'école, réductions d'avantages selon le clan et la classe, modificateurs de
combat.

### Desktop

Deux rangées à l'intérieur de la carte :

1. À gauche, les **informations générales** — clan, famille, classe, école, rang,
   expérience gagnée, dépensée et restante —, un divider, puis le **mandala des
   anneaux** : les cinq anneaux disposés en croix, chacun avec ses deux traits et
   leur valeur, l'XP total au centre, et le bouton d'édition de la fiche.
   À droite, l'**illustration du personnage**.
2. À gauche, les **avantages et désavantages** puis le **tableau des
   compétences** ; à droite, les **statistiques de combat**.

Les blocs internes de la carte partagent une même classe, `sheet-bloc` : padding
zéro, coins à 0.5rem, contenu découpé — leur tableau ou leur cartouche touche
leurs bords.

### Mobile

La mise en page change complètement. **L'illustration passe en tête**, et reçoit
en surimpression les valeurs des anneaux et de leurs traits — le mandala, qui ne
tient pas dans la largeur, est masqué. Suivent, empilés sur le fond de la carte :
les informations, le tableau des compétences, puis celui des statistiques de
combat.

### Le niveau fusionné, et l'exception qu'il entretenait

La carte était le même élément que sa colonne, `col-12 card card-body` — seul
endroit du site où les deux niveaux étaient confondus. Le gabarit Element annule
le padding de ses colonnes sous 992px pour que les cartes atteignent les bords de
l'écran ; il devait donc **excepter les `.card-body`** de cette règle, sinon la
fiche perdait son padding intérieur avec.

Les deux niveaux sont séparés — `col-12` puis `card card-page` — et l'exception a
disparu de `components/element.css` : la règle s'applique maintenant à toutes les
colonnes sans distinction. La fiche porte son padding elle-même, dans
`pages/character-sheet.css`.

## Aventure › Épisode — `/aventure/episode/{id}`

**Gabarit partagé, en-tête propre.** La page n'étend pas `element.html.twig` —
son en-tête est un bandeau de chapitre, pas un fil d'Ariane — mais son corps
reprend le gabarit des pages de détail : `container-fluid.element-page >
.container > section > .row > col-*`. Elle charge donc `components/element.css`
et hérite de l'écart entre blocs, du bord-à-bord mobile et des marges du
gabarit. `pages/episode.css` ne garde que ce qui lui est propre.

### Bandeau de chapitre

Reprend la présentation d'un chapitre de la page Aventure : grande image de fond,
titre du chapitre en Shogun, citation. Il contient aussi :

- **la ligne de vignettes d'épisodes**, qui permet de basculer d'un épisode à
  l'autre dans le chapitre ; l'épisode courant est mis en avant ;
- **les grilles de portraits** (`chapter-characters`), le même composant que les
  sections de chapitre de la page Aventure : joueurs à gauche, non-joueurs à
  droite. **Différence propre à cette page** : seuls les personnages impliqués
  dans l'épisode courant ne sont pas grisés.

**Sur mobile** : les grilles de portraits disparaissent faute de place, et la
ligne de vignettes se réduit à un menu déroulant de sélection d'épisode.

### Titre de l'épisode

Posé sur un cartouche texturé (`bg-stroke-container`), en style Present via
`heading-episodedetail`.

### Carte de contenu

Une `card card-page`, comme les pages de détail, mais **plus étroite sur grand
écran** : sa cellule de grille est en `col-12 offset-xl-1 col-xl-10`, soit dix
colonnes sur douze avec une colonne vide de chaque côté à partir de 1200px —
environ 920px de large. En dessous de ce palier elle occupe toute la largeur.
C'est la grille qui porte cette différence, pas la carte : `card-page` reste la
même brique qu'ailleurs.

Empile les scènes de l'épisode, chacune rendue par le composant
`aventure/scene-detail.html.twig`.

**Une scène** — une image, et par-dessus :

- en haut à gauche, le lieu et le moment de la journée ;
- en haut à droite, la ligne des personnages non-joueurs ;
- en bas à droite, la ligne des personnages joueurs avec leur gain d'XP ;
- en bas à gauche, le titre de la scène.

Puis le texte descriptif. **Les noms de personnages et de lieux y sont des
liens** : le balisage est fait à l'enregistrement par le service `Baliseur`, qui
transforme les `{Nom}` saisis par le MJ en liens HTML stockés en base — le
template ne fait que les restituer en `raw`.

Chaque scène se termine par un ornement décoratif, avec les boutons MJ modifier
et supprimer s'il est connecté.

**Sur mobile** : les lignes de personnages disparaissent de l'image faute de
place, et la ligne lieu + moment se simplifie en une ligne de texte blanc
« Lieu, Moment ».

### Fin de page

- **Classement des joueurs** après la dernière scène, avec l'XP gagné, surmonté
  d'un libellé de résultat si l'épisode cloturait une quête — succès, mitigé ou
  échec ;
- en bas de carte, un bouton **Retour** à gauche pour remonter en haut de page,
  et à droite le passage à l'**épisode suivant** du chapitre.

**Sous 576px**, les bords de la carte fusionnent avec ceux de l'écran, comme le
fait `card-page` sur les pages de détail — le `main` et sa cellule de grille
perdent leur gouttière, et la carte son rayon.

**Le texte des scènes** utilise `card-description compact`, le cran intermédiaire
de l'échelle de texte de carte — entre la base et le `aerated` d'Archive et Lieu.
Son conteneur `.scene-body` ne sert plus qu'à ancrer le titre de scène, qui vient
se poser sur le bas de l'image.

---

## Règles › École — `/regles/ecole/{id}`

Gabarit Element, avec image de tête. Une carte d'intro, puis Techniques et
Personnages côte à côte, et le bloc « Autres Écoles » pour finir.

### Carte d'intro

L'image de l'école, avec **l'emblème de son clan en haut à droite**, cliquable
vers la page du clan.

Par-dessus l'image, **trois colonnes** : le trait bonus, la liste des compétences
offertes et la liste d'équipement. Texte blanc, rendu lisible par un halo noir
flouté derrière chaque valeur — du texte sur une image ne va jamais sans
assombrissement (cf. design.md). C'est pour ça que cette page passe
`gradient-desktop` à `parts/card-top-image.html.twig` : elle superpose autre
chose que le titre.

Sous l'image, la description de l'école en `card-description aerated`.

**Sur mobile la zone des trois colonnes redescend sous l'image**, dans la carte :
elle est simplement placée après l'image dans le DOM et n'est en absolute qu'à
partir de 992px. Sur le fond clair de la carte, le halo noir et les ombres de
texte disparaissent.

### Techniques et Personnages

Une rangée : **Techniques à gauche** (deux tiers), **Personnages à droite** (un
tiers), qui s'empilent pleine largeur sous 992px.

**Techniques** — jusqu'à cinq techniques, une par rang, chacune avec son numéro
de rang à gauche à la couleur du clan, puis son nom et sa description. Les rangs
vides ne s'affichent pas : une école de bushi en a cinq, une de shugenja une
seule, sans rang.

Quand l'école a des sorts, **une carte Magie s'empile sous les Techniques** —
affinité, déficience, sorts de départ. C'est le cas des écoles à technique
unique. L'écart entre ces deux cartes est celui du gabarit, `--gap`, pour qu'il
ressemble à celui entre les rangées.

**Personnages** — `parts/picture-grid.html.twig`, **deux par ligne**, quatre
entre 576 et 992px où la carte est pleine largeur.

### Autres Écoles

Le bloc de fin, `parts/picture-grid.html.twig`, en auto-fill : deux par ligne
sur mobile, six en desktop, sans palier écrit.

## Règles › Règle — `/regles/rule/{id}`

**Gabarit Element** — fil d'Ariane, titre, divider, puis la `card card-page` avec
son image de tête.

### Sommaire posé sur l'image

Sur l'image de tête, un sommaire aligné **à gauche, à 1rem du bord, centré
verticalement**. Il liste les parties de la règle en liens d'ancre : un clic
amène à la section correspondante, plus bas dans la carte. Une règle peut avoir
plusieurs parties.

**Sa lisibilité vient d'un cartouche flouté, pas d'un dégradé** — classe
partagée `.blur-cartouche` (`effects.css`) : coins arrondis, bordure claire très
transparente, fond légèrement teinté et `backdrop-filter: blur(16px)`. Le même
effet habille le titre de scène d'un épisode. C'est la seconde façon de tenir la
règle « pas de texte sur une image sans traitement » du design system, et elle a
l'avantage de ne pas assombrir l'illustration.

Le sommaire **disparaît dès 992px** : il ne pourrait pas s'afficher sans couvrir
l'essentiel de l'image à cette taille.

### Parties de la règle

Empilées dans la carte de page, chacune composée de :

- un titre en `heading-cardtitle` — le numéro suivi du libellé — puis son divider ;
- une zone de texte en `card-description`, **le cran normal**, ni `compact` ni
  `aerated` ;
- un **encart `aside`** en `float: right`, qui affiche des informations
  complémentaires : le texte reprend sa pleine largeur sous lui une fois qu'il
  l'a dépassé ;
- l'ornement décoratif, avant la partie suivante.

**Sur mobile**, les encarts quittent le flottement et s'empilent **sous** le
texte de leur partie.

### Fonctions MJ

Un bouton d'édition apparaît à droite du titre de **chaque partie**, et un autre
à droite du titre de page — celui-là vient du gabarit Element.

### Carte « Autres Règles »

Grille de vignettes vers les autres règles du même type — de Bases ou Annexes —
avec les arrondis en diagonale habituels. Une règle qui pointe vers un PDF porte
en plus une petite icône PDF sur sa vignette.

---

## Empire › Lore — `/empire/lore/{id}`

**Identique à la page Règle** — même gabarit Element, même image de tête avec son
sommaire flouté, mêmes parties empilées avec leur encart flottant, même carte de
fin. Se reporter à la section Règle pour le détail.

Les deux pages partagent leurs deux briques : `parts/sommaire.html.twig` et
`parts/rule-lore-part.html.twig`, plus leur CSS dans
`components/rule-lore-part.css`. Les templates restent séparés, pour qu'elles
puissent diverger plus tard sans se gêner.

**Les trois différences :**

- **trois parties** au lieu de cinq — c'est le paramètre `parts` du sommaire et la
  borne de la boucle ;
- le placeholder d'image est `NA_LORE`, et la carte de fin s'intitule
  **« Autres Lores »** ;
- le bouton d'édition MJ pointe sur l'entité `lore`.

---

## Règles › Bibliothèque — `/regles/library/{id}`

**Même base que la page Règle** : gabarit Element — titre, divider, image de tête
— puis une longue `card card-page`, et une carte « Autres Bibliothèques » empilée
à la fin. Deux différences de structure :

- **une seule section** texte + encart flottant, pas plusieurs parties comme
  Règle et Lore. Elle partage leur mise en forme — `components/rule-lore-part.css`,
  et les mêmes classes `part-body`, `aside` et `card-description` — mais **pas leur
  partial** : ses champs s'appellent `description` et `aside`, là où
  `parts/rule-lore-part.html.twig` lit des `partN`. Le markup est donc écrit dans
  la page, en cinq lignes ;
- **une zone de navigation par onglets** s'insère entre le divider du titre et
  l'image de tête.

### Navigation par onglets

L'onglet par défaut est une **icône maison** : il affiche l'image de tête, le
texte d'introduction et son encart. Les autres onglets mènent à une liste, rendue
par `regles/list-<entité>.html.twig` — un fichier par type de contenu, dont la
mise en page varie.

Une bibliothèque décrit sa navigation en base par quatre champs : `tab_field`
(l'attribut qui découpe les onglets principaux), `sub_tab_field` (les sous-onglets),
`filter_field` (un filtre supplémentaire) et `mixable` (autorise un onglet
« tout »).

### Sommaire posé sur l'image (accueil, ≥ 992px)

Comme Règle et Lore, l'accueil pose un **sommaire** en cartouche flouté sur
l'image de tête. Ici il ne liste pas des parties — il n'y en a qu'une — mais les
**onglets principaux**, dans l'ordre de la barre. Ses liens sont ceux de la barre
d'onglets, pas des ancres. Pas d'entrée « Accueil » : on y est déjà, et depuis un
onglet c'est l'icône maison de la barre qui y ramène.

**Les onglets qui nomment des choses dénombrables sont au pluriel**, dans le
sommaire comme dans la barre : Avantages / Désavantages, Compétences et
l'Armurerie. Pas le Grimoire, dont les onglets qualifient — Magie, Maho, Kiho.
Seule l'Armurerie pluralise aussi ses sous-onglets, pour la même raison.

Le `s` par défaut suffit partout sauf pour six valeurs, listées en table :
« Couteaux », et « Bugei », « Divers », « Hast », « Marchand » et « Ninjutsu »
qui restent invariables.

Les onglets ne sont calculés qu'une fois — libellé, lien et état actif — puis
rendus deux fois : par la barre et par le sommaire, qui reçoit la liste telle
quelle.

Le sommaire remplaçant la barre, **celle-ci disparaît sur l'accueil** — mais
seulement au-dessus de 992px, où le sommaire existe : en dessous la barre reste
seule à naviguer, comme le `h1` reprend sa place quand le titre sur l'image
s'effface. Depuis un onglet, l'icône maison de la barre ramène à l'accueil,
image et sommaire compris.

`parts/sommaire.html.twig` sert les deux usages : il prend soit `items`, une
liste de `{label, path}` comme `parts/breadcrumbs.html.twig`, soit `un_element`
et `parts` pour balayer les parties d'une Règle ou d'un Lore. Il ne s'affiche
qu'à partir de deux entrées.

### Les quatre bibliothèques

**Compétences** — la plus simple. Un onglet par catégorie (Bugei, Noble,
Dégradante…). Chaque ligne est un accordéon dépliable qui révèle le détail ; à
droite de son déclencheur, une étiquette et le trait associé.

**Avantages / Désavantages** — l'onglet principal sépare les deux genres. On
arrive sur la liste complète, filtrable par un **sous-onglet** de type (mental,
physique…), « tout » par défaut. À droite du déclencheur : une étiquette et le
coût ou le gain d'XP, selon le genre.

**Grimoire** (sorts) — onglet principal entre magie normale, Maho, Kiho et
Tatouage ; **sous-onglet** par anneau (Eau, Feu, Terre, Air, Vide, Universel),
Eau par défaut. À droite du déclencheur : des icônes de mots-clés et le niveau.
Sous les sous-onglets, un filtre par mot-clé ou par niveau.

**Armurerie** (objets) — onglet principal entre Arme, Armure et Projectile, puis
un **sous-onglet** par type — familles d'armes, ninjutsu ou flèches pour les
projectiles. Un court encart de rappel précède la liste. Ici les accordéons sont
**toujours ouverts**, ce qui donne un rendu de tableau : une ligne par objet avec
son image et sa description, et des colonnes qui changent selon l'onglet — taille,
poids, VD, dégâts moyens et prix pour les armes ; ND, réduction et prix pour les
armures.
