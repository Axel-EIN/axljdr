# Audit d'harmonisation — 30/08/2026

Relevé des divergences de comportement et des duplications repérées lors d'un
passage sur l'ensemble du code (21 contrôleurs admin, 29 formulaires,
104 templates, 46 CSS). Le point 6 d'origine — factorisation du champ image du
back-office — a été traité le 31/08 et n'apparaît plus ici que par ses restes.

Rien n'a été touché sur les points ci-dessous : ils attendent d'être repris un
par un, en vérifiant à chaque fois l'étanchéité avant de bouger quoi que ce soit.

---

## 1. La rubrique Aventure ignore la visibilité

`Episode` porte `PublishableTrait`, est déclaré dans
[`EntityRegistry`](../src/Service/EntityRegistry.php) avec sa route, et
`AdminEpisodeType` expose bien `PublishableFields`. Mais :

- [`AventureController::viewEpisode`](../src/Controller/AventureController.php)
  n'appelle pas `accessGuard()`, contrairement à Empire, Règles et Personnages ;
- aucun template de `templates/aventure/` n'appelle `element_listed` ou
  `element_readable` — seul `episode-card.html.twig` utilise `element_fresh`
  pour le badge « nouveau ».

Conséquence : un épisode `hidden` ou non publié s'affiche dans sa saison et se
lit à son URL. C'est le seul trou de la mécanique de publication.

## 2. Trois listes de règles sur quatre ne filtrent pas

[`list-objet.html.twig`](../templates/regles/list-objet.html.twig) filtre ses
items sur `element_readable`. `list-sort`, `list-avantage` et `list-competence`
ne le font pas, alors que `Sort`, `Avantage` et `Competence` portent tous
`PublishableTrait`.

## 3. Listes déroulantes d'entités non triées

Corrigé le 30/08 pour `personnage` (fiche MJ, archive, chef de clan et de
famille). Restent sans `query_builder`, donc en ordre d'id :

| Formulaire | Champ | Entité |
| --- | --- | --- |
| `AdminAvantageType` | `exclusive`, `discountClasse` | `Classe` |
| `AdminAvantageType` | `discountClan`, `discountClan2` | `Clan` |
| `AdminChapitreType` | `saisonParent` | `Saison` |
| `AdminEcoleType` | `classe`, `clan` | `Classe`, `Clan` |
| `AdminEpisodeType` | `chapitreParent` | `Chapitre` |
| `AdminFamilleType` | `clan` | `Clan` |
| `AdminLieuType` | `clan` | `Clan` |
| `AdminPersonnageType` | `clan`, `famille`, `classe`, `ecole`, `joueur` | — |
| `AdminSceneType` | `episodeParent`, `lieu` | `Episode`, `Lieu` |

Les `placeholder` n'ont par ailleurs aucune convention : `''`, `'Non défini'`,
`'Aucune famille'`, `'Pas de lieu défini'`, `'PNJ / Aucun Joueur-Défini'`,
`'— Aucun —'`, `'— Aucune —'`, `'Pas encore défini'`, `'Aucun bonus'`,
`'Pas de genre défini'`. À trancher en une forme unique avant de reprendre les
formulaires.

## 4. Champs image inégalement outillés

Depuis la factorisation, chaque écart se lit au point d'appel de
[`parts/image-field.html.twig`](../templates/parts/image-field.html.twig).

**Sans recadrage** (pas de `width`/`height` passés) : `objet.image`,
`classe.image`, `clan.territoireCarte`, `lieu.carte`. Ajouter les dimensions
cibles suffit à l'activer.

**Sans suppression** (`deletable: false`) : `clan.territoireCarte`, `lieu.carte`
— et `clan.video`, resté en markup propre. Le bouton n'est pas posé parce
qu'**aucun contrôleur ne lit `remove_territoireCarte`, `remove_carte` ni
`remove_video`** : il faut traiter le contrôleur avant d'enlever le drapeau,
sinon le bouton ne fait rien.

`clan.video` est le seul champ resté hors du partial : son markup `<video>`
(poster, `controls`, source) ne ressemble pas aux autres, et il n'est utilisé
qu'une fois — le factoriser reviendrait à compliquer le partial pour un appelant.

## 5. Messages flash

Une soixantaine de chaînes écrites à la main, sans forme commune :

