# Specs de vue

Ce que chaque vue doit être, écrit au moment de sa revue. Sert de garde-fou plus
tard : une modification qui casse un de ces points est une régression, même si
elle paraît anodine ailleurs.

Ce qui vaut pour toutes les pages d'un même gabarit est décrit une fois dans la
section du gabarit ; les sections de page ne gardent que ce qui leur est propre.

Cf. [sitemap.md](sitemap.md) pour la liste des pages et l'avancement de la revue.

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
Trois briques les servent : `nav-admin.html.twig`, `btn-mj-tab-link.html.twig`
et `card-element-resume.html.twig`.

Sous le titre, une **navigation** (`nav-admin.html.twig`) liste toutes les entités ; chaque lien mène à
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
`list-element.html.twig` s'occupe du rendu. Sept formats rendent une valeur —
`string`, `number`, `symbol`, `image`, `bool`, `boolInt`, `color` — et trois
rendent une constante en clair : `access` pour le palier de publication, `date`,
et `status` pour l'état d'un personnage. S'y ajoutent des jetons `{genre}` ou
`{anneau}` résolus depuis l'élément pour choisir le bon placeholder. Le tableau
complet est dans [design.md](design.md).

Ce qui est commun à toutes les listes : **les deux dernières colonnes sont
toujours l'icône d'édition et l'icône de suppression**, et quand l'entité a des
images, elles occupent les colonnes de gauche et sont cliquables vers le
formulaire d'édition.

### Formulaires

Un par entité, de présentation libre — l'entité dicte ses champs, de 22 lignes
pour un Avantage à 215 pour une Scène. Ce qu'ils ont tous : la navigation du
panneau au-dessus, un `div.col-12` qui les enveloppe, et
`btns/btn-form.html.twig` en pied, dont le libellé passe à « Créer » ou
« Modifier » selon la page.

Tout champ d'image porte sous lui `parts/image-delete-button.html.twig`, qui
n'apparaît que si une image est en place : il ne supprime rien tout de suite, il
bascule un `remove_<champ>` que le contrôleur lit après le `handleRequest`, ce qui
laisse annuler avant d'enregistrer. L'avatar du formulaire Utilisateur suit cette
même mécanique.

Les treize entités publiables portent en plus, juste au-dessus des boutons, le
bloc `parts/form-publication.html.twig`, en trois colonnes : le palier d'accès,
la date de mise à disposition **suivie d'une case « Effacer la date »**, et la
liste à cocher des PJ pour qui l'élément est débloqué. Les champs viennent de
`PublishableFields`, une seule déclaration pour les treize formulaires.

**Une date saisie garde son heure tant que son jour ne change pas.** Le champ
est un `DateType` sans heure ; si le MJ réenregistre sans toucher au jour, la
date d'origine — heure comprise — est reposée telle quelle, et ce n'est qu'en
changeant de jour qu'elle prend l'heure courante. Sans ça, chaque
enregistrement remettrait l'élément à minuit et rebattrait le fil de l'accueil.

**Les cases de débloquage ne montrent et n'écrivent que ce que le MJ a
accordé.** Un débloquage obtenu en jeu porte `by_meeting` et reste invisible
ici : décocher une case ne peut donc pas effacer une rencontre.

### Divergence connue

La liste des **Comptes Utilisateurs** est encore un tableau écrit à la main, hors
de `list-element.html.twig`. Ses boutons de création et d'édition sont passés au
squelette partagé ; son tableau reste à migrer, ce qui demande un format pour la
colonne des rôles — c'est un tableau, pas une valeur.

Son formulaire porte tout le compte : avatar, pseudo, e-mail, rôles, mot de
passe, case « compte vérifié » et **personnage principal**. Ce dernier est le seul
champ du back-office dont les choix dépendent de l'élément édité — les personnages
de ce joueur et eux seuls — il est donc posé en `PRE_SET_DATA`, l'utilisateur
n'étant connu qu'à ce moment. À la création la liste est vide, ce qui est correct :
un compte qui n'existe pas encore n'a pas de personnage.

Éditer **son propre compte** depuis cette page déconnecte : le formulaire pose
`'******'` dans le mot de passe de l'entité pour ne pas l'afficher, la session
resérialise ce jeton et la requête suivante ne le retrouve plus en base. Le
symptôme est antérieur au personnage principal ; il n'a pas été traité ici.

## Aventure › Accueil — `/aventure` et `/aventure/{id}`

`aventure/index.html.twig`, qui délègue chaque bandeau à
`aventure/chapter-detail.html.twig`. CSS : `pages/campaign.css`,
`components/chapter.css` et `components/episode-card.css`.

`/aventure` redirige vers la saison courante : les deux URL sont la même page.
L'accueil du site, lui, est la rubrique Accueil — cf. sa section en fin de fichier.

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
joué. Même règle dans `parts/history.html.twig`, qui écrit « Saison en cours »
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

**Trois d'entre elles sont des grilles de vignettes** (`parts/picture-grid` en
`variant: 'rule'`), toutes **en ratio 4:3** : c'est ce format qui signale une
règle, quelle que soit la taille de la vignette.

- **Règles de Base** — quatre par ligne, donc de grandes vignettes. La taille
  tranche volontairement avec les archives et les lieux de la rubrique Empire,
  qui sont en six par ligne.
- **Les Bibliothèques** — également quatre par ligne, mêmes grandes vignettes.
  Ce sont des regroupements : sorts, compétences, avantages, objets.
- **Règles Annexes** — six par ligne, donc des vignettes plus petites, mais le
  ratio 4:3 reste. Elle ne liste que des **Règles** (`Rule::findAnnexes()`), pas
  de bibliothèques : les bibliothèques annexes existent en base mais la rubrique
  ne les affiche nulle part aujourd'hui.

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

Les trois passent par `parts/picture-grid.html.twig` — la grille de vignettes
seule, sans carte, le gabarit Category posant déjà le titre et son divider.
**Six par ligne, deux sous 576px.**

Elles héritent donc de tout ce que porte `parts/picture-grid.html.twig` : le nom
sous la vignette, les **marques de publication** — icône d'état et badge
« Nouveau », cf. design.md pour les huit états et les coins réservés —, et le
**badge PDF** en haut à droite pour les Lore qui n'ont que leur document.

Les Lieux montrent leur illustration et non leur icône. **Au survol,
l'illustration s'assombrit et l'icône du lieu apparaît au centre** — le bâtiment
tel qu'il figure sur la carte. Il n'y a rien à passer pour ça : `picture-grid`
affiche toujours l'illustration et révèle l'icône au survol dès que l'élément en
porte une, donc toute entité qui a les deux en profite.

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

