# Check-list de revue des pages

Revue d'uniformisation, page par page. Ordre : ce qu'un visiteur atteint depuis
l'accueil, du plus important au plus enfoui. Les notes entre parenthèses sont
les divergences déjà repérées, à confirmer au moment de la revue.

Les critères de satisfaction de chaque page cochée sont dans
[specs.md](specs.md).

## Accueil

- [x] **Aventure** — `/` et `/aventure/{id}` · `aventure/index`

`/` redirige vers la saison courante : les deux URL sont la même page.

## Rubriques

- [x] L'Empire — `/empire` · `empire/index` → `category`
- [x] Les Règles — `/regles` · `regles/index` → `category`
- [x] Personnages — `/personnages` · `personnages/index` → `category`

## Pages de détail

- [x] **Épisode** — `/aventure/episode/{id}` · `aventure/episode-detail`
- [x] Lieu — `/empire/lieu/{id}`
- [x] Archive — `/empire/archive/{id}`
- [x] Lore — `/empire/lore/{id}`
- [x] Clan — `/empire/clan/{id}`
- [x] Règle — `/regles/rule/{id}`
- [x] Bibliothèque — `/regles/library/{id}`
- [x] Classe — `/regles/classe/{id}`
- [x] École — `/regles/ecole/{id}`
- [x] Profil personnage — `/personnages/profil/{id}`
- [x] Fiche personnage — `/personnages/fiche/{id}`

## Variantes d'état

- [x] Élément verrouillé — `parts/element-locked`
- [x] Aucune saison — `aventure/aucune-saison`

## Compte et authentification

- [x] Connexion — `/login`
- [x] Inscription — `/register`
- [x] Mot de passe oublié — `/oubli-pass`
- [x] Mot de passe — `/mon_compte/password/edit` et `/reset_pass/{token}`
- [x] Changer d'avatar — `/mon_compte/avatar/edit`
- [x] Mon compte — `/mon_compte`

Les six étendent `other.html.twig`. `/reset_pass/{token}` rend le même template
que `/mon_compte/password/edit` : une seule page pour deux routes.

`registration/confirmation_email.html.twig` n'est pas une page mais le corps du
mail de vérification — hors de cette revue. `/verify/email` ne rend rien, elle
vérifie puis redirige.

## Pages de texte

- [x] Contact — `/contact` (gabarit propre : porte un formulaire, pas un document)
- [x] À propos — `/about`
- [x] CGU — `/cgu`
- [x] Mentions légales — `/mentions/legales`
- [x] Politique de confidentialité — `/politique/confidentialite`

Les quatre dernières étendent `other.html.twig` et n'écrivent que leur titre
et leur contenu.

## Back-office (MJ / admin)

- [x] Accueil du back-office — `/back-office`

Les vingt sections partagent leur liste, leur création et leur édition : chacune
n'écrit que son `_form`. Seule la liste des Utilisateurs reste un tableau écrit à
la main, à migrer vers `list-element`.

- [x] Archives
- [x] Avantages
- [x] Chapitres
- [x] Clans
- [x] Classes
- [x] Compétences
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
