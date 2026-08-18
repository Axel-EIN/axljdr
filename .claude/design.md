# Design System — AXL-JDR

Règles de design, de responsive et de gabarits du site. À lire avant toute
intervention sur le Responsive Design, l'UI/UX, le Front End, un template
Twig visuel, ou du CSS — cf. le pointeur dans `CLAUDE.md`.

## Pages de détail sur mobile (`.element-page`)

Tout est porté par `element.html.twig` + `components/element.css`, il n'y a rien à réécrire
par page : cartes empilées en une colonne, **bord à bord** (0px de marge
latérale, sans bordure ni arrondi — contre 0.5rem partout ailleurs sur le
site, cf. `components/element.css`), titre en `heading-elementdetail` avec son
`hr` masqué sur mobile (seule exception à la règle suivante), rien entre le
dernier bloc et le footer.

Un `p-3`/`p-4`/`px-5` posé dans un template écrase ce padding (`!important`)
et doit donc être passé en `-lg-` — ou mieux, écrit en CSS : les marges et le
rythme vertical des trois gabarits (`.element-page`, `.category-page`,
`.other-page`) vivent dans `components/element.css`, `components/category.css` et
`components/other.css`, pas en utilitaires sur les balises de
`element.html.twig`, `category.html.twig` et `other.html.twig`.

La carte principale d'une page de détail se compose toujours en deux niveaux :
la cellule de grille (`col-*`) porte l'espace extérieur et sert de prise au
bord-à-bord mobile, la carte porte la surface. Ni l'une ni l'autre ne porte le
retrait du contenu — il vient des enfants, ce qui laisse l'image de tête
(`parts/card-top-image.html.twig`) toucher les bords et recouvrir les arrondis
hauts. Les valeurs communes vivent en CSS, pas en utilitaires recopiés :
`.card-page` pour le padding bas de la carte, `.card-description` (variante
`.aerated`) pour le retrait, la taille et l'interlignage du texte.

Les grilles de vignettes, de portraits et d'emblèmes descendent à deux colonnes
et gardent leur gouttière de 0.5rem, bords compris.

Les pages de texte simple (à propos, CGU, mentions légales…) et les pages de
compte suivent le même principe via `other.html.twig`, cf. plus bas.

La page épisode ne suit pas ce gabarit (elle a son propre `main`) : ses
équivalents vivent dans `pages/episode.css`.

### Une carte dans une rangée remplit sa hauteur

Deux cartes côte à côte de contenus inégaux : la plus courte flotte à côté de la
plus longue. Les gabarits Element et Other donnent donc `height: 100%` à toute
carte enfant d'une colonne de leur rangée. **Ne pas écrire de `h-100` dans un
template** : sur une rangée à une seule carte la règle n'a aucun effet, elle ne
coûte donc rien là où elle ne sert pas.

Le back-office n'est pas concerné : ses cartes de résumé vivent dans `.page-min`,
pas dans un de ces deux gabarits, et gardent leur `h-100`.

### Écart entre deux cartes empilées

Aucun bloc ne déclare son espacement vertical : `components/element.css` pose son `--gap`
sur la `.row` des pages de détail. `row-gap` sépare les lignes et non les items,
donc deux colonnes côte à côte sur desktop ne sont pas concernées, et il n'y a
rien à écrire — donc rien à oublier — dans les templates. Un `py-1`/`pt-1`/`pb-1`
sur un enfant direct de cette `.row` s'ajouterait à l'écart : il n'y en a plus
aucun, ne pas en réintroduire.

L'écart est uniforme, quel que soit ce qui termine la carte. Une image plein
cadre ou une grille de vignettes n'est plus un cas particulier.

**Une image assez grande pour occuper toute la ligne mérite en revanche le
traitement bord à bord**, même si le titre et le texte autour d'elle restent en
retrait normal. La classe `.img-bleed` (`components/element.css`) fait déborder l'image du
padding de la carte (0.5rem) via une marge négative — même mécanique que les
grilles de vignettes, appliquée à une image seule. En place sur la carte du
Territoire (`empire/clan.html.twig`).