`personnages/character-card.html.twig`. Une carte verticale remplie par
l'illustration du personnage, avec **l'emblème de son clan accroché en haut à
gauche, légèrement débordant**, un cartouche pour son nom et prénom, et en
dernière ligne le pseudo de son joueur. Un dégradé noir vers le bas rend le tout
lisible. Au survol, la carte zoome.

Trois états se posent par-dessus, dans cet ordre de priorité :

- **non lisible** — carte grisée et translucide, cadenas battant, non
  cliquable ;
- **mort** (`Status::DEAD`) — cartouche en rouge, icône de mort, illustration
  teintée, carte translucide qui redevient opaque au survol ;
- **disparu** (`Status::MISSING`) — même traitement, en bleu Air, avec l'icône
  du point d'interrogation.

Mort et disparu sont deux valeurs d'un même champ ordonné, `Personnage.status` —
vivant, disparu, mort — et non deux booléens : un personnage ne peut pas être les
deux. Les deux posent aussi `perso-inactif` sur la colonne.

La carte porte enfin les marques de publication de la vignette, badge
« Nouveau » compris (cf. design.md), et **un personnage non listé ne rend pas de
carte du tout**.

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
moitié-moitié.

À gauche les **informations personnelles** — pseudo, e-mail, mot de passe, statut
de vérification, rôles, avatar — chacune avec son lien de modification quand elle
en a un. **L'avatar y est toujours celui du compte**, jamais le portrait du
personnage principal : c'est de cette carte qu'on le téléverse.

À droite le **personnage principal** — son portrait cliquable vers son profil, le
mon de son clan, son nom et le lien vers sa fiche privée — puis la liste
déroulante qui le change et la case « sans personnage », puis **Mes Autres
Personnages**, soit les personnages du compte moins le principal. Cette grille lui est propre : c'est le seul endroit du
site où un personnage est montré avec sa fiche privée.

Le choix part en POST sur `/mon_compte/main-character`, avec jeton CSRF.
**L'identifiant reçu se cherche dans les personnages du joueur**, jamais dans le
dépôt : un id qui ne lui appartient pas ne peut donc pas être retenu, sans avoir
à écrire de contrôle d'accès.

**Une case « sans personnage » part dans le même POST.** Cochée, elle pose
`Utilisateur.withoutCharacter` et fait naviguer le joueur comme un visiteur : ni
personnage principal ni reprise automatique du dernier créé, donc plus aucun
débloquage personnel et aucune section de secrets sur l'accueil. C'est le seul
moyen, pour un joueur ou pour le MJ, de voir le site tel qu'un anonyme le voit.

**Le personnage retenu est le seul qui compte pour la visibilité.**
`CurrentPlayer::characters()` rend le personnage principal, ou à défaut le
dernier créé du compte, et rien d'autre : un joueur à plusieurs personnages ne
voit donc pas l'union de leurs débloquages, mais ceux de celui qu'il a choisi.
Changer de personnage principal change ce qu'il voit du site.

**Deux routes, une page** : `/reset_pass/{token}` rend le même template que
`/mon_compte/password/edit`.

**La modale de connexion vient de `base.html.twig`**, jamais d'une page — deux
pages l'incluaient en plus, ce qui la dupliquait avec son `id`.

### L'avatar et son repli

`parts/avatar.html.twig` rend le seul carré d'avatar du site, en trois cas et
dans cet ordre :

1. avec `character: true`, le **portrait du personnage principal** s'il y en a un ;
2. sinon l'**avatar téléversé** du compte ;
3. sinon l'**initiale du pseudo**, en capitale blanche sur un fond secondary
   assombri. Plus aucun portrait aléatoire servi par `pravatar.cc`.

La boîte est carrée et fixe, quelle que soit l'image : `size` arrive en pixels et
se pose en style inline sur `--avatar-size`, que `.avatar` (`icons.css`) lit pour
sa largeur, sa hauteur et la taille de l'initiale. 96px dans la barre du haut,
128px sur Mon compte et dans le formulaire du back-office, 64px dans la liste des
utilisateurs.

### Zone utilisateur de la barre du haut

Visible une fois connecté, à droite de la navigation : pseudo en gras, le
personnage principal, le lien Mon compte, puis Se déconnecter en plus petit.
L'avatar est à droite de cette pile, et **il montre le portrait du personnage
principal dès qu'il y en a un** — l'avatar du compte n'apparaît donc que faute de
personnage principal.

La ligne du personnage principal est le mon de son clan en 24px suivi de son nom
entre parenthèses, le tout cliquable vers son profil et souligné au survol. Elle
disparaît quand le compte n'a pas de personnage principal.

Sous 992px l'avatar disparaît et la pile s'aligne à droite.

## Variantes d'état

**Élément verrouillé** — `element-locked.html.twig`, rendu à la place du
contenu quand un élément est listé sans être lisible pour ce visiteur — cf. la
règle de lecture dans « Accueil ».
Il reprend le gabarit Element : une carte de page, un placeholder grisé surmonté
d'un cadenas qui bat, puis un texte d'attente propre à l'entité — un lieu, une
archive et un personnage n'attendent pas la même chose. Ces textes, comme les
libellés et les placeholders, sont encore trois tables écrites dans le gabarit ;
`EntityRegistry` porte déjà la clé, la route et le libellé de chaque entité, la
reprise reste à faire.

**Élément introuvable** — `element-hidden.html.twig`, rendu quand l'élément
n'est même pas listé pour ce visiteur. C'est une **page**, sur le gabarit Other,
servie avec un **statut HTTP 404** : le visiteur voit un message d'attente et un
retour à l'accueil plutôt que la page d'erreur du site, et un robot voit bien un
404. Le message ne dit jamais si l'élément existe — « Cette page n'existe pas,
ou n'est pas accessible depuis votre compte » couvre les deux cas sans les
distinguer.

Les deux sont posées par `VisibilityTrait::accessGuard()`, dans le contrôleur.

**Aucune saison** — `aventure/aucune-saison.html.twig`, sur le gabarit Other, quand
la base n'a encore aucune saison. Un texte d'attente et, pour le MJ, le bouton de
création de la première saison.

Les deux textes d'attente partagent la classe `texte-attente` : centrée et en
italique, contrairement au texte justifié d'une carte ordinaire.

## Gabarit Category

`category.html.twig` et les cinq rubriques qui l'étendent — Accueil, Aventure
(par sa page de saison), Personnages, Empire, Règles. CSS dans
`components/category.css`.

**Bandeau de tête** — une image de fond posée par `header-bg-{{ header_classname }}`,
et par-dessus deux lignes : `header_up` en petit, un `hr` à mi-largeur, puis
`header_down` en `heading-category`, passé par le filtre `relief`. Le titre
**passe en `reduced` dès qu'un de ses mots dépasse neuf caractères** — le
gabarit le mesure lui-même, il n'y a pas de classe à poser depuis la page.