- **Accords faux** : « **Le** Famille a bien été ajoutée », « **La** Avantage a
  bien été supprimée » face à « **L'**Avantage a bien été ajouté », « Le Lore a
  bien été **modifiée** ».
- **Fautes** : « crée » pour créé, « créee » pour créée, « au prélable » pour
  au préalable (6 occurrences).
- **Casse instable** : `L'Archive` à la création, `l'archive` à la suppression ;
  idem pour `Lore`, `Règle`, `Bibliothèque`, `Sort`, `Objet`.
- **Ponctuation** : point final présent ou absent selon la rubrique.
- **Vocabulaire** : « La faction a bien été supprimée » alors que tout le
  back-office dit « Clan ».
- Guillemets simples et doubles mélangés.

À traiter avec le point 8 : si `EntityRegistry` porte le libellé et le genre,
les trois messages se génèrent depuis une seule source.

## 6. Le test de redirection, 40 fois

```php
if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'x')
```

Le `!empty()` ne sert à rien puisque la comparaison qui suit le couvre. Une
méthode de trait — `redirectBack($request, 'x', 'route', $params)` — remplace
les 40 occurrences, réparties sur 19 contrôleurs.

## 7. Libellés d'entité recopiés à la main

`label`, `labels`, `genre` et `determinant` sont réécrits dans chaque action,
soit environ 84 fois — alors que
[`EntityRegistry`](../src/Service/EntityRegistry.php) existe déjà et porte
`label`. Lui ajouter `labels`, `genre` et `determinant` couvre les trois
gabarits du back-office et les messages du point 5.

Deux variables sont par ailleurs **passées pour rien** :

- `list-element.html.twig` n'utilise pas `determinant` (il le dérive de `genre`),
  et les 21 contrôleurs le lui passent quand même ;
- `create.html.twig` et `edit.html.twig` n'utilisent pas `genre`, passé lui aussi
  à chaque fois.

Enfin, `create`/`edit` lisent `entity` là où `list-element` lit `element` : deux
noms pour la même chose.

## 8. Préremplissage + renumérotation dupliqués

Le bloc « lire `numero` / `tab` / `subtab` dans la query string, puis renuméroter
la fratrie via `Numeroteur` » est recopié dans `AdminSortController`,
`AdminObjetController`, `AdminLibraryController`, `AdminRuleController`,
`AdminEpisodeController`, `AdminChapitreController`, `AdminSaisonController` et
`AdminSceneController`. Seul varie le nom du champ parent (`base`, `anneau`,
`type`, `chapitreParent`, `episodeParent`).

## 9. Deux accès à l'EntityManager dans le même fichier

20 contrôleurs admin sur 21 injectent `EntityManagerInterface $em` dans l'action
de création, puis reprennent `$this->getDoctrine()->getManager()` dans l'édition
et la suppression. Seul `AdminDevelopmentController` est homogène.

`getDoctrine()` est déprécié en Symfony 5.4 et supprimé en 6.0 : c'est autant à
reprendre le jour de la montée de version, autant le faire une fois pour toutes.

## 10. Détails de forme

- Les 18 méthodes `add*` des contrôleurs admin — plus `editScene` — n'ont pas de
  type de retour `: Response`, que toutes les autres actions déclarent.
- `AdminClanController::afficherAdminClans` et
  `AdminUtilisateurController::viewUtilisateurs` sortent de la nomenclature
  `viewAdmin<Entités>` suivie par les 19 autres.
- La liste des utilisateurs trie en `id ASC` là où les 20 autres trient en
  `id DESC`.
- Les includes de `parts/` s'écrivent tantôt avec un `/` initial
  (`include('/parts/btn-form...')`), tantôt sans — parfois pour le même fichier
  (`messages-flashes` est appelé des deux façons).
- Syntaxe de tableau mélangée dans les appels aux repositories : `findBy(array(),
  array('id' => 'DESC'))`, `findBy([], ['id' => 'DESC'])` et
  `findBy( [] , ['id' => 'DESC'] )`.
- CSS : 5 media queries à `480px` et une à `767.98px` s'écartent des paliers
  `575.98` / `991.98` utilisés partout ailleurs.
- Sur validation invalide, la création rend un 200 (`render` + `createView()`)
  et l'édition un 422 (`renderForm`). Sans Turbo côté client, c'est sans effet
  visible — mais les deux actions devraient répondre pareil.