### Du texte sur une image ne va jamais sans assombrissement

**Règle sans exception : tout texte posé sur une image reçoit un dégradé
obscurcissant ou un flou derrière lui.** Une illustration n'a pas de couleur
garantie — un ciel clair, une neige, un mur blanc suffisent à rendre un texte
blanc illisible, et le cas ne se voit qu'une fois le contenu en base.

Le dégradé de tête de carte suit donc le texte, pas le palier : sous 992px le
titre est toujours en surimpression, donc le dégradé est là ; au-dessus, seules
les pages qui superposent quelque chose le gardent, en passant
`gradient-desktop` à `parts/card-top-image.html.twig`. En place sur Lieu (surnom
et stats sur l'image) et École (zone Trait bonus / Compétences / Équipement).

Le `text-shadow` seul ne suffit pas : il sauve un mot sur un fond moyen, pas un
paragraphe sur un fond clair.

**Un dégradé se règle pour la hauteur qu'il couvre, il ne se partage donc pas
entre deux étendues différentes.** Le bandeau de chapitre de l'accueil a besoin
de noir en haut et en bas — « CHAPITRE N » d'un côté, la citation et le bouton
de l'autre. Le même dégradé sur le fond de la page Épisode, qui court jusqu'en
bas de page, s'étirerait sur une hauteur sans rapport. Les deux gardent donc
leur assombrissement, et c'est la seule raison pour laquelle ce bandeau n'est
pas factorisé entre les deux pages.

### Titre de page en surimpression sur une image (mobile)

Quand la première carte commence par une grande image bord à bord, le titre se
pose **en bas de l'image, centré**, plutôt que d'empiler une bande de titre.

**Faire vivre l'élément au même endroit que son repère, jamais tenter de faire
coïncider deux positions.** C'est la règle qui compte ici, et elle a coûté trois
correctifs successifs pour être apprise. Le titre était rendu par le gabarit,
donc ancré sur la `section`, et on essayait de faire tomber le bas de sa boîte
sur le bas de l'image — une boîte qui n'est ni sa sœur ni sa parente. Chaque
tentative a échoué à un palier différent : `40vh` ne suivait pas la largeur,
`56.25vw` décrochait dès que le `.container` se plafonne (540, 720, 960px), et
même le bon ratio laissait un décalage résiduel parce que la carte est 2rem plus
large que la section. Tant que les deux boîtes sont indépendantes, il n'existe
pas de valeur juste — seulement des valeurs justes à une largeur donnée.

La solution est de rendre le titre **dans le conteneur de l'image** :
`parts/card-top-image.html.twig` accepte un paramètre `titre`, et
`.card-top-titre` (`components/element.css`) l'ancre en `bottom: 0`. Le conteneur étant déjà
`position: relative` via `.gradient-after-down`, `bottom: 0` *est* le bas de
l'image, par construction — plus aucune hauteur, aucun ratio, aucun palier à
maintenir, et le retrait du bas devient une valeur choisie plutôt que subie.

**Dupliquer la valeur est acceptable quand ça achète cette stabilité.** Le titre
est écrit deux fois dans le DOM : le `h1` du gabarit et sa copie sur l'image,
chacun masqué à son palier. C'est un compromis assumé, et il reste maintenable
parce que la source est unique — les deux affichent la même variable, l'un en
miroir de l'autre. Mieux vaut ce miroir qu'un calcul de position à refaire à
chaque changement de largeur.

Côté accessibilité, la copie est un `<span aria-hidden="true">` purement
décoratif, et le `h1` du gabarit reste le seul titre sémantique : il passe en
`sr-only` sous 992px, jamais en `display: none`, pour que les lecteurs d'écran
gardent un titre de niveau 1.

Le dégradé qui assure la lisibilité vit sur le même conteneur : classe partagée
`.gradient-after-down` (`effects.css`), posée sur le **conteneur** de l'image et
jamais sur l'`<img>`, qui ne supporte pas `::after`.

En place sur archive, lieu, école, règle, bibliothèque et la page verrouillée.
Exception : `pages/profil.css` et `pages/character-sheet.css`, qui ont leur bandeau dédié
et gardent leur propre règle — elles sont encore sur l'ancien mécanisme et
mériteraient le même traitement.

## Vocabulaire : les trois variantes de la grille d'images

`parts/picture-grid.html.twig` rend les trois motifs du site, choisis par
`variant` — utiliser ces noms pour s'y référer. Ils partagent la grille, le lien,
l'état verrouillé et le réglage des colonnes, et ne diffèrent que par la forme de
l'image et par le nom.

- **`thumbnail`** — coins arrondis en diagonale (haut-gauche + bas-droite, les
  deux autres restent carrés) et **le nom sous l'image**.
  En place : Archives, Lieux, Lores (`empire/index.html.twig`), Règles de Bases,
  Bibliothèques, Règles Annexes (`regles/index.html.twig`), Écoles d'un clan et
  d'une classe, Lieux d'un clan, et tous les blocs « Autres X ».
- **`emblem`** — l'image seule, souvent transparente, détachée sur le fond de
  page, son nom dessous, zoom et reflet au survol. Ni carte ni cartouche.
  En place : Clans Majeurs et Autres factions (Empire), Les Classes (Règles).
- **`portrait`** — planche contact de portraits **carrés, sans nom** : fond sombre
  et images bord à bord sans gouttière sous 992px, liseré interne.
  En place : Autres PJs/PNJs (profil), Personnages d'une classe, d'une école,
  d'un clan.

Un même type de contenu peut apparaître dans l'un ou l'autre selon la page —
c'est le contexte qui décide, pas le type d'entité. Les Lieux et les Écoles d'un
clan sont passés en `thumbnail` : plus cohérent avec le rendu de ces entités
ailleurs, et le nom y apparaît.

**Avec `title`, le composant s'enrobe d'une carte** — titre
`h2.heading-cardtitle`, divider, puis la grille, avec `btn_add` en option. C'est
le bloc « Autres X » qui ferme les pages de détail. Sans `title`, il ne rend que
la grille, et `wrapper` passe alors sur elle.

### Le réglage des colonnes

Jamais de palier à écrire :

- `min_size` — une largeur mini d'élément (défaut 10.5rem) :
  `repeat(auto-fill, …)` en place autant que la largeur disponible en accepte.
  La densité suit l'écran sans media query, et le même bloc sert dans une carte
  pleine largeur comme dans une colonne d'un tiers. **C'est le réglage à
  préférer.**
- `per_row` — un nombre exact de colonnes. Soit un nombre pour tous les paliers,
  soit un tableau `{desktop, tablet, mobile}` : `desktop` est la valeur de base,
  les deux autres ne surchargent que sous 992px et sous 576px. Le composant remet
  alors la largeur mini à zéro, sinon la rangée imposée déborderait sur un petit
  écran.

Les deux ne se cumulent pas : `per_row` gagne.

**Le compte transite par une variable intermédiaire, `--cols`.** Les valeurs
arrivent en style inline, qui gagne sur toute feuille : un `@media` ne pourrait
jamais surcharger `--cols-desktop` directement. Le CSS lit donc les variables
posées en inline pour en dériver `--cols`, qui elle vit dans la feuille et se
laisse surcharger palier par palier.

**La grille cible son image par une classe, jamais par la balise `img`.** Un
`.picture-grid img { width: 100% }` attrape aussi les surimpressions posées
dessus — cadenas, badge PDF — avec une spécificité qui bat leur propre règle.
D'où `.picture` sur l'image de chaque variante, et `.ratio-cropped` sur celle que
le cadre recadre.

**Les coins arrondis vivent sur le cadre, pas sur l'image** : `.frame` porte le
rayon et `overflow: hidden`, sinon la règle attrape le cadenas et le badge PDF.

### Deux autres paramètres communs

**`limit`** coupe la liste à N éléments côté vue, quand le contrôleur n'a pas de
raison de le faire — Personnages d'une classe en montre 16.

**L'état verrouillé** est le même dans les trois variantes : pour un joueur,
l'élément n'est plus cliquable, il est grisé, un cadenas y bat, une infobulle
annonce « À débloquer » et le nom devient « ??? ». Le MJ le voit normalement, avec
un simple cadenas en coin qui signale qu'il est caché aux joueurs.

## Un titre de section porte toujours son divider

Tout titre de section est suivi d'un `<hr>`, quel que soit le contenu qui
vient après — texte comme grille d'images, mobile comme desktop.

Deux exceptions, et seulement deux : le titre de page (`h1`) sur mobile, où il
passe sur l'image (cf. ci-dessus) ; et la variante `heading-cardtitle.decorated`,
un titre de section *à l'intérieur* d'une carte de page, séparé du bloc précédent
par un ornement décoratif — c'est lui qui joue le rôle du divider, et le titre est
alors centré sous lui. En place sur les sections Carte et Plan de la page Lieu.

## Gabarit des pages de texte simple (à propos, CGU, mentions légales…)

Une page qui n'est que du texte étend `other.html.twig`, et n'écrit que son
titre et son contenu :

```twig
{% extends 'other.html.twig' %}
{% set titre = 'Mentions Légales' %}
{% block contenu %} … {% endblock %}
```

Le gabarit reprend le DOM d'`element.html.twig` — `.element-page`, puis
`.container > section > .row > .col-12 > .card.card-page` — donc il hérite du
même bord-à-bord mobile, des mêmes coins, de la même fusion avec le footer.
Ce qu'il n'a pas : image de tête, boutons MJ, sommaire, colonnes multiples.

Le titre passe par `titre` et non par trois chaînes recopiées : le gabarit le
lit pour le `<title>`, le fil d'Ariane et le `h1`.

Le texte est à nu dans la carte, donc c'est la carte qui porte son padding
(`components/other.css`) — là où les pages Element le font porter à leur
`card-description`. Chaque titre de section interne reste un
`h2.heading-cardtitle` suivi de son `hr`.

En place dans `about/`, `cgu/`, `mentions_legales/` et
`politique_confidentialite/`. La page Contact garde un gabarit propre : elle
porte un formulaire, pas un document.

## Colonnes des listes du back-office

`back_office/list-element.html.twig` rend n'importe quelle liste d'entité à
partir de chaînes décrites par le contrôleur, une par colonne :

```
- path                    → propriété ou chemin pointé (ex: "saisonParent.titre")
- Label                   → en-tête, défaut = path
- format ∈ {string (defaut), number, symbol, image, bool, boolInt, color}
    string   : texte (gauche)
    number   : texte aligné à droite
    symbol   : image carrée 48×48 (mon, icone…)
    image    : image landscape, hauteur 64px (illustration, bannière…)
    bool     : check verte si la valeur est remplie, vide sinon (test de présence)
    boolInt  : "Oui" si val == 1, "Non" si val == 0 (booléen stocké en smallint)
    color    : code hex + swatch
- extra → sens différent selon le format :
    pour symbol/image : nom complet du placeholder (ex: "NA_SAISON")
                        défaut = NA_ICON (symbol) ou NA_169 (image)
                        Peut contenir des tokens {genre}, {anneau} qui
                        sont résolus depuis l'élément courant (ex:
                        "NA_PERSO_PORTRAIT_{genre}" → NA_PERSO_PORTRAIT_M/F selon
                        la valeur. Token vide → underscore final retiré.)
    pour string/number/bool/boolInt/color : "bold" pour mettre en gras
    Exemples : "image:Image:image:NA_SAISON", "nom:Nom::bold", "estMajeur:Majeur:boolInt",
              "icone:Portrait:symbol:NA_PERSO_PORTRAIT_{genre}"
```

## Piège : une couleur héritée ne bat jamais une couleur posée

Poser `color` sur un conteneur ne colore ses enfants que s'ils n'en déclarent
pas. Or presque toutes les classes de titre et de libellé du site en déclarent
une — `heading-cardtitle`, `label-amerigo`, `heading-scene`… **Une couleur
héritée perd donc toujours contre elles, quelle que soit la spécificité** : ce
n'est pas un arbitrage de cascade, la règle directe s'applique et l'héritage
n'entre jamais en jeu.

Un conteneur clair posé sur une image doit donc viser ses titres explicitement,
et non compter sur son propre `color`. Deux cas l'ont montré : les libellés des
filtres de la rubrique Personnages, et les trois titres posés sur l'image de la
page École — dont la règle correctrice existait mais visait `heading-cardtitle`
sans point, donc une balise inexistante.

## Piège : `col-*` et `row` sur la même balise

Il n'en reste aucun dans le projet — si un `grep` en fait réapparaître un, c'est
une régression.


Une colonne fait déjà 100% de la largeur de sa rangée. Lui ajouter `row`
plaque dessus les `-15px` de marge que Bootstrap prévoit pour une rangée
posée dans un conteneur *paddé* : la boîte ne s'élargit pas, elle **glisse de
15px vers la gauche**. Bord gauche à peu près en place, bord droit 30px trop
court — c'est la signature du bug.

Deux effets s'ajoutent :

- imbriquée dans une autre `col-* row`, la même balise cumule les décalages
  (16px à gauche, 32px à droite) ;
- `row` étant `display: flex`, un enfant qui n'est pas une colonne devient un
  élément flex sans `flex-grow` : il **se réduit à la largeur de son contenu**.
  Une ligne d'accordéon repliée finit à 400px, puis reprend sa largeur une fois
  dépliée, quand le contenu suffit.

Les deux rôles se séparent : la colonne garde `col-*` pour sa largeur, et le
`display: flex` se pose en CSS quand ses enfants en ont besoin
(`.library-row`). Aucun `row` sur un `col-*`.

## Piège : conteneur sans largeur définie

Rencontré six fois. Une image dans un conteneur en *shrink-to-fit* (élément
flex sans classe `col-`) ne peut résoudre aucun dimensionnement relatif —
`img-fluid`, `max-width: %`, `min(Npx, 100%)` — et retombe sur sa **taille
intrinsèque**, souvent 300px et plus.

**Cause corrigée à la racine** : la famille `.img-*` d'`icons.css` déclarait sa
taille en `max-width: min(Npx, 100%)`. Un pourcentage dans une contrainte ne se
résout pas au calcul des tailles intrinsèques, donc le navigateur retombait sur
le fichier. Les vingt classes portent désormais une `width` en dur, avec
`max-width: 100%` comme seul garde-fou — le rendu est identique, mais les
contributions `min-content` et `max-content` valent enfin la taille annoncée.
Ne pas revenir à une contrainte en pourcentage seule.

Le symptôme le plus déroutant vient du `min-content` : une image ne pouvant pas
être coupée, un flex item qui la contient ne peut pas rétrécir sous sa
largeur intrinsèque — il **déborde** de son conteneur, et aucun `justify-content`
n'y change rien. C'est ce qui arrivait à la liste d'infos du Lieu, où un mon de
clan de 320×320 affiché en `.img-24` imposait 428px de largeur minimale.

Symptômes observés : portraits sur une seule colonne au lieu de deux, blasons
de famille démesurés. Le correctif est toujours de donner une vraie classe de
colonne au conteneur, pas de bricoler la taille de l'image.

**Le symptôme peut aussi être le conteneur, pas l'image.** Le pourcentage est
ignoré au calcul de la contribution max-content : le conteneur se dimensionne
sur la taille intrinsèque du fichier, puis l'image se recadre correctement
dedans. Si le conteneur porte un fond ou une bordure, on voit une grande zone
colorée à côté d'une image à la bonne taille — c'était le cas de l'avatar de la
navbar (`header.css`, `#user-zone avatar`), où `.img-96` restait à 96px dans un
bloc `bg-secondary` de 300 à 540px selon le fichier. Largeur en **px** ici, pas
en rem : la base du site est à 18px, donc `6rem` dépasserait `.img-96` de 12px.

## Libellés qui débordent sur mobile

Masquer le texte, garder l'icône, via un `<span class="d-none d-lg-inline">`
autour du libellé. Ne pas dupliquer le libellé en deux variantes.

Exemples en place : boutons MJ « Ajouter X » (`parts/btn-ajouter-element.html.twig`,
`.btn-add-label`), navigation de saison réduite aux chevrons
(`aventure/index.html.twig`).

## Responsive

```
   Résolutions cibles produit (référence). Les largeurs sont en pixels
   d'interface (DP), pas en pixels physiques :

     Palier                 DP     Appareil cible          Orient.  Pouces  Réels  PPI  DPR
     Small Mobile           320    iPhone SE               portrait   4.7"    750  326  x2
     Mobile                 360    Android 9A              portrait   6.3"   1080  422  x3
     Large Mobile           420    iPhone Air              portrait   6.5"   1260  460  x3
     7p Tablet              520    Galaxy Tab A7           portrait   7.0"    800  216  x1.5
     10p Tablet             800    Pixel Tablet            portrait  10.0"   1600  276  x2
     Laptop                1280    MacBook Air             paysage   13.6"   2560  224  x2
     Laptop Medium         1440    Asus Zenbook 14         paysage   14.0"   2880  243  x2
     Laptop High           1560    Huawei MateBook X Pro   paysage   14.0"   3120  264  x2
     Desktop HD            1920    Iiyama ProLite          paysage   23.0"   1920   92  x1
     Desktop Ultra Wide    2560    Iiyama G-Master         paysage   32.0"   3440  110  x1

   Bootstrap 4.6 (chargé en CDN, non recompilable ici) n'offre que 5 paliers
   fixes en min-width : 0 / 576 / 768 / 992 / 1200px. Correspondance réelle
   entre ces paliers et les cibles ci-dessus :

     col-*      0px     Small Mobile, Mobile, Large Mobile ET 7p Tablet (520)
     col-sm-*   576px   aucune cible — palier vide
     col-md-*   768px   10p Tablet (800)
     col-lg-*   992px   aucune cible — palier vide
     col-xl-*   1200px  Laptop 1280 → Desktop Ultra Wide 2560, confondus


   CONVENTION @media — le versant mobile s'ecrit `min du palier suivant
   - 0.02px`, jamais en entier, donc : 575.98 / 767.98 / 991.98 / 1199.98.
   Bootstrap declenche .col-lg-* a 992px PILE, donc un `max-width: 992px` s'applique en
   meme temps et c'est l'ordre de declaration qui tranche. Le 0.02 est la
   valeur de la mixin media-breakpoint-down de Bootstrap (arrondi Safari).
   Exception : 480px n'est pas un palier Bootstrap, n'a aucun min-width en
   face, et reste ecrit en entier.
```

Rappels utiles :

- Bootstrap 4.6 n'offre que 5 paliers (0 / 576 / 768 / 992 / 1200) et n'est
  pas recompilable ici.
- Le basculement mobile/desktop du site est posé à **992px**.
- Une classe `col-N` sans préfixe s'applique à **toutes** les largeurs : c'est
  la cause la plus fréquente des problèmes de responsive du projet. Toujours
  écrire des paires (`col-6 col-lg-4`). Pour une grille d'éléments, ne pas
  écrire de colonnes du tout : passer `per_row` ou `min_size` à
  `parts/picture-grid.html.twig`, cf. « les trois variantes de la grille ».
- Les CSS spécifiques à une page vivent dans `public/css/pages/` (ex.
  `pages/location.css`) et ne sont chargés que par leur template, via un bloc
  `stylesheets` : leurs sélecteurs y sont donc de fait limités à cette page.
  Un composant partagé par plusieurs templates (bandeaux clan/classe, grilles
  "Autres X"…) vit dans `public/css/components/`.