**Sections** — le contrôleur passe une liste `sections`, et le gabarit boucle
dessus en rendant, pour la Nième, le bloc `section_content<N>` que la page
définit. Chaque section porte son `id` — la clé d'entité, donc les ancres du
site (`/empire#lieu`) —, son titre en `heading-categorysubsection` découpé en
`titleLight` + `titleStrong`, et son divider **masqué sous 992px** : sur mobile,
les cartes et images empilées séparent déjà les sections.

À droite du titre, une `.section-aside` reçoit deux choses : le bloc
`section_aside<N>` si la page en définit un, et le bouton « Ajouter X » du MJ
**seulement si la section déclare un `label_one`**.

**`content_top`** — un bloc optionnel rendu avant la première section, pour ce
qui doit mordre sur le bandeau. Seule la rubrique Personnages s'en sert, pour
son cartouche de filtres.

Une page de rubrique n'écrit donc que ses blocs de contenu et sa feuille de
style ; tout le reste vient du contrôleur, en données.

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

**Grille de vignettes** — `parts/picture-grid.html.twig` en `variant: 'landscape'`
(le défaut) : images aux **coins arrondis en diagonale** (haut-gauche et
bas-droite) avec le titre dessous. **6 colonnes** au-delà de 992px, **3** entre
768 et 992, **2** en dessous.

**Élément non lisible** — le contrôleur rend `element-locked.html.twig` à
la place du contenu : le même gabarit, avec un placeholder grisé, un cadenas qui
pulse et un texte d'attente. Le MJ, lui, voit toujours la page.

**Vignettes** — dans les grilles, un élément non lisible apparaît grisé et
translucide, non cliquable, avec un cadenas qui pulse et une infobulle « À
débloquer » ; son titre reste affiché, estompé. Les autres portent la marque de
leur palier. Cf. design.md, « les cinq variantes de la grille ».

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

Un bloc dédié, `empire/clan-banner.html.twig`, dont le socle est partagé avec la
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

`regles/class-banner.html.twig`. Une image de fond assombrie par un dégradé, le
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

`personnages/character-profil.html.twig`, `pages/profil.css`, plus
`components/history.css` et `components/edit-mode.css` partagés avec la Fiche.

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
2. **Histoire** (deux tiers) — tout ce qui s'écrit sur le personnage tient dans
   cette seule carte, découpée en sous-sections ; cf. le détail plus bas. Pas de
   titre de carte classique : un `h2.heading-elementpart.decorated` « Histoire »
   ouvre la carte, avec son ornement au-dessus comme les sous-sections.
3. **Historique** (deux tiers) — `parts/history.html.twig`, **du plus récent au
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

### La carte Histoire, et qui lit quoi

Une seule carte plutôt que trois colonnes : chaque zone est une sous-section,
au motif de la page Lieu — `parts/divider-ornament.html.twig` puis un
`h2.heading-elementpart.decorated`, l'ornement jouant le rôle du divider.

Dans l'ordre, et chacune n'apparaissant que pour qui y a droit :

- le **texte public** en tête de carte, les titres du personnage en cartouche
  au-dessus. C'est le champ `description` de `Personnage` ;
- **Développement**, l'arc narratif écrit par le MJ, **public** : une entrée par
  ligne, le repère de la scène en lien vers l'épisode, le marqueur `MORT` quand
  la participation le porte, puis le texte. Même tri que l'Historique, du plus
  récent au plus ancien, et **l'image de la scène à droite de chaque ligne**,
  cliquable vers elle comme le repère. La section est **toujours affichée**,
  « Rien pour le moment. » quand elle est vide, et le MJ y trouve son bouton
  d'ajout, centré comme les autres actions de la carte ;
- **Histoire secrète** (`playerNotes`), que le joueur adresse au MJ — le joueur
  du personnage et le MJ seuls ;
- **Notes du Maître de Jeu** (`gmNotes`) — le MJ seul, en lecture comme en
  écriture.

Sous chaque titre secret, une ligne en `text-muted text-small` dit qui lit la
zone : c'est elle qui porte l'information, plutôt qu'un intitulé à rallonge.

Le joueur du personnage **et** le MJ écrivent dans le texte public et dans
l'histoire secrète ; le MJ seul écrit dans les siennes. Le contrôle est dans
`PersonnagesController`, jamais dans le gabarit : `estLeJoueur()` compare le
joueur du personnage à l'utilisateur connecté, et `JoueurPersonnageType` n'ajoute
`gmNotes` que sur l'option `is_gm`. Rien ne passe par une validation : ce que le
joueur écrit dans son texte public est en ligne aussitôt.

### Les balises, et le HTML que le joueur ne peut pas écrire

Le texte public et les développements passent par le service `Baliseur` :
`[Prénom]` et `{Lieu}` deviennent des liens vers le personnage ou le lieu, posés
à l'enregistrement, et rendus au champ à l'ouverture — c'est ce que faisaient
déjà Lieu, Scène et le back-office Personnage. Les neuf paires
`baliserPersonnages` + `baliserLieux` de ces contrôleurs sont regroupées dans
`Baliseur::baliser()` et `::debaliser()`, qui laissent un texte vide intact.

Sur le profil, la règle est dissymétrique :

- **à l'ouverture, tout le monde reçoit le texte débalisé.** Le champ ne contient
  jamais de HTML, pour personne ;
- **à l'enregistrement, seul le MJ repose les liens.** Ce que le joueur écrit
  reste littéral : ses `[Prénom]` sont stockés tels quels et s'affichent tels
  quels, jusqu'à ce que le MJ repasse dessus.

C'est un choix de sécurité, pas de confort. Le profil est rendu en `raw` : servir
au joueur le HTML des liens, c'est lui donner un champ où retoucher du HTML qui
sera réinjecté dans la page. Le prix est qu'un profil balisé par le MJ perd ses
liens au premier enregistrement du joueur — accepté.

Le débalisage ne suffit pas à lui seul, puisque rien n'empêche d'écrire du HTML
à la main. **Ce que le joueur enregistre passe donc par `strip_tags()`**, sur le
texte public comme sur l'histoire secrète. Les deux zones de notes, elles, sont
rendues **sans `raw`** : `|nl2br` échappe et garde les retours à la ligne, ce qui
suffit à une note et ne laisse aucune surface. Seul le texte public garde `raw`,
parce que lui seul reçoit des liens du baliseur.

`strip_tags` retire les balises mais garde leur contenu — un `<script>alert(1)</script>`
devient le texte `alert(1)`, inoffensif. Contrepartie connue : un `<` isolé, du
genre « j'ai <3 ce perso », emporte la fin de la phrase.

