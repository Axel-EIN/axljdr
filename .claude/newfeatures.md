# Idées de fonctionnalités

Deux choses ici : ce qui reste à faire un jour, et ce qui a été écarté
volontairement en cours de route avec la raison — pour ne pas le reproposer
tant que ce n'est pas à l'ordre du jour, et pour retrouver le contexte le jour
où ça le devient.

## Publication des saisons et des chapitres

Le palier `access` et ses dates couvrent treize entités, dont l'épisode, mais
**pas Saison ni Chapitre** : cf. « Accueil » dans
[specs.md](specs.md) pour ce qui est en place.

Conséquence, « la saison en cours » se déduit toujours d'un seul fait : c'est la
dernière créée. La barre de saison de l'accueil Aventure le lit à l'envers — pas
de saison suivante, donc celle-ci est en cours — et le partial Historique
s'appuie sur la même règle pour cacher son titre. Créer une saison à l'avance lui
ferait donc perdre son « en cours » au profit de celle qu'on prépare.

Le jour où on voudra préparer une saison à l'avance : le palier sur Saison et
Chapitre, et « en cours » qui bascule sur la dernière saison publiée.

## Date rokuganaise

Une date dans le jeu — « Fondation de l'Empire, An 10 » — sur `Archive`, sur les
scènes et sur les épisodes. Elle ouvrirait deux vues : la chronologie de l'Empire
côté Archives, et le déroulé de la campagne côté Aventure.

À ne pas confondre avec les dates réelles, `createdAt` et `publishedAt`, qui sont
en place et ne parlent que de la vie du site.

## Objets du personnage

Les sorts connus sont en place — `FichePersonnage.knownSpells`, groupés par
anneau, mécanique révélée dans un popover au clic, cf. « Personnages › Fiche »
dans [specs.md](specs.md). **Il manque le pendant côté objets** : une table de
liaison vers `Objet`, pour dire ce que le personnage possède. La fiche ne
référence aujourd'hui que `arme`, `arme2` et `armure`.

À ne pas confondre avec `Scene.foundObjects` (table `found_object`), qui est en
place : elle dit ce qui a été **trouvé pendant une scène**, et alimente le bloc
« Objets découverts » de l'accueil. Elle ne dit rien de qui le garde.

## Notes des joueurs

Le premier palier est en place, mais **sur le personnage et non sur le joueur** :
`Personnage.playerNotes` et `Personnage.gmNotes`, éditées en place sur le Profil,
cf. « Personnages › Profil » dans [specs.md](specs.md).

Reste à faire, le jour où ça se justifie : une entité `Note` à part, avec une
visibilité privée ou partagée, et un rattachement à une autre entité — PNJ, lieu,
épisode, chapitre.

## Recherche globale

Rien ne permet de retrouver un PNJ, un lieu ou une règle croisés trois saisons
plus tôt. Une recherche sur les noms et descriptions des entités principales,
posée dans la barre de navigation.

## Carte et déplacements

`Lieu` porte déjà `carte`, `locX` et `locY`, mais il n'existe pas de section
Carte à part entière. Une table de liaison personnage ↔ lieu, alimentée par la
scène où le lieu est visité, permettrait de retracer le chemin parcouru.

À vérifier avant de créer la table : le trajet est déjà déductible en suivant
`participations → scène → lieu`. Et `character_unlock` écrit déjà ce lien pour
débloquer les lieux visités, avec sa date et son drapeau `by_meeting` — la table
de liaison demandée ici existe donc en partie, pour les seuls lieux au palier
automatique.

## Timeline du personnage

Les données sont déjà en base dans `Participation` (XP par scène, mort, bonus) :
il ne manque que la vue — progression d'XP, épisodes traversés, changements de
rang.

## Export et impression de la fiche

Pour avoir la fiche sur la table.

## Lanceur de dés roll & keep

Purement front, aucune entité.

## Gestionnaire de combat

Deux voies : réécrire un gestionnaire réactif en JS dans le site, ou reprendre
les projets existants [axljdrbattle-back](https://github.com/Axel-EIN/axljdrbattle-back)
et [axljdrbattle-front](https://github.com/Axel-EIN/axljdrbattle-front) (Node +
React, WebSocket).

Le MJ choisit un lieu, une scène et des personnages, puis déclenche le combat :
choix de posture, jet d'initiative, tours de jeu affichés en direct pour tout le
monde.

Le vrai enjeu côté base n'est pas l'archivage des combats mais les états qui
persistent de l'un à l'autre — points de vie, points de Vide dépensés.
