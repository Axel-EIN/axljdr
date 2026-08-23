# Idées de fonctionnalités

Deux choses ici : ce qui reste à faire un jour, et ce qui a été écarté
volontairement en cours de route avec la raison — pour ne pas le reproposer
tant que ce n'est pas à l'ordre du jour, et pour retrouver le contexte le jour
où ça le devient.

## Brouillon et publication des saisons

Aujourd'hui « la saison en cours » se déduit d'un seul fait : c'est la dernière
créée. La barre de saison de l'accueil le lit à l'envers — pas de saison
suivante, donc celle-ci est en cours — et le partial Historique s'appuie sur la
même règle pour cacher son titre.

Conséquence acceptée : créer une saison à l'avance ferait immédiatement perdre
son « en cours » à la saison réellement jouée. Ça ne gêne pas tant que le MJ
crée les saisons au fil du jeu.

Le jour où on voudra préparer une saison à l'avance, il faudra un état de
publication (brouillon / publiée) plutôt qu'une déduction sur le numéro. Ça
donnerait aussi de quoi préparer chapitres et épisodes sans les exposer.

## Chronologie

Deux dates différentes à ne pas confondre.

**La date réelle.** Il n'y a pas de date en base : « le plus récent » se déduit
du numéro donné par le MJ. L'accueil affiche donc les chapitres du numéro le
plus élevé au plus bas, et c'est cet ordre-là qui définit la récence. Avec des
dates, on pourrait distinguer l'ordre de lecture (le numéro, choisi par le MJ)
de la chronologie réelle, et calculer « récent » sur autre chose qu'un rang.

**La date dans le jeu.** Une date rokuganaise sur `Archive` (« Fondation de
l'Empire — An 10 ») et sur les scènes et épisodes ouvre deux vues : la
chronologie de l'Empire côté Archives, et le déroulé de la campagne côté
Aventure.

## Rubrique « Quoi de neuf »

L'accueil du site est pour l'instant la page Aventure de la saison courante.
Elle doit céder la place à une rubrique « Quoi de neuf », qui reste à définir.

## Sorts et objets du personnage

Deux tables de liaison depuis `FichePersonnage` : une vers `Sort`, une vers
`Objet`. La fiche ne référence aujourd'hui que `arme`, `arme2` et `armure` —
rien ne dit ce que le personnage possède, ni ce qu'il sait lancer.

Les sorts s'affichent groupés par élément, leur mécanique se révélant au survol
sur desktop et au clic sur mobile. Les objets reprennent ce qui a été choisi à
la création du personnage.

## Notes des joueurs

Aucune entité `Note` n'existe. Premier palier : une seule zone éditable par
joueur. Ensuite une table, avec une visibilité privée ou partagée, et un
rattachement à une entité — PNJ, lieu, épisode, chapitre.

## Recherche globale

Rien ne permet de retrouver un PNJ, un lieu ou une règle croisés trois saisons
plus tôt. Une recherche sur les noms et descriptions des entités principales,
posée dans la barre de navigation.

## Carte et déplacements

`Lieu` porte déjà `carte`, `locX` et `locY`, mais il n'existe pas de section
Carte à part entière. Une table de liaison personnage ↔ lieu, alimentée par la
scène où le lieu est visité, permettrait de retracer le chemin parcouru.

À vérifier avant de créer la table : le trajet est déjà déductible en suivant
`participations → scène → lieu`.

## Timeline du personnage

Les données sont déjà en base dans `Participation` (XP par scène, mort, bonus) :
il ne manque que la vue — progression d'XP, épisodes traversés, changements de
rang.

## Personnage principal du joueur

Un joueur peut avoir plusieurs personnages ; en désigner un comme principal
donnerait un avatar par défaut à qui n'en a pas choisi. Le repli actuel est un
portrait aléatoire servi par `pravatar.cc`.

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