L'édition se fait **en place**, comme sur la fiche de personnage, mais **zone
par zone** : chaque zone éditable est un `.profil-zone` qui porte son propre
bouton, et c'est sur ce bloc que le JS pose `edit-mode` — pas sur la page. Les
`.read-only` et les `.edit-only` s'échangent donc dans cette zone seule, les
autres restent en lecture. La bascule vit dans `components/edit-mode.css`,
partagée avec la fiche.

Les trois boutons — Éditer, Annuler, Valider — viennent de
`btns/btn-interactive-edit-actions.html.twig`, qui ne prend que le libellé du premier. Ils sont
en `btn-quaternary-style`, le bouton discret du projet, et non en bouton
principal : ce sont des actions d'édition, pas des appels à l'action.

Les trois zones étant dans la même carte, **un seul `<form>` les enveloppe**.
Valider soumet donc tout, y compris les zones restées en lecture — sans effet,
puisque leurs champs portent déjà leur valeur courante.

### Le développement d'un personnage

`Development` porte un texte et **une participation**, rien d'autre : le
personnage et la scène s'en déduisent, et la mort se lit sur
`participation.estMort` plutôt que sur un type propre à l'entrée — un seul
endroit dit qu'un personnage est mort dans une scène. Conséquence assumée : un
développement ne s'écrit que sur une scène jouée.

Le texte passe par le `Baliseur` comme celui du profil, sans condition : le
développement n'est écrit que par le MJ.

La saisie est au back-office (`/admin/development`), en **deux listes liées** :
le personnage d'abord, la scène ensuite, filtrée sur ses seules participations.
Le select Personnage n'est **pas mappé** — le personnage réel vient toujours de
la participation, ce champ ne fait que réduire le second — et il ne propose que
les personnages ayant au moins une participation. Arrivé depuis le bouton du
profil, qui passe `?personnage={id}`, il est déjà rempli ; en édition, il l'est
depuis le développement.

Le filtrage est **côté client** : chaque option de scène porte un
`data-personnage`, et un script du `_form` reconstruit le select à chaque
changement. Pas de requête, pas de form event — mais tout le catalogue des
participations part dans la page, ce qui la fait grossir avec la campagne (cf.
plus bas).

Supprimer une participation emporte ses développements, par `orphanRemoval` côté
Doctrine et `ON DELETE CASCADE` côté base.

### Mobile

L'ordre change, et c'est le seul endroit du site où il change : **l'illustration
passe en premier**, en bandeau de 125px collé au haut de la page, avec un dégradé
noir vers le bas pour que **le nom du personnage s'y pose en blanc**. Le `h1` du
gabarit quitte le flux et vient s'aligner en bas de ce bandeau.

Suivent, empilées pleine largeur : l'**histoire**, puis les
**informations**, l'**historique**, et les **autres personnages** — toujours deux
portraits par ligne.

L'illustration est donc écrite deux fois dans le gabarit, une fois en bandeau et
une fois dans la carte : les deux ne vivent pas dans le même parent, aucune règle
CSS ne peut faire passer un élément de l'un à l'autre. C'est l'inverse des
descriptions de famille sur la page Clan, où les deux places étaient dans la même
grille et une seule occurrence suffisait.

### Personnage non lisible

Un personnage que le visiteur n'a pas le droit de lire rend
`element-locked.html.twig` à la place de tout le contenu, comme les autres
entités du gabarit Element.

## Personnages › Fiche — `/personnages/fiche/{id}`

`personnages/character-sheet.html.twig`, `pages/character-sheet.css`, plus
`components/spell.css` et `components/edit-mode.css`.

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
2. À gauche, les **avantages et désavantages**, le **tableau des compétences**,
   puis le bloc **Magie** quand il y a lieu ; à droite, les **statistiques de
   combat**.

Les blocs internes de la carte partagent une même classe, `card-bloc` : padding
zéro, coins à 0.5rem, contenu découpé — leur tableau ou leur cartouche touche
leurs bords. Chacun s'ouvre sur un `card-title-cartouche` qui lui sert de titre.

### Le bloc Magie et les sorts connus

`FichePersonnage.knownSpells` est une table de liaison vers `Sort` : ce que le
personnage sait lancer. Le bloc **n'apparaît que si** le personnage est de
classe Shugenja **ou** porte déjà au moins un sort — un bushi à qui le MJ a
donné un sort le voit donc, et un shugenja au grimoire vide voit le bloc vide
plutôt que rien.

**En-tête, quatre colonnes** : l'affinité et la déficience de l'école, la
technique spéciale de l'école, et les **sorts universels**, qui ne se rattachent
à aucun anneau.

**Sous l'en-tête, une colonne par anneau** — Terre, Air, Feu, Eau — chacune sous
son en-tête coloré et son icône d'élément, avec la liste des sorts connus de cet
anneau. **L'anneau du Vide n'apparaît que pour un Ishiken-Do**, lu sur les deux
champs d'avantage de la fiche : lui seul lance des sorts de Vide.

Le regroupement par anneau se fait dans le gabarit, pas au contrôleur : la
collection arrive à plat et un `merge` la répartit.

**Chaque sort est une ligne** (`personnages/character-sheet-spell-line.html.twig`) :
son nom à la couleur de son anneau, et son niveau en pastille de la même
couleur. La mécanique du sort — description, portée, zone, durée, augmentations
— **n'est pas dans la page mais dans un popover Bootstrap**, ouvert au clic et
refermé à la perte de focus, sur mobile comme sur desktop. Le même bloc de
détail sert au Grimoire de la rubrique Règles : c'est un partial à part, inclus
des deux côtés.

**Aucun sort ne coûte d'XP.** Le personnage en connaît six au départ et en
gagne trois par Rang ; le bloc Magie ne pèse donc pas dans le total d'XP dépensé
de la fiche, et il n'y a rien à en déduire.

En édition, chaque colonne porte son ajout et chaque ligne sa croix de retrait,
en `edit-only` — la même bascule `edit-mode` que le reste de la fiche.

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
titre du chapitre en Shogun, citation. **Le markup n'est pas partagé** — les deux
dégradés couvrent des hauteurs sans rapport, cf. design.md — mais tout le reste
l'est : `components/chapter.css` est chargé par les deux pages, et
`aventure/chapter-characters.html.twig` rend les grilles de portraits pour l'une
comme pour l'autre.

Il contient aussi :

- **la ligne de vignettes d'épisodes** (`aventure/episodes-line.html.twig`), qui
  permet de basculer d'un épisode à l'autre dans le chapitre ; l'épisode courant
  est mis en avant ;
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

