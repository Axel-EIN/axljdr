<?php

namespace App\Controller;

use App\Entity\FichePersonnage;
use App\Entity\Personnage;
use App\Form\JoueurFichePersonnageType;
use App\Repository\AvantageRepository;
use App\Repository\ChapitreRepository;
use App\Repository\CompetenceRepository;
use App\Repository\EpisodeRepository;
use App\Repository\ObjetRepository;
use App\Repository\PersonnageRepository;
use App\Repository\SaisonRepository;
use App\Repository\SortRepository;
use App\Service\ClasseurHistorique;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonnagesController extends AbstractController
{
    use LockedTrait;

    /**
     * @Route("/personnages", name="personnages")
     */
    public function viewPersonnages(
        Request $request,
        PersonnageRepository $personnageRepository,
        SaisonRepository $saisonRepository,
        ChapitreRepository $chapitreRepository,
        EpisodeRepository $episodeRepository
    ): Response {
        // Si aucun paramètre n'est explicitement dans l'URL, on applique le défaut :
        // dernière saison + dernier chapitre + dernier épisode.
        // Si l'utilisateur a explicitement choisi "Toutes les saisons" (param présent mais vide),
        // on respecte son choix et on ne pré-filtre pas.
        $saisonId   = null;
        $chapitreId = null;
        $episodeId  = null;

        if ($request->query->has('saison')) {
            $rawSaison = $request->query->get('saison');
            $saisonId = $rawSaison !== '' && $rawSaison !== null ? (int) $rawSaison : null;

            if ($request->query->has('chapitre')) {
                $rawChapitre = $request->query->get('chapitre');
                $chapitreId = $rawChapitre !== '' && $rawChapitre !== null ? (int) $rawChapitre : null;

                if ($request->query->has('episode')) {
                    $rawEpisode = $request->query->get('episode');
                    $episodeId = $rawEpisode !== '' && $rawEpisode !== null ? (int) $rawEpisode : null;
                }
            }
        } else {
            $latestSaison = $saisonRepository->findCourante();
            if ($latestSaison !== null) {
                $saisonId = $latestSaison->getId();
                $latestChapitre = $chapitreRepository->findOneBy(
                    ['saisonParent' => $saisonId],
                    ['numero' => 'DESC']
                );
                if ($latestChapitre !== null) {
                    $chapitreId = $latestChapitre->getId();
                }
            }
        }

        // Le chapitre est rattaché à une saison : on ignore le chapitre si la saison ne correspond pas.
        if ($chapitreId !== null && $saisonId !== null) {
            $chapitre = $chapitreRepository->find($chapitreId);
            if ($chapitre === null || $chapitre->getSaisonParent()->getId() !== $saisonId) {
                $chapitreId = null;
                $episodeId  = null;
            }
        }

        // L'épisode est rattaché à un chapitre : on l'ignore si le chapitre ne correspond pas.
        if ($episodeId !== null && $chapitreId !== null) {
            $episode = $episodeRepository->find($episodeId);
            if ($episode === null || $episode->getChapitreParent()->getId() !== $chapitreId) {
                $episodeId = null;
            }
        }

        $pjs  = $personnageRepository->findAllPJsSorted($saisonId, $chapitreId, $episodeId);
        $pnjs = $personnageRepository->findAllPNJsSorted($saisonId, $chapitreId, $episodeId);

        $saisons   = $saisonRepository->findBy([], ['numero' => 'ASC']);
        $chapitres = $saisonId !== null
            ? $chapitreRepository->findBy(['saisonParent' => $saisonId], ['numero' => 'ASC'])
            : [];
        $episodes = $chapitreId !== null
            ? $episodeRepository->findBy(['chapitreParent' => $chapitreId], ['numero' => 'ASC'])
            : [];

        $sections = [];
        $sections[0]['name'] = "PJs";
        $sections[0]['entity'] = 'personnage';
        $sections[0]['label_one'] = "un personnage";
        $sections[0]['titleLight'] = '';
        $sections[0]['titleStrong'] = 'Personnages Joueurs';

        $sections[1]['name'] = "PNJs";
        $sections[1]['entity'] = 'personnage';
        $sections[1]['label_one'] = "un personnage";
        $sections[1]['titleLight'] = '';
        $sections[1]['titleStrong'] = 'Personnages non-joueurs';

        $header_classname = 'characters';
        $header_up = "Les Héros de l'Aventure";
        $header_down = 'Les Personnages';
        $category = 'personnage';

        return $this->render('personnages/index.html.twig', [
            'pjs' => $pjs,
            'pnjs' => $pnjs,
            'sections' => $sections,
            'header_classname' => $header_classname,
            'header_up' => $header_up,
            'header_down' => $header_down,
            'category' => $category,
            'saisons' => $saisons,
            'chapitres' => $chapitres,
            'episodes' => $episodes,
            'selected_saison_id'   => $saisonId,
            'selected_chapitre_id' => $chapitreId,
            'selected_episode_id'  => $episodeId,
        ]);
    }

    /**
     * @Route("/personnages/profil/{id}", name="personnage_profil")
     */
    public function viewPersonnageProfil(Personnage $personnage, PersonnageRepository $personnageRepository, ClasseurHistorique $classeur, SaisonRepository $saisonRepository): Response {

        if ($response = $this->lockedPage($personnage, 'personnage', 'personnages')) {
            return $response;
        }

        $autresPersonnages = $personnage->getEstPj()
            ? $personnageRepository->findAllPJsExceptOne($personnage->getId())
            : $personnageRepository->findAllPNJsExceptOne($personnage->getId());
        shuffle($autresPersonnages);

        $xp_total = 0;
        $participations = $personnage->getParticipations();

        foreach ($participations as $une_participation) {
           $xp_total = $xp_total + $une_participation->getXpEffectif();
        }

        $xp_progression = $xp_total;

        $fiche = $personnage->getFichePersonnage();
        $xp_creation = ($fiche !== null) ? $fiche->getCreationExp() : 0;
        $xp_total += $xp_creation;

        $rang = 1;

        if ($xp_total >= 360)
            $rang = 5;
        elseif ($xp_total >= 240)
            $rang = 4;
        elseif ($xp_total >= 140)
            $rang = 3;
        elseif ($xp_total >= 60)
            $rang = 2;

        return $this->render('personnages/profil.html.twig', [
            'personnage' => $personnage,
            'nom' => $personnage->getNom() . ' ' . $personnage->getPrenom(),
            'entity' => 'personnage',
            'category' => 'personnages',
            'un_element' => $personnage,
            'xp' => $xp_total,
            'xp_creation' => $xp_creation,
            'xp_progression' => $xp_progression,
            'rang' => $rang,
            'autresPersonnages' => $autresPersonnages,
            'historique' => $classeur->pourPersonnage($personnage),
            'saison_courante' => $saisonRepository->findCourante()?->getNumero(),
        ]);
    }

    /**
     * @Route("/personnages/fiche/{id}", name="personnage_fiche")
     */
    public function afficherFichePersonnage(FichePersonnage $fiche, CompetenceRepository $competenceRepository, AvantageRepository $avantageRepository, ObjetRepository $objetRepository, SortRepository $sortRepository): Response
    {
        $utilisateur = $this->getUser();

        $estLeJoueur = $utilisateur !== null
            && $fiche->getPersonnage()->getJoueur() !== null
            && $fiche->getPersonnage()->getJoueur()->getId() == $utilisateur->getId();

        if (!$estLeJoueur && ($response = $this->lockedPage($fiche->getPersonnage(), 'personnage', 'personnages'))) {
            return $response;
        }

        $xp_progression = 0;
        foreach ($fiche->getPersonnage()->getParticipations() as $participation) {
            $xp_progression += $participation->getXpEffectif();
        }

        $xp_creation = $fiche->getCreationExp() ?? 0;
        $xp_total = $xp_progression + $xp_creation;

        $rang = 1;
        if ($xp_total >= 360)      $rang = 5;
        elseif ($xp_total >= 240)  $rang = 4;
        elseif ($xp_total >= 140)  $rang = 3;
        elseif ($xp_total >= 60)   $rang = 2;

        // Compétences + Avantages pour les dropdowns d'édition in-place (joueur ou MJ)
        $competences = [];
        $avantagesJson = [];
        $desavantagesJson = [];
        $armesJson = [];
        $spellsJson = [];
        if ($estLeJoueur || $this->isGranted('ROLE_MJ')) {
            $allComps = $competenceRepository->findBy([], ['nom' => 'ASC']);
            foreach ($allComps as $c) {
                $competences[] = [
                    'id' => $c->getId(),
                    'nom' => $c->getNom(),
                    'trait' => $c->getTrait(),
                    'categorie' => $c->getCategorie(),
                    'specialisations' => [
                        $c->getSpecialisation1(),
                        $c->getSpecialisation2(),
                        $c->getSpecialisation3(),
                        $c->getSpecialisation4(),
                        $c->getSpecialisation5(),
                        $c->getSpecialisation6(),
                    ],
                ];
            }

            $serialize = function ($a) {
                return [
                    'id'                => $a->getId(),
                    'nom'               => $a->getNom(),
                    'description'       => $a->getDescription(),
                    'cout'              => $a->getCout(),
                    'discount'          => $a->getDiscount(),
                    'discountClanId'    => $a->getDiscountClan()   ? $a->getDiscountClan()->getId()   : null,
                    'discountClan2Id'   => $a->getDiscountClan2()  ? $a->getDiscountClan2()->getId()  : null,
                    'discountClasseId'  => $a->getDiscountClasse() ? $a->getDiscountClasse()->getId() : null,
                    'exclusiveId'       => $a->getExclusive()      ? $a->getExclusive()->getId()      : null,
                ];
            };
            foreach ($avantageRepository->findBy(['genre' => 'Avantage'], ['nom' => 'ASC']) as $a) {
                $avantagesJson[] = $serialize($a);
            }
            foreach ($avantageRepository->findBy(['genre' => 'Désavantage'], ['nom' => 'ASC']) as $a) {
                $desavantagesJson[] = $serialize($a);
            }

            $seesLocked = $this->isGranted('ROLE_MJ');
            // Une arme verrouillée déjà équipée reste servie, sinon le JS perd son VD.
            $equipped = array_filter([
                $fiche->getArme()?->getId(),
                $fiche->getArme2()?->getId(),
                $fiche->getArmeActuelle()?->getId(),
            ]);

            foreach ($objetRepository->findBy(['categorie' => 'ARME'], ['nom' => 'ASC']) as $o) {
                if ($o->getLocked() && !$seesLocked && !in_array($o->getId(), $equipped, true)) {
                    continue;
                }

                $armesJson[] = [
                    'id'   => $o->getId(),
                    'nom'  => $o->getNom(),
                    'vd'   => $o->getVd(),
                    'type' => $o->getType(),
                ];
            }

            foreach ($sortRepository->findBy(['categorie' => 'MAGIE'], ['niveau' => 'ASC', 'nom' => 'ASC']) as $s) {
                $spellsJson[] = [
                    'id'     => $s->getId(),
                    'nom'    => $s->getNom(),
                    'niveau' => $s->getNiveau(),
                    'anneau' => $s->getAnneau(),
                ];
            }

            $mainsNues = $objetRepository->findOneBy(['nom' => 'Mains Nues / Corps']);
            $mainsNuesId = $mainsNues ? $mainsNues->getId() : null;
        } else {
            $mainsNuesId = null;
        }

        return $this->render('personnages/character-sheet.html.twig', [
            'fiche'          => $fiche,
            'xp_total'       => $xp_total,
            'xp_creation'    => $xp_creation,
            'xp_progression' => $xp_progression,
            'rang'           => $rang,
            'est_le_joueur'  => $estLeJoueur,
            'competences_json' => $competences,
            'avantages_json'    => $avantagesJson,
            'desavantages_json' => $desavantagesJson,
            'armes_json'        => $armesJson,
            'spells_json'       => $spellsJson,
            'mains_nues_id'     => $mainsNuesId,
            'category' => 'personnages',
            'entity' => 'fiche',
            'un_element' => $fiche,
            'nom' => $fiche->getPersonnage()->getNom() . ' ' . $fiche->getPersonnage()->getPrenom(),
        ]);
    }

    /**
     * @Route("/personnages/fiche/{id}/edit", name="personnage_fiche_edit", methods={"POST"})
     */
    public function editerFichePersonnage(Request $request, FichePersonnage $fiche, EntityManagerInterface $em): Response
    {
        $utilisateur = $this->getUser();

        $estLeJoueur = $utilisateur !== null
            && $fiche->getPersonnage()->getJoueur() !== null
            && $fiche->getPersonnage()->getJoueur()->getId() == $utilisateur->getId();

        if (!$estLeJoueur && !$this->isGranted('ROLE_MJ')) {
            throw $this->createAccessDeniedException("Vous ne pouvez éditer que la fiche de votre propre personnage.");
        }

        $form = $this->createForm(JoueurFichePersonnageType::class, $fiche, [
            'sees_locked' => $this->isGranted('ROLE_MJ'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Votre fiche a bien été modifiée.');
        } else {
            $this->addFlash('danger', "La fiche n'a pas pu être modifiée : données invalides.");
        }

        return $this->redirectToRoute('personnage_fiche', ['id' => $fiche->getId()]);
    }
}