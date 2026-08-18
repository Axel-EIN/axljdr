# Design System — AXL-JDR

Règles de design, de responsive et de gabarits du site. À lire avant toute
intervention sur le Responsive Design, l'UI/UX, le Front End, un template
Twig visuel, ou du CSS — cf. le pointeur dans `CLAUDE.md`.

## Pages de détail sur mobile (`.element-page`)

Tout est porté par `element.html.twig` + `base.css`, il n'y a rien à réécrire
par page : cartes empilées en une colonne, **bord à bord** (0px de marge
latérale, sans bordure ni arrondi — contre 0.5rem partout ailleurs sur le
site, cf. `base.css`), padding de carte fixé à 0.5rem, titre `.title-h2` à
2.5rem avec son `hr` masqué sur mobile (seule exception à la règle
suivante), rien entre le dernier bloc et le footer.

Un `p-3`/`p-4`/`px-5` posé dans un template écrase ce padding (`!important`)
et doit donc être passé en `-lg-`.

Les grilles de vignettes et de portraits portent `.vignette-grid` : deux
colonnes (`col-6`) et 0.5rem de gouttière partout, bords compris.

Les pages de texte simple (à propos, CGU, mentions légales…) suivent le même
principe via `.text-page`, cf. plus bas.

La page épisode ne suit pas ce gabarit (elle a son propre `main`) : ses
équivalents vivent dans `pages/episode.css`.

### Empilement mobile : l'écart dépend de ce qui *termine* la carte

Une carte qui **se termine par du texte** garde un léger écart avec la
suivante (~0.5rem — déjà obtenu nativement par deux `section`/`aside` en
`py-1`, 0.25rem + 0.25rem, rien à ajouter).

Une carte qui **se termine par une image plein cadre** (bandeau bord à bord,
ou une grille "Autres personnages"/"Lieux"/"Écoles" — même en colonnes, elles
remplissent toute la largeur et sont visuellement des images) doit avoir un
écart **nul** avec ce qui suit, carte suivante ou footer : le fond texturé du
site qui apparaîtrait dans l'écart est bien plus visible derrière une grande
image qu'entre deux cartes blanches.

**Corollaire : une image assez grande pour occuper toute la ligne mérite le
même traitement bord à bord**, même si le reste de la carte (titre, texte)
autour d'elle reste en retrait normal. La classe `.img-bleed` (`base.css`)
fait déborder l'image du padding de la carte (0.5rem) via une marge négative
— même mécanique que les grilles ci-dessus, appliquée à une image seule.

Concrètement, sur le conteneur de la carte : `pt-1 pb-0 pb-lg-1` (jamais
`py-1`) — seul le bas est neutralisé, l'écart *avant* la carte reste normal.
`pb-0`/`mb-0` doivent être posés en responsive (`pb-0 pb-lg-*`), jamais
corrigés après coup par une règle CSS : ce sont des utilitaires Bootstrap
`!important`, qu'une règle CSS classique ne peut jamais battre (cf.
philosophie de code, règle 1).

En place : bandeaux d'intro clan/classe (`mb-0 mb-lg-3` sur `#element-banner`),
image de tête + `<hr>` en doublon masqué dans `empire/lore.html.twig`, sections
"Écoles"/"Lieux"/"Personnages" de `empire/clan.html.twig` et "Personnages" de
`regles/classe.html.twig` ; `.img-bleed` sur la carte du Territoire
(`empire/clan.html.twig`). Déjà bon sans y toucher : `personnages/profil.html.twig`
(dernière carte, déjà sans marge basse) et `regles/ecole.html.twig`.

### Titre de page en surimpression sur une image (mobile)

Quand la première carte commence par une grande image bord à bord (pas du
texte), le titre de page (`h1`) se pose **en bas de l'image, centré
horizontalement**, plutôt que d'empiler une bande de titre séparée :

- le `h1` sort du flux (`position: absolute`) ;
- l'image reçoit un léger dégradé noir sur sa partie **basse** pour garder le
  titre blanc lisible sans l'assombrir en entier — classe partagée
  `.gradient-after-down` (`effects.css`), posée sur le **conteneur** de
  l'image (jamais l'`<img>` lui-même, qui ne supporte pas `::after`) ;
- si l'image partage sa carte avec d'autres contenus (icône, texte, badges…)
  qui doivent rester au-dessus du titre plutôt que chevauchés, ils sont
  repoussés d'un `padding-top` dédié.

**Piège vécu, à ne pas reproduire** : `element.html.twig` pose `m-0 p-0` sur
le `h1` — des utilitaires Bootstrap donc `!important`. Tout `padding`/`margin`
posé sur ce `h1` depuis une page est silencieusement ignoré, quels que
soient la valeur ou l'ordre de chargement (même piège que le `mb-3` des
bandeaux clan/classe, cf. plus haut). L'ancrage se pilote uniquement avec
des propriétés que ces utilitaires ne touchent pas :

```css
.element-page > .container > section > h1 {
    position: absolute;
    top: 0;
    left: 1rem;               /* jamais padding-left */
    right: 1rem;               /* jamais padding-right */
    height: calc(40vh - 1rem); /* jamais padding-bottom : la hauteur pilote
                                  la distance au bord bas via align-items */
    display: flex;
    align-items: flex-end;    /* ancrage bas */
    justify-content: center;  /* centrage horizontal */
    color: var(--color-white);
}
```

La hauteur de la zone (`40vh`, ou `var(--profil-banner-h)` sur le profil) doit
correspondre à celle du conteneur d'image juste en dessous (même variable ou
même valeur), pour que le bas de l'un corresponde au bas de l'autre — le `h1`
se positionne par rapport à toute la section, pas juste l'image.