**Ce classement compte l'XP brut, sans le bonus de rattrapage ×2.** Il mesure la
performance de la séance, et le double XP est un coup de pouce accordé aux
personnages en retard sur la campagne : l'y faire peser récompenserait le retard
plutôt que le jeu. Le bonus est en revanche **affiché à côté du gain**, pour que
le joueur voie le surplus qui lui revient. C'est le classement général de
l'accueil qui l'intègre, parce qu'il mesure la puissance atteinte et non une
séance — cf. « Les deux classements ne comptent pas la même chose » dans la
section Accueil.
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

`regles/dojo-detail.html.twig` et `pages/dojo.css` — la route et l'entité disent
encore « école », le gabarit et sa feuille disent déjà « dojo ». Gabarit Element,
avec image de tête. Une carte d'intro, puis Techniques et
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

Sur l'image de tête, un sommaire posé **en bas à gauche, à 1rem des deux
bords**. Il liste les parties de la règle en liens d'ancre : un clic amène à la
section correspondante, plus bas dans la carte. Une règle peut avoir plusieurs
parties.

**Sa lisibilité vient d'un cartouche flouté, pas d'un dégradé** — classe
partagée `.blur-cartouche` (`effects.css`) : coins arrondis, bordure claire très
transparente, fond légèrement teinté et `backdrop-filter: blur(16px)`. Le même
effet habille le titre de scène d'un épisode. C'est la seconde façon de tenir la
règle « pas de texte sur une image sans traitement » du design system, et elle a
l'avantage de ne pas assombrir l'illustration.

Le sommaire **n'existe qu'au-dessus de 992px** (`d-none d-lg-flex`) : en dessous
il couvrirait l'essentiel de l'image.

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

### La source PDF

Une règle peut porter un PDF. **Il ne remplace la page que si aucune partie n'est
rédigée** : la vignette et l'image de tête mènent alors droit au document, comme
avant. Dès qu'une partie porte du texte, la page reprend la main — c'est le
résumé qu'on veut faire lire, pas le document source, souvent long.

Le PDF reste joignable par un bouton posé **en bas à droite de l'image de tête**,
« Voir la source PDF », précédé de l'icône PDF. Il est délibérément discret :
fond rouge à demi transparent et `backdrop-filter`, la même façon de tenir la
lisibilité sur une image que le cartouche du sommaire, sans l'insistance d'un
bouton primaire. **Sous 992px il passe en haut à droite**, le titre de page
occupant le bas de l'image.

Le test « la page a-t-elle de quoi se lire » est `hasParts()`, porté par
`PartsTrait` que partagent Lore et Rule. `parts/picture-grid.html.twig` s'en sert
pour décider où pointe la vignette, `parts/card-top-image.html.twig` pour choisir
entre l'image cliquable et le bouton.

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

Les deux pages partagent leurs deux briques : `rule-lore-library/summary.html.twig` et
`rule-lore-library/part-row.html.twig`, plus leur CSS dans
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

`regles/library-detail.html.twig`, `pages/library.css`, plus
`components/rule-lore-part.css` et `components/spell.css`.

**Même base que la page Règle** : gabarit Element — titre, divider, image de tête
— puis une longue `card card-page`, et une carte « Autres Bibliothèques » empilée
à la fin. Deux différences de structure :

- **une seule section** texte + encart flottant, pas plusieurs parties comme
  Règle et Lore. Elle partage leur mise en forme — `components/rule-lore-part.css`,
  et les mêmes classes `part-body`, `aside` et `card-description` — mais **pas leur
  partial** : ses champs s'appellent `description` et `aside`, là où
  `rule-lore-library/part-row.html.twig` lit des `partN`. Le markup est donc écrit dans
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

`rule-lore-library/summary.html.twig` sert les deux usages : il prend soit `items`, une
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

---


## Accueil — `/`

Rubrique d'accueil, route `news`, sur le **gabarit Category**. Elle prend `/`, et
**Aventure a reculé sur `/aventure`** en gardant sa redirection vers la saison
courante. La navigation porte `ACCUEIL` en tête de `nav_sections`, et le logo y
mène.

Elle répond à deux besoins qui n'en font qu'un : côté joueur, retrouver d'un
coup d'œil ce qui est apparu depuis la dernière séance ; côté MJ, préparer du
matériel à l'avance et l'ouvrir au goutte-à-goutte. Le corollaire est que
**deux personnages n'ont pas le même site sous les yeux** — de quoi donner à
chacun de la matière à mettre en commun.

**Cinq sections**, donc cinq blocs `section_content` du gabarit Category : la
dernière séance, puis quatre rubriques de nouveautés. Le découpage est ce qui
évite la page-catalogue.

### Le palier d'accès

Ce que le joueur peut voir tient sur **un seul axe ordonné**, `access`, un
`smallint` à quatre constantes (`src/Entity/Access.php`). Il a remplacé
`locked`. **La valeur par défaut d'une entité neuve est `AUTO`** : ce que le MJ
crée sans y penser reste invisible jusqu'à ce qu'il le classe.

| `access` | Qui voit la vignette | Qui accède au contenu | Marque |
| --- | --- | --- | --- |
| `SECRET` (0) | MJ seul | MJ, et les personnages à qui le MJ l'a révélé | œil barré, puis œil une fois révélé |
| `LOCKED` (1) | tout le monde | MJ, et les personnages à qui le MJ l'a ouvert | cadenas fermé, puis ouvert |
| `AUTO` (2) | MJ seul | MJ, et les personnages qui l'ont rencontré ou visité | aucune |
| `PUBLIC` (3) | tout le monde | tout le monde, anonymes compris | aucune |

**Deux familles, deux marques.** `SECRET` et `LOCKED` portent de la valeur et
s'ouvrent individuellement : ce sont les seuls à porter une icône, et sa forme
dit comment l'obtenir — l'œil pour ce dont les autres ignorent l'existence, le
cadenas pour ce qui est teasé au vu de tous. `AUTO` s'ouvre tout seul à
la première rencontre ou visite, et reste muet pour ne pas faire de bruit.

**Un axe ordonné plutôt que trois booléens.** `hidden`, `unlockable` et un
débloquage général autoriseraient des combinaisons sans signification —
caché *et* débloquable, débloquable *et* déjà ouvert — et obligeraient à lire
trois champs dans le bon ordre à chaque vignette. Ici l'état illégal n'existe
pas, et la comparaison suffit.

### Les deux dates

**`publishedAt`** — datetime nullable. Sur un élément `PUBLIC`, c'est **elle
seule** qui distingue le socle de départ de ce qui paraît ensuite : sans date,
l'élément est du contenu initial, visible de tous mais absent de l'accueil ;
avec une date passée, il entre au fil des nouveautés et devient éligible au
badge « nouveau » pendant quatorze jours. Une date future programme la parution
sans cron ni état supplémentaire.

