<?php

namespace App\Controller;

use App\Entity\FichePersonnage;
use App\Entity\Personnage;
use App\Form\JoueurFichePersonnageType;
use App\Form\JoueurPersonnageType;
use App\Repository\AvantageRepository;
use App\Repository\ChapitreRepository;
use App\Repository\CompetenceRepository;
use App\Repository\DevelopmentRepository;
use App\Repository\EpisodeRepository;
use App\Repository\ObjetRepository;
use App\Repository\PersonnageRepository;
use App\Repository\SaisonRepository;
use App\Repository\SortRepository;
use App\Service\Baliseur;
use App\Service\ClasseurHistorique;
use App\Service\ClasseurXP;
use App\Service\SheetExport;
use App\Service\Visibility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonnagesController extends AbstractController
{
    use VisibilityTrait;

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

        if ($chapitreId !== null && $saisonId !== null) {
            $chapitre = $chapitreRepository->find($chapitreId);
            if ($chapitre === null || $chapitre->getSaisonParent()->getId() !== $saisonId) {
                $chapitreId = null;
                $episodeId  = null;
            }
        }

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
    public function viewPersonnageProfil(Personnage $personnage, PersonnageRepository $personnageRepository, ClasseurHistorique $classeur, SaisonRepository $saisonRepository, Visibility $visibility, DevelopmentRepository $developmentRepository, Baliseur $baliseur, ClasseurXP $classeurXP): Response {

        if ($response = $this->accessGuard($visibility, $personnage, 'personnage', 'personnages')) {
            return $response;
        }

        $autresPersonnages = $personnage->getEstPj()
            ? $personnageRepository->findAllPJsExceptOne($personnage->getId())
            : $personnageRepository->findAllPNJsExceptOne($personnage->getId());
        shuffle($autresPersonnages);

        $fiche = $personnage->getFichePersonnage();
        $xp_creation = ($fiche !== null) ? (int) $fiche->getCreationExp() : 0;
        $xp_total = $classeurXP->total($personnage);
        $xp_progression = $xp_total - $xp_creation;
        $rang = $classeurXP->rank($xp_total);

        $estLeJoueur = $this->estLeJoueur($personnage);
        $estMj = $this->isGranted('ROLE_MJ');
        $peutEditer = $estLeJoueur || $estMj;

        $form = null;
        if ($peutEditer) {
            $form = $this->createForm(JoueurPersonnageType::class, $personnage, ['is_gm' => $estMj]);
            $form->get('description')->setData($baliseur->debaliser($personnage->getDescription()));
        }

        return $this->render('personnages/character-profil.html.twig', [
            'personnage' => $personnage,
            'est_le_joueur' => $estLeJoueur,
            'peut_editer' => $peutEditer,
            'form' => $form?->createView(),
            'developments' => $developmentRepository->findByPersonnage($personnage),
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
    public function afficherFichePersonnage(FichePersonnage $fiche, CompetenceRepository $competenceRepository, AvantageRepository $avantageRepository, ObjetRepository $objetRepository, SortRepository $sortRepository, Visibility $visibility, ClasseurXP $classeurXP): Response
    {
        $estLeJoueur = $this->estLeJoueur($fiche->getPersonnage());

        if (!$estLeJoueur && !$this->isGranted('ROLE_MJ')) {
            return $this->render('element-hidden.html.twig', [], new Response('', Response::HTTP_NOT_FOUND));
        }

        $xp_creation = (int) ($fiche->getCreationExp() ?? 0);
        $xp_total = $classeurXP->total($fiche->getPersonnage());
        $xp_progression = $xp_total - $xp_creation;
        $rang = $classeurXP->rank($xp_total);

        $competences = [];
        $avantagesJson = [];
        $desavantagesJson = [];
        $armesJson = [];
        $spellsJson = [];
        $objetsJson = [];
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
                    'summary'           => $a->getSummary(),
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

            $equipped = array_filter([
                $fiche->getArme()?->getId(),
                $fiche->getArme2()?->getId(),
                $fiche->getArmeActuelle()?->getId(),
            ]);

            foreach ($objetRepository->findBy(['categorie' => 'ARME'], ['nom' => 'ASC']) as $o) {
                if (!$visibility->isReadable($o) && !in_array($o->getId(), $equipped, true)) {
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

            foreach ($sortRepository->findBy(['categorie' => 'KIHO'], ['niveau' => 'ASC', 'nom' => 'ASC']) as $s) {
                $spellsJson[] = [
                    'id'     => $s->getId(),
                    'nom'    => $s->getNom(),
                    'niveau' => $s->getNiveau(),
                    'anneau' => 'KIHO_' . $s->getAnneau(),
                ];
            }

            if ($fiche->getPersonnage()->getEcole()?->hasTattoos()) {
                foreach ($sortRepository->findBy(['categorie' => 'TATOUAGE'], ['nom' => 'ASC']) as $s) {
                    $spellsJson[] = [
                        'id'     => $s->getId(),
                        'nom'    => $s->getNom(),
                        'niveau' => null,
                        'anneau' => 'TATOUAGE',
                    ];
                }
            }

            $owned = array_map(fn ($o) => $o->getId(), $fiche->getInventoryItems()->toArray());

            foreach ($objetRepository->findBy([], ['nom' => 'ASC']) as $o) {
                if (!$visibility->isReadable($o) && !in_array($o->getId(), $owned, true)) {
                    continue;
                }

                $objetsJson[] = [
                    'id' => $o->getId(),
                    'nom' => $o->getNom(),
                    'categorie' => $o->getCategorie(),
                    'taille' => $o->getTaille(),
                    'poids' => $o->getPoids(),
                    'vd' => $o->getVd(),
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
            'objets_json'       => $objetsJson,
            'mains_nues_id'     => $mainsNuesId,
            'category' => 'personnages',
            'entity' => 'fiche',
            'un_element' => $fiche,
            'nom' => $fiche->getPersonnage()->getNom() . ' ' . $fiche->getPersonnage()->getPrenom(),
        ]);
    }

    /**
     * @Route("/personnages/fiche/{id}/pdf", name="personnage_fiche_pdf")
     */
    public function exporterFichePersonnage(FichePersonnage $fiche, SheetExport $sheetExport): Response
    {
        if (!$this->estLeJoueur($fiche->getPersonnage()) && !$this->isGranted('ROLE_MJ')) {
            return $this->render('element-hidden.html.twig', [], new Response('', Response::HTTP_NOT_FOUND));
        }

        $response = new Response($sheetExport->build($fiche));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $sheetExport->filename($fiche)
        ));

        return $response;
    }

    /**
     * @Route("/personnages/fiche/{id}/edit", name="personnage_fiche_edit", methods={"POST"})
     */
    public function editerFichePersonnage(Request $request, FichePersonnage $fiche, EntityManagerInterface $em, Visibility $visibility): Response
    {
        $estLeJoueur = $this->estLeJoueur($fiche->getPersonnage());

        if (!$estLeJoueur && !$this->isGranted('ROLE_MJ')) {
            throw $this->createAccessDeniedException("Vous ne pouvez éditer que la fiche de votre propre personnage.");
        }

        $form = $this->createForm(JoueurFichePersonnageType::class, $fiche, [
            'sees_locked' => $this->isGranted('ROLE_MJ'),
            'unlocked' => $visibility->unlockedIds('objet'),
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

    /**
     * @Route("/personnages/profil/{id}/edit", name="personnage_profil_edit", methods={"POST"})
     */
    public function editerProfilPersonnage(Request $request, Personnage $personnage, EntityManagerInterface $em, Baliseur $baliseur): Response
    {
        $estMj = $this->isGranted('ROLE_MJ');

        if (!$this->estLeJoueur($personnage) && !$estMj) {
            throw $this->createAccessDeniedException("Vous ne pouvez éditer que le profil de votre propre personnage.");
        }

        $form = $this->createForm(JoueurPersonnageType::class, $personnage, ['is_gm' => $estMj]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($estMj) {
                $personnage->setDescription($baliseur->baliser($personnage->getDescription()));
            } else {
                $personnage->setDescription($this->sansHtml($personnage->getDescription()));
                $personnage->setPlayerNotes($this->sansHtml($personnage->getPlayerNotes()));
            }

            $em->flush();
            $this->addFlash('success', 'Le profil a bien été modifié.');
        } else {
            $this->addFlash('danger', "Le profil n'a pas pu être modifié : données invalides.");
        }

        return $this->redirectToRoute('personnage_profil', ['id' => $personnage->getId()]);
    }

    private function sansHtml(?string $texte): ?string
    {
        return $texte === null ? null : strip_tags($texte);
    }

    private function estLeJoueur(Personnage $personnage): bool
    {
        $utilisateur = $this->getUser();

        return $utilisateur !== null
            && $personnage->getJoueur() !== null
            && $personnage->getJoueur()->getId() == $utilisateur->getId();
    }
}