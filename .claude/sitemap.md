# Plan du site

Toutes les pages du site, dans l'ordre où un visiteur les atteint depuis
l'accueil. Sert de plan et de check-list : **la case est cochée quand la page a
passé la revue d'uniformisation**, dont les critères sont dans
[specs.md](specs.md).

Chaque ligne donne l'URL puis le template, quand il ne se déduit pas de la
rubrique. Les notes entre parenthèses sont les divergences repérées, à confirmer
au moment de la revue.

## Accueil

- [x] **Accueil** — `/` · `news/index` → `category`, cinq sections

## Aventure

- [x] **Aventure** — `/aventure` et `/aventure/{id}` · `aventure/index`
- [x] **Épisode** — `/aventure/episode/{id}` · `aventure/episode-detail`

`/aventure` redirige vers la saison courante : les deux URL sont la même page.

## Personnages

- [x] Rubrique — `/personnages` · `personnages/index` → `category`
- [x] Profil — `/personnages/profil/{id}` · `personnages/character-profil`
- [x] Fiche — `/personnages/fiche/{id}` · `personnages/character-sheet`

## Empire

- [x] Rubrique — `/empire` · `empire/index` → `category`
- [x] Clan — `/empire/clan/{id}` · `empire/clan`
- [x] Archive — `/empire/archive/{id}` · `empire/archive`
- [x] Lieu — `/empire/lieu/{id}` · `empire/location`
- [x] Lore — `/empire/lore/{id}` · `empire/lore`

## Règles

- [x] Rubrique — `/regles` · `regles/index` → `category`
- [x] Règle — `/regles/rule/{id}` · `regles/rule`
- [x] Bibliothèque — `/regles/library/{id}` · `regles/library-detail`
- [x] Classe — `/regles/classe/{id}` · `regles/class-detail`
- [x] École — `/regles/ecole/{id}` · `regles/dojo-detail`

## Compte et authentification

- [x] Connexion — `/login`
- [x] Inscription — `/register`
- [x] Mot de passe oublié — `/oubli-pass`
- [x] Mot de passe — `/mon_compte/password/edit` et `/reset_pass/{token}`
- [x] Changer d'avatar — `/mon_compte/avatar/edit`
- [x] Mon compte — `/mon_compte`

Les six étendent `other.html.twig`. `/reset_pass/{token}` rend le même template
que `/mon_compte/password/edit` : une seule page pour deux routes.

## Pages de texte

- [x] Contact — `/contact` (gabarit propre : porte un formulaire, pas un document)
- [x] À propos — `/about`
- [x] CGU — `/cgu`
- [x] Mentions légales — `/mentions/legales`
- [x] Politique de confidentialité — `/politique/confidentialite`

Les quatre dernières étendent `other.html.twig` et n'écrivent que leur titre et
leur contenu.

## Variantes d'état

Rendues à la place d'une page, sans URL propre.

- [x] Élément non lisible — `element-locked` (gabarit Element)
- [x] Élément introuvable — `element-hidden` (gabarit Other, rendu avec un
  statut 404 : ni listé ni lisible)
- [x] Aucune saison — `aventure/aucune-saison`

## Back-office (MJ / admin)

- [x] Accueil du back-office — `/back-office`

Chaque entité a ses trois vues — liste `/admin/{entité}`, création
`/admin/{entité}/create`, édition `/admin/{entité}/{id}/edit` — et n'écrit qu'un
fichier, son `_form.html.twig` ; la suppression est une route POST sans page.
Seule la liste des Utilisateurs reste un tableau écrit à la main, à migrer vers
`list-element`.

- [x] Archives
- [x] Avantages
- [x] Chapitres
- [x] Clans
- [x] Classes
- [x] Compétences
- [x] Développements
- [x] Écoles
- [x] Épisodes
- [x] Familles
- [x] Fiches
- [x] Bibliothèques
- [x] Lieux
- [x] Lores
- [x] Objets
- [x] Personnages
- [x] Règles
- [x] Saisons
- [x] Scènes
- [x] Sorts
- [x] Utilisateurs

## Routes sans page

- `/logout` et `/verify/email` — traitent puis redirigent.
- `/mon_compte/main-character` — POST seulement : reçoit le personnage principal
  choisi et renvoie sur Mon compte.
- `/personnages/fiche/{id}/edit` — reçoit le formulaire de fiche du joueur et
  renvoie sur la fiche.
- `/personnages/profil/{id}/edit` — reçoit les trois zones de texte du profil et
  renvoie sur le profil.
- `/admin/{entité}/{id}/delete` — POST seulement.
- `app:unlocks:backfill` n'est pas une route mais une commande
  (`src/Command/BackfillUnlocksCommand.php`) : elle sème les débloquages du
  palier automatique depuis les rencontres et visites déjà jouées.
- `registration/confirmation_email.html.twig` n'est pas une page mais le corps du
  mail de vérification.