**C'est le MJ qui la pose, à la main, et rien ne la déplace tout seul.** Aucun
changement de palier ne la rafraîchit : `setPublishedAt()` n'est appelé que par
le formulaire. Un PNJ qu'on ouvre à tout le monde ne repasse donc en tête du fil
que si le MJ le redate — le prix de ne pas voir la moitié du site se déclarer
neuve à chaque passe de rangement. La conséquence à connaître : **rien ne
distingue en base un élément jamais publié d'un élément qu'on a délibérément
sorti du fil**, une seule date, celle du palier courant.

**`createdAt`** — datetime non nul, posé en `prePersist`. Technique : sert au
back-office, jamais au joueur.

**`unlockedAt`** vit dans la table de débloquage, une ligne par couple
personnage × élément. C'est elle qui sert de date de découverte quand il y en a
une : `Visibility::discoveredAt()` prend le débloquage personnel s'il existe,
`publishedAt` sinon.

### La règle de lecture

Une seule, et elle remplace cinq lectures divergentes de `locked` :

```
paru     = publishedAt IS NULL  OU  publishedAt <= now
débloqué = access <= AUTO  ET  débloqué par un de mes personnages
listé    = débloqué  OU  MJ  OU  access = LOCKED  OU  (access = PUBLIC ET paru)
lisible  = débloqué  OU  MJ  OU  (access = PUBLIC ET paru)
```

**Le débloquage personnel passe avant le palier et avant la date** : il ouvre
aussi un élément `hidden`, que personne d'autre ne voit, et c'est `unlocked_at`
qui fait office de mise à disposition. C'est ce qui permet de confier une
information à un seul personnage sans la teaser aux autres.

**Ce que le personnage possède compte comme débloqué**, sans ligne en base :
`Visibility::isOwned()` ouvre l'**école** du personnage du visiteur. Un joueur ne
peut pas se retrouver devant un cadenas sur sa propre école, quel que soit le
palier auquel le MJ l'a laissée.

**« Mes personnages » n'en fait qu'un.** Le calcul porte sur ce que rend
`CurrentPlayer` : le personnage principal, ou à défaut le dernier créé du
compte. Un joueur à plusieurs personnages voit le site à travers celui-là seul,
et la case « sans personnage » de Mon compte le ramène à la vue anonyme.

**La date ne joue que sur le palier Public** : elle programme sa mise à
disposition. Aux trois autres paliers c'est le déblocage qui ouvre, et la date ne
sert plus qu'au badge « Nouveau ».

**Le cadenas se montre à tout le monde**, anonymes compris : il n'existe plus de
palier réservé aux comptes connectés, et le teasing d'un `LOCKED` est justement ce
qui donne envie d'ouvrir un compte. Ce qui n'est pas public demande en revanche un
déblocage, donc un personnage, donc un compte. Et le MJ liste tout, y compris ce
qu'il garde caché — c'est ce qu'il prépare.

**Les deux paliers invisibles se distinguent chez le MJ** : `SECRET` porte l'œil
barré, `AUTO` l'engrenage, et une fois ouverts ils donnent l'œil ouvert et le même
engrenage. Le tableau complet des huit états est dans [design.md](design.md).

Listé sans être lisible, c'est l'état **teasé** : vignette grisée et
translucide, non cliquable, cadenas battant au centre, infobulle « À
débloquer », et son icône d'état en haut à droite. **Le nom reste lisible**,
simplement estompé : c'est ce qui donne envie de l'ouvrir.

**Le calcul se fait une fois par requête**, dans le service `Visibility` : tous
les débloquages du visiteur en une requête, groupés par entité en jeux d'ids,
puis chaque test est une appartenance en mémoire. Sans ça, la règle coûterait
une requête par vignette.

Sept fonctions Twig l'exposent aux gabarits, et **elles seules** doivent servir à
décider d'un affichage : `element_state`, `element_readable`, `element_listed`,
`element_unseen`, `element_fresh`, `element_sort`, plus `element_url` et
`element_key` qui viennent d'`EntityRegistry` et d'`ElementUrl`. Un gabarit ne
lit jamais `access` ni `publishedAt` directement.

### Les deux classements ne comptent pas la même chose

L'accueil montre un classement quand le visiteur n'a pas de personnage, la page
d'épisode en montre un autre en fin de page. **Ils répondent à deux questions
différentes et se calculent différemment — ce n'est pas une divergence à
corriger.**

| | Classement général (accueil) | Classement d'épisode |
| --- | --- | --- |
| Ce qu'il mesure | la puissance atteinte | la performance de la séance |
| XP de création | comptée | ignorée |
| Bonus de rattrapage ×2 | **compté** | **ignoré** |
| Qui y figure | tous les PJ, morts et disparus compris, qu'ils aient joué ou non | les participants de l'épisode |

**Le double XP est un coup de pouce, pas une performance.** Il est accordé aux
personnages en retard sur la campagne pour qu'ils rattrapent leur écart : le
faire peser dans le classement d'une séance récompenserait le retard plutôt que
le jeu, et léserait ceux qui n'y ont pas droit. Le classement d'épisode compte
donc l'XP brut, et **affiche le bonus à côté** pour que le joueur voie le
surplus qui lui revient. Le classement général, lui, mesure ce que le personnage
vaut aujourd'hui : le bonus y est acquis, il compte.

`ClasseurXP::total()` porte le calcul général — participations effectives plus XP
de création — et sert aussi au rang et à la fiche, pour que la même règle ne
soit pas réécrite à trois endroits. `ClasseurXP::cumuler()` porte celui de
l'épisode et reste volontairement sur l'XP brut.

### Débloquage par personnage — `character_unlock`

Une seule table polymorphe, pas une par entité :

| Colonne | Rôle |
| --- | --- |
| `character_id` | FK vers `Personnage`, `ON DELETE CASCADE` |
| `entity` | clé d'entité — `lieu`, `personnage`, `lore`… |
| `element_id` | id de la ligne visée, **sans FK** |
| `unlocked_at` | datetime, future admise |
| `by_meeting` | booléen : la ligne vient d'une rencontre en jeu, pas du MJ |

Unique sur `(character_id, entity, element_id)`. Le nom de table évite `unlock`,
mot réservé MySQL. Les lignes ouvrent tout ce qui demande un débloquage — les
trois paliers sous `PUBLIC` — et n'ont aucun effet au-dessus. Elles survivent à
un changement de palier et reprennent la main si l'élément redescend.