Règle partagée dans `effects.css` (`--overlay-h`, 40vh par défaut,
redéfinissable par page — cf. `pages/lieu.css`) : archive, lieu, école,
règle, bibliothèque en héritent sans rien déclarer. Exception : `pages/profil.css`,
qui n'utilise pas `.gradient-after-down` (bandeau dédié, hauteur
`var(--profil-banner-h)`) et garde donc sa propre règle.

Exception assumée, titre en haut : `components/element-banner-intro.css`
(bandeau d'intro clan/classe, où icône/citation/champion restent sous le
titre plutôt que de partager sa ligne). Garde encore l'ancien
`padding: 0 1rem` — mort pour la même raison (`p-0`/`m-0`), mais sans
symptôme visible : ancrage en haut via `top`, texte centré, donc l'absence de
retrait horizontal ne se voit pas. À corriger si un jour ça devient visible.

## Vocabulaire : les deux styles de vignette

Deux motifs de grille distincts sur le site, à ne pas confondre — utiliser
ces noms pour s'y référer :

- **`diag-corner-vignette`** — coins arrondis en diagonale (haut-gauche +
  bas-droite, les deux autres restent carrés). Porté par `.vignette img`
  (`components.css`), via le composant `parts/element-vignette.html.twig`.
  En place : Archives, Lieux, Lores (`empire/index.html.twig`), Règles de
  Bases, Bibliothèques, Règles Annexes (`regles/index.html.twig`), "Autres X"
  (`empire/lore.html.twig`, `empire/archive.html.twig`, `regles/rule.html.twig`,
  `regles/ecole.html.twig`), Écoles d'une classe (`regles/classe.html.twig`),
  Lieux et Autres Lieux d'un clan (`empire/clan.html.twig`, `empire/lieu.html.twig`).
- **`grid-gallery-vignette`** — planche contact plein cadre : fond sombre,
  vignettes bord à bord sans gouttière sur mobile, 2 colonnes classiques en
  desktop. Porté par `.other-character-grid`/`.other-school-grid`
  (`components/other-elements-grid.css`), via
  `parts/other-character-grid.html.twig`, ou un bloc dédié dans le template
  pour les écoles d'un clan.
  En place : Autres PJs/PNJs (profil), Personnages (classe, école, clan),
  Écoles (clan). Les Lieux (clan, page lieu) sont passés en
  `diag-corner-vignette` — plus cohérent avec le rendu des lieux partout
  ailleurs sur le site.

Un même type de contenu (personnages, lieux, écoles) peut donc apparaître
dans l'un ou l'autre selon la page — c'est le contexte (grille dédiée type
"Autres X" vs galerie plein cadre d'un clan/classe) qui décide, pas le type
d'entité.

## Un titre de section porte toujours son divider

Tout titre de section est suivi d'un `<hr>`, quel que soit le contenu qui
vient après — texte comme grille d'images, mobile comme desktop. Seul le
titre de page (`h1`) fait exception sur mobile (cf. ci-dessus).

## Gabarit des pages de texte simple (à propos, CGU, mentions légales…)

Pour une page qui n'est que du texte (pas de grille, pas de fiche), reprendre
le même gabarit que les pages de détail :

- `<main class="container-fluid text-page">` — la classe `text-page` porte le
  bord-à-bord mobile (parallèle à `.element-page`, mais pour un DOM sans
  `.row` ni colonne : la carte est enfant direct du `.container`, annulée par
  une marge négative plutôt qu'un padding de colonne à zéro) ;
- titre de page en `h1.title-h2` + `hr`, texte dans une ou plusieurs `card
  card-body` (padding desktop en `p-lg-*` uniquement, jamais `p-*` nu) ;
- chaque titre de section interne en `h2.title-h3` + son `hr`.

En place dans `about/`, `cgu/`, `mentions_legales/` et
`politique_confidentialite/`.

## Piège : conteneur sans largeur définie

Rencontré cinq fois. Une image dans un conteneur en *shrink-to-fit* (élément
flex sans classe `col-`) ne peut résoudre aucun dimensionnement relatif —
`img-fluid`, `max-width: %`, `min(Npx, 100%)` — et retombe sur sa **taille
intrinsèque**, souvent 300px et plus.

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

Exemples en place : boutons MJ « Ajouter X » (`parts/btn-box-add-element.html.twig`,
`.btn-add-label`), navigation de saison réduite aux chevrons
(`aventure/index.html.twig`).

## Responsive

Les paliers et les résolutions cibles sont documentés en tête de
`public/css/base.css`. Rappels utiles :

- Bootstrap 4.6 n'offre que 5 paliers (0 / 576 / 768 / 992 / 1200) et n'est
  pas recompilable ici.
- Le basculement mobile/desktop du site est posé à **992px**.
- Une classe `col-N` sans préfixe s'applique à **toutes** les largeurs : c'est
  la cause la plus fréquente des problèmes de responsive du projet. Toujours
  écrire des paires (`col-6 col-md-4 col-lg-2`).
- Les CSS spécifiques à une page vivent dans `public/css/pages/` (ex.
  `pages/lieu.css`) et ne sont chargés que par leur template, via un bloc
  `stylesheets` : leurs sélecteurs y sont donc de fait limités à cette page.
  Un composant partagé par plusieurs templates (bandeaux clan/classe, grilles
  "Autres X"…) vit dans `public/css/components/` (ex. `components/clan-banner.css`).