**`by_meeting` sépare deux origines qui ne se gèrent pas pareil.** Une ligne
posée par le MJ dans le formulaire est une décision : elle ne bouge que s'il
décoche la case. Une ligne posée par une rencontre en scène est une déduction :
elle est balayée dès que l'élément quitte le palier automatique, puisqu'elle n'a
alors plus de raison d'être. Sans ce drapeau, un changement de palier effacerait
aussi ce que le MJ avait accordé à la main.

Ce que ça coûte : aucune intégrité sur `element_id`, donc une suppression laisse
des orphelins — c'est `Unlocker::forget()`, appelé dans le `delete` de chaque
entité concernée, qui les balaie. Ce que ça gagne : une requête par page, une
table, et **aucun fichier de plus quand une entité rejoint le système**. Treize
tables de liaison auraient donné l'intégrité contre treize requêtes et un
fichier par entité.

**Les clés d'entité vivent dans `EntityRegistry`** — clé → classe, route,
libellé — et servent à `Unlock.entity`, à `element_url()` et aux fils mélangés
de l'accueil. La reprise n'est pas complète : `element-locked.html.twig` garde
ses propres tables de libellés, de placeholders et de textes d'attente, et
`Library.entity` ses propres `choices`. Le registre est le point de vérité vers
lequel les ramener.

### Ce que le MJ saisit

Dans le formulaire admin de l'élément, `parts/form-publication.html.twig` : le
palier d'accès, la date de mise à disposition avec sa case d'effacement, et les
PJ pour qui l'élément est ouvert. Les listes du back-office rendent le palier et
la date par les formats `access` et `date` de `table_cols`.

**Le palier `AUTO` s'ouvre tout seul, en scène.** Quand le MJ enregistre une
scène, `Unlocker::syncScene()` donne à chaque PJ qui y participe une ligne pour
**le lieu de la scène et pour chaque personnage croisé** — PJ comme PNJ — à la
seule condition que l'élément soit en `AUTO`. L'écriture se fait à
l'enregistrement, jamais en déduction à la volée : un seul chemin de lecture.

Le pendant est `Unlocker::syncAccess()`, appelé à l'enregistrement d'un Lieu ou
d'un Personnage : il repose les lignes de rencontre si l'élément vient de passer
en `AUTO`, et les balaie s'il en sort.

**`SECRET` et `LOCKED` restent manuels** : croiser un personnage dans une scène
ne veut pas dire l'avoir rencontré, c'est au MJ de le décider, par les cases à
cocher du formulaire. C'est le palier qui départage — pas de champ « débloquage
automatique » : un lieu qui ne doit pas s'ouvrir tout seul se met à un autre
palier.

**La reprise rétroactive est une commande**, `app:unlocks:backfill`
(`src/Command/BackfillUnlocksCommand.php`), et non une migration : elle sème les
lignes des lieux et des PNJ en `AUTO` depuis les rencontres et visites déjà
jouées. Seuls ces deux types se rencontrent en scène ; ailleurs, rien ne
déclenche le palier automatique.

### Les entités concernées

**Treize entités portent `PublishableTrait`** : les huit qui portaient `locked`
— Archive, Clan, Ecole, Lieu, Lore, Objet, Personnage, Rule — plus **Episode**
(le dernier résumé publié fait la une), **Sort**, **Competence**, **Avantage**
et **Library**. Tout le contenu de bibliothèque est donc publiable au
goutte-à-goutte. Ce sont exactement les treize clés d'`EntityRegistry`.

Restent dehors Saison, Chapitre, Scene, Classe et Famille.

Une migration par entité, comme le veut la convention. **Le palier lui-même a
été remanié trois fois après coup**, et ces passes-là sont des migrations
globales, pas par entité : un jeu de paliers croisés
(`VALUABLE`/`COMMON` × `HIDDEN`/`TEASED`) d'abord, ramené à cinq valeurs
ensuite, puis à quatre en fusionnant les deux paliers publiés en un seul
`PUBLIC` — c'est de là que vient la règle actuelle, où **c'est la date, et non
le palier, qui distingue le socle initial de ce qui paraît ensuite**. La table
des débloquages a été vidée en cours de route et le MJ a reclassé à la main :
ne pas chercher de continuité dans les valeurs d'`access` d'avant le 06/09/2026.

### Section 1 — La dernière séance

Deux colonnes. **À gauche**, une `session-card` teintée à la couleur du chapitre
et sur son image de fond, qui empile :

- le **dernier épisode publié** rendu par `aventure/episode-card.html.twig`, la
  carte d'épisode de la page Aventure — elle a donc quitté `pages/campaign.css`
  pour `components/episode-card.css` ;
- à côté, le **classement de cet épisode** (`news/ranking.html.twig`) ;
- puis quatre bandes empilées : **Lieux visités** (grille `icon`),
  **Personnages rencontrés** (grille `portrait`), **Objets découverts** (grille
  `rule`), et **Séances précédentes**, les cinq épisodes d'avant en lignes
  cliquables. Chaque grille porte son `empty_message` : la bande reste et dit
  « Aucun lieu visité » plutôt que de disparaître ;
- en pied, deux liens vers la **saison** et vers le **chapitre** de l'épisode,
  ce dernier avec l'ancre du bandeau de chapitre.

Sans aucun épisode publié, la carte rend son `texte-attente`.

**À droite**, deux cartes empilées : **Mes Dernières Participations** — cinq
lignes, portrait du personnage, épisode, lieu de la scène, XP cumulé sur
l'épisode ou l'icône de mort —, présente seulement si le visiteur en a ; puis le
**Classement Général**, toujours affiché.

Les objets découverts en séance viennent de `Scene.foundObjects`, une table de
liaison `found_object` renseignée par le MJ sur la scène.

### Sections 2 à 5 — les quatre rubriques de nouveautés

Quatre rubriques, **trois groupes chacune**, décrites dans une seule table
`RUBRIQUES` du contrôleur et rendues par un unique
`news/news-subsection.html.twig` :

| Rubrique | Groupes |
| --- | --- |
| Dernières **Découvertes** | Personnages, Lieux, Objets |
| Dernières **Informations** | Archives, Lore, Factions |
| Dernières **Mécaniques** | Règles, Bibliothèques, Écoles |
| Dernières **Trouvailles** | Sorts, Compétences, Avantages |

Chaque groupe déclare sa variante de grille, son nombre de colonnes, son nombre
de lignes, et un second libellé pour sa colonne de secrets.

**Chaque rubrique se coupe en deux colonnes** : à gauche (7/12) ce qui vient de
paraître pour tout le monde, à droite (5/12) ce que **mon personnage** a
débloqué — carte à fond distinct, teintée à la couleur de son clan et sur son
illustration, pour que l'information rare se voie comme telle.

**La colonne des secrets disparaît pour un visiteur sans personnage**, et la
colonne publique reprend alors toute la largeur : ses grilles récupèrent les
colonnes libérées (`perRow + secretPerRow`) au lieu de s'étirer. C'est le seul
endroit du site où le nombre de colonnes d'une grille dépend de qui regarde.

**Ce qui alimente la colonne publique** (`NewsFeed::latest()`) : les éléments en
`PUBLIC` dont la date de publication est passée, **plus** les éléments en `AUTO`
que mon personnage a croisés, triés ensemble par date de découverte — la date de
rencontre pour les seconds, `publishedAt` pour les premiers.

**Ce qui alimente la colonne des secrets** (`NewsFeed::unlockedByEntity()`) :
les débloquages accordés par le MJ — `by_meeting = false` — sur des éléments en
`SECRET` ou `LOCKED`, triés par `unlocked_at`, douze au plus par entité avant
découpe.

**Les fils listent aussi ce qui reste fermé** : un `LOCKED` non ouvert s'y mêle
aux éléments lisibles, grisé sous son cadenas. C'est délibéré — c'est sur la
page la plus vue du site que le teasing donne envie de jouer pour ouvrir la
suite.

**Ils listent toujours les derniers, quelle que soit leur date.** La fenêtre de
fraîcheur ne pilote que le badge : une campagne en pause trois semaines garde
une page pleine plutôt qu'une page vide. Un groupe qui déborde d'une ligne passe
à deux, et la carte réserve la hauteur correspondante par `--rows`.

### Icônes et badge « nouveau »

**Une seule icône par vignette**, posée en haut à droite par
`parts/badge-access.html.twig` d'après l'état rendu par `element_state()`. Le
tableau des huit états et de leurs icônes est dans [design.md](design.md) ; ce
qu'il faut en retenir ici :

- seuls `SECRET` et `LOCKED` portent une marque avant ouverture — l'œil barré
  pour ce dont les autres ignorent l'existence, le cadenas pour ce qui est teasé
  au vu de tous ;
- une fois ouverts, ils donnent l'œil ouvert et le cadenas ouvert ;
- `AUTO` porte l'engrenage dans les deux sens, et n'est visible que du MJ tant
  qu'il n'est pas croisé ;
- `PUBLIC` ne porte rien.

Le **jaune** ne marque que le cadenas fermé : c'est le seul état qui appelle une
action, les autres constatent. Le cadenas fermé garde en plus son asset pixel
battant au centre de la vignette ; toutes les icônes de coin sont en
FontAwesome. Il n'y a **pas d'icône d'espion** : l'état seul dit ce qu'il faut.

**Nouveau** — la date de découverte a moins de **14 jours**
(`Visibility::FRAICHEUR_JOURS`) : `unlocked_at` s'il y a un débloquage
personnel, `publishedAt` sinon, la même valeur que celle qui sert au tri. Un
élément non lisible n'est jamais « nouveau ». Le badge suit l'élément **partout
sur le site**, donc il vit dans `parts/badge-new.html.twig` et se pose dans
`picture-grid`, dans `personnages/character-card.html.twig` et sur la carte
d'épisode.

Chaque coin de vignette est réservé pour que deux marques ne se recouvrent
jamais, et le badge « Nouveau » se déplace selon la variante de grille : la
répartition est dans design.md, « Les coins d'une vignette ».

### Ce que ça a changé dans l'existant

Les cinq lectures divergentes de `locked` ont toutes été ramenées sur
`Visibility` : `parts/picture-grid.html.twig`, `regles/list-objet.html.twig`,
`personnages/character-card.html.twig`, `empire/location.html.twig`, et
`LockedTrait` devenu `VisibilityTrait`. Aucun gabarit ne lit plus `access` en
direct.

`src/Form/JoueurFichePersonnageType.php` filtrait l'équipement proposé au joueur
par `o.locked = false` ; il lit maintenant le palier, la date **et** les
débloquages individuels — un objet ouvert en jeu est donc équipable. Les objets
déjà équipés restent proposés quoi qu'il arrive, pour qu'une fiche ne perde
jamais son matériel.

### Cas limites

- **Redescendre un palier** — l'élément revient au teasé, et les débloquages du
  MJ reprennent la main. Les débloquages de rencontre, eux, sont balayés : ils
  n'ont de sens qu'au palier automatique. **La date ne bouge pas**, c'est au MJ
  de la reposer s'il veut remettre l'élément en tête du fil.
- **Débloquer un élément déjà public** — sans effet visible. La ligne est
  conservée.
- **Personnage mort** — ses débloquages restent : il a lu ces pages, les lui
  retirer serait une régression.
- **Élément supprimé** — `Unlocker::forget()` balaie ses lignes dans le `delete`
  de l'entité ; il n'y a pas de FK pour le faire à sa place.
- **Base sans rien de publié** — la page rend son bandeau, son
  `texte-attente` sur la carte de séance, et « Rien de nouveau » dans chaque
  grille.
- **MJ** — il voit tout, à tous les paliers, et le badge lui reste utile pour
  repérer ce qu'il vient d'ouvrir.
- **Joueur en mode « sans personnage »** — il voit exactement ce que voit un
  anonyme : pas de colonne de secrets, pas de bloc de participations.

### Hors périmètre

- **`access` sur Saison et Chapitre**, qui réglerait « la saison en cours »
  déduite du dernier numéro, cf. [newfeatures.md](newfeatures.md).
- **Page d'archive de l'Accueil** — tout l'historique, paginé, pour que
  l'accueil reste court. Les dates suffiront à la produire.
- **Historique des paliers** — une seule date est conservée, celle du palier
  courant.
- **Notification par mail** d'une publication.
- **Date rokuganaise** — chronologie dans le jeu, sans rapport avec ces dates.
- **Union des débloquages d'un joueur à plusieurs personnages** — un seul
  personnage compte, celui que Mon compte a retenu.
- **Le portrait du personnage qui a ouvert un élément** sur la vignette du fil
  personnel — la carte de la colonne des secrets porte l'illustration du
  personnage courant, ce qui suffit tant qu'un seul compte.

### Le piège `|default()` sur `position`

`btns/btn-add-element.html.twig` posait sa position en
`position|default('topright-corner-snap')`. La carte Histoire voulait un bouton d'ajout
centré, donc sans position absolue — mais `position: ''` est *empty* en Twig, et
le défaut reprenait la main : le bouton restait collé en haut à droite. Le
partial est passé en `position is defined ? position : 'topright-corner-snap'`, cf. la même
règle en fin de [code.md](code.md).

Le gabarit Category passe justement `position: ''` pour poser le bouton dans la
`.section-aside` de sa section, et **n'inclut le partial que si la section
déclare un `label_one`** — l'accueil, dont aucune section n'a de bouton
d'ajout, n'en déclare aucun.
