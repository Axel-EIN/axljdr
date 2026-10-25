<?php

namespace App\Controller;

use App\Entity\FichePersonnage;
use App\Entity\Personnage;
use App\Form\PlayerCharacterType;
use App\Form\PlayerSheetType;
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
use App\Service\Localisateur;
use App\Service\ClasseurXP;
use App\Service\SheetExport;
use App\Service\Visibility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CharactersController extends AbstractController
{
    use VisibilityTrait;

    /**
     * @Route("/characters", name="characters")
     */
    public function index(
        Request $request,
        PersonnageRepository $characterRepository,
        SaisonRepository $seasonRepository,
        ChapitreRepository $chapterRepository,
        EpisodeRepository $episodeRepository
    ): Response {
        $seasonId  = null;
        $chapterId = null;
        $episodeId = null;

        if ($request->query->has('season')) {
            $rawSeason = $request->query->get('season');
            $seasonId = $rawSeason !== '' && $rawSeason !== null ? (int) $rawSeason : null;

            if ($request->query->has('chapter')) {
                $rawChapter = $request->query->get('chapter');
                $chapterId = $rawChapter !== '' && $rawChapter !== null ? (int) $rawChapter : null;

                if ($request->query->has('episode')) {
                    $rawEpisode = $request->query->get('episode');
                    $episodeId = $rawEpisode !== '' && $rawEpisode !== null ? (int) $rawEpisode : null;
                }
            }
        }

        if ($chapterId !== null && $seasonId !== null) {
            $chapter = $chapterRepository->find($chapterId);
            if ($chapter === null || $chapter->getSaisonParent()->getId() !== $seasonId) {
                $chapterId = null;
                $episodeId = null;
            }
        }

        if ($episodeId !== null && $chapterId !== null) {
            $episode = $episodeRepository->find($episodeId);
            if ($episode === null || $episode->getChapitreParent()->getId() !== $chapterId) {
                $episodeId = null;
            }
        }

        $pcs  = $characterRepository->findAllPJsSorted($seasonId, $chapterId, $episodeId);
        $npcs = $characterRepository->findAllPNJsSorted($seasonId, $chapterId, $episodeId);

        $seasons  = $seasonRepository->findBy([], ['numero' => 'ASC']);
        $chapters = $seasonId !== null
            ? $chapterRepository->findBy(['saisonParent' => $seasonId], ['numero' => 'ASC'])
            : [];
        $episodes = $chapterId !== null
            ? $episodeRepository->findBy(['chapitreParent' => $chapterId], ['numero' => 'ASC'])
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
        $category = 'characters';

        return $this->render('characters/index.html.twig', [
            'pcs' => $pcs,
            'npcs' => $npcs,
            'sections' => $sections,
            'header_classname' => $header_classname,
            'header_up' => $header_up,
            'header_down' => $header_down,
            'category' => $category,
            'seasons' => $seasons,
            'chapters' => $chapters,
            'episodes' => $episodes,
            'selected_season_id'  => $seasonId,
            'selected_chapter_id' => $chapterId,
            'selected_episode_id' => $episodeId,
        ]);
    }

    /**
     * @Route("/characters/profile/{id}", name="characters_profile")
     */
    public function showProfile(Personnage $character, PersonnageRepository $characterRepository, ClasseurHistorique $classeur, SaisonRepository $seasonRepository, Visibility $visibility, DevelopmentRepository $developmentRepository, Baliseur $baliseur, ClasseurXP $classeurXP, Localisateur $localisateur): Response {

        if ($response = $this->accessGuard($visibility, $character, 'personnage', 'characters')) {
            return $response;
        }

        $otherCharacters = $character->getEstPj()
            ? $characterRepository->findAllPJsExceptOne($character->getId())
            : $characterRepository->findAllPNJsExceptOne($character->getId());
        shuffle($otherCharacters);

        $sheet = $character->getFichePersonnage();
        $creationXp = ($sheet !== null) ? $classeurXP->creation($sheet) : 0;
        $totalXp = $classeurXP->total($character);
        $rank = $classeurXP->rank($totalXp);

        $isOwnCharacter = $this->isOwnCharacter($character);
        $isGm = $this->isGranted('ROLE_MJ');
        $canEdit = $isOwnCharacter || $isGm;

        $form = null;
        if ($canEdit) {
            $form = $this->createForm(PlayerCharacterType::class, $character, ['is_gm' => $isGm]);
            $form->get('description')->setData($baliseur->debaliser($character->getDescription()));
        }

        return $this->render('characters/character-profile.html.twig', [
            'personnage' => $character,
            'is_own_character' => $isOwnCharacter,
            'can_edit' => $canEdit,
            'form' => $form?->createView(),
            'developments' => $developmentRepository->findByPersonnage($character),
            'nom' => $character->getNomComplet(),
            'entity' => 'personnage',
            'category' => 'characters',
            'un_element' => $character,
            'xp' => $totalXp,
            'creation_xp' => $creationXp,
            'rank' => $rank,
            'other_characters' => $otherCharacters,
            'historique' => $classeur->pourPersonnage($character),
            'saison_courante' => $seasonRepository->findCourante()?->getNumero(),
        ] + $localisateur->carte($character));
    }

    /**
     * @Route("/characters/sheet/{id}", name="characters_sheet")
     */
    public function showSheet(FichePersonnage $sheet, CompetenceRepository $skillRepository, AvantageRepository $advantageRepository, ObjetRepository $objectRepository, SortRepository $spellRepository, Visibility $visibility, ClasseurXP $classeurXP): Response
    {
        $isOwnCharacter = $this->isOwnCharacter($sheet->getPersonnage());

        if (!$isOwnCharacter && !$this->isGranted('ROLE_MJ')) {
            return $this->render('element-hidden.html.twig', [], new Response('', Response::HTTP_NOT_FOUND));
        }

        $xp = $classeurXP->sheet($sheet);

        $skillsJson = [];
        $advantagesJson = [];
        $disadvantagesJson = [];
        $weaponsJson = [];
        $spellsJson = [];
        $objectsJson = [];
        foreach ($skillRepository->findBy([], ['nom' => 'ASC']) as $skill) {
            $skillsJson[] = [
                'id' => $skill->getId(),
                'nom' => $skill->getNom(),
                'traitCourt' => $skill->getTraitCourt(),
                'categorie' => $skill->getCategorie(),
                'specialisations' => [
                    $skill->getSpecialisation1(),
                    $skill->getSpecialisation2(),
                    $skill->getSpecialisation3(),
                    $skill->getSpecialisation4(),
                    $skill->getSpecialisation5(),
                    $skill->getSpecialisation6(),
                ],
            ];
        }

        $serialize = function ($advantage) use ($classeurXP, $sheet) {
            return [
                'id'                => $advantage->getId(),
                'nom'               => $advantage->getNom(),
                'description'       => $advantage->getDescription(),
                'summary'           => $advantage->getSummary(),
                'cout'              => $advantage->getCout(),
                'coutEffectif'      => $classeurXP->advantageCost($sheet, $advantage),
                'exclusiveId'       => $advantage->getExclusive()      ? $advantage->getExclusive()->getId()      : null,
            ];
        };
        foreach ($advantageRepository->findBy(['genre' => 'Avantage'], ['nom' => 'ASC']) as $advantage) {
            $advantagesJson[] = $serialize($advantage);
        }
        foreach ($advantageRepository->findBy(['genre' => 'Désavantage'], ['nom' => 'ASC']) as $advantage) {
            $disadvantagesJson[] = $serialize($advantage);
        }

        $equipped = array_filter([$sheet->getArmeActuelle()?->getId()]);

        foreach ($objectRepository->findBy(['categorie' => 'ARME'], ['nom' => 'ASC']) as $object) {
            if (!$visibility->isReadable($object) && !in_array($object->getId(), $equipped, true)) {
                continue;
            }

            $weaponsJson[] = [
                'id'   => $object->getId(),
                'nom'  => $object->getNom(),
                'vd'   => $object->getVd(),
                'type' => $object->getType(),
            ];
        }

        foreach ($spellRepository->findBy(['categorie' => 'MAGIE'], ['niveau' => 'ASC', 'nom' => 'ASC']) as $spell) {
            $spellsJson[] = [
                'id'     => $spell->getId(),
                'nom'    => $spell->getNom(),
                'niveau' => $spell->getNiveau(),
                'anneau' => $spell->getAnneau(),
            ];
        }

        foreach ($spellRepository->findBy(['categorie' => 'KIHO'], ['niveau' => 'ASC', 'nom' => 'ASC']) as $spell) {
            $spellsJson[] = [
                'id'     => $spell->getId(),
                'nom'    => $spell->getNom(),
                'niveau' => $spell->getNiveau(),
                'anneau' => 'KIHO_' . $spell->getAnneau(),
            ];
        }

        if ($sheet->getPersonnage()->getEcole()?->hasTattoos()) {
            foreach ($spellRepository->findBy(['categorie' => 'TATOUAGE'], ['nom' => 'ASC']) as $spell) {
                $spellsJson[] = [
                    'id'     => $spell->getId(),
                    'nom'    => $spell->getNom(),
                    'niveau' => null,
                    'anneau' => 'TATOUAGE',
                ];
            }
        }

        $owned = array_map(fn ($object) => $object->getId(), $sheet->getInventoryItems()->toArray());

        foreach ($objectRepository->findBy([], ['nom' => 'ASC']) as $object) {
            if (!$visibility->isReadable($object) && !in_array($object->getId(), $owned, true)) {
                continue;
            }

            $objectsJson[] = [
                'id' => $object->getId(),
                'nom' => $object->getNom(),
                'categorie' => $object->getCategorie(),
                'type' => $object->getType(),
                'taille' => $object->getTaille(),
                'poids' => $object->getPoids(),
                'vdOuForce' => $object->getVdOuForce(),
            ];
        }

        $bareHands = $objectRepository->findOneBy(['nom' => 'Mains Nues / Corps']);
        $bareHandsId = $bareHands ? $bareHands->getId() : null;

        return $this->render('characters/character-sheet.html.twig', [
            'fiche'              => $sheet,
            'xp'                 => $xp,
            'rank'               => $classeurXP->rank($xp['total']),
            'is_own_character'   => $isOwnCharacter,
            'skills_json'        => $skillsJson,
            'advantages_json'    => $advantagesJson,
            'disadvantages_json' => $disadvantagesJson,
            'weapons_json'       => $weaponsJson,
            'spells_json'        => $spellsJson,
            'objects_json'       => $objectsJson,
            'bare_hands_id'      => $bareHandsId,
            'category' => 'characters',
            'entity' => 'sheet',
            'un_element' => $sheet,
            'nom' => $sheet->getPersonnage()->getNomComplet(),
        ]);
    }

    /**
     * @Route("/characters/sheet/{id}/pdf", name="characters_sheet_pdf")
     */
    public function exportSheet(FichePersonnage $sheet, SheetExport $sheetExport): Response
    {
        if (!$this->isOwnCharacter($sheet->getPersonnage()) && !$this->isGranted('ROLE_MJ')) {
            return $this->render('element-hidden.html.twig', [], new Response('', Response::HTTP_NOT_FOUND));
        }

        $response = new Response($sheetExport->build($sheet));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $sheetExport->filename($sheet)
        ));

        return $response;
    }

    /**
     * @Route("/characters/sheet/{id}/edit", name="characters_sheet_edit", methods={"POST"})
     */
    public function editSheet(Request $request, FichePersonnage $sheet, EntityManagerInterface $em, Visibility $visibility): Response
    {
        $isOwnCharacter = $this->isOwnCharacter($sheet->getPersonnage());

        if (!$isOwnCharacter && !$this->isGranted('ROLE_MJ')) {
            throw $this->createAccessDeniedException("Vous ne pouvez éditer que la fiche de votre propre personnage.");
        }

        $form = $this->createForm(PlayerSheetType::class, $sheet, [
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

        return $this->redirectToRoute('characters_sheet', ['id' => $sheet->getId()]);
    }

    /**
     * @Route("/characters/profile/{id}/edit", name="characters_profile_edit", methods={"POST"})
     */
    public function editProfile(Request $request, Personnage $character, EntityManagerInterface $em, Baliseur $baliseur): Response
    {
        $isGm = $this->isGranted('ROLE_MJ');

        if (!$this->isOwnCharacter($character) && !$isGm) {
            throw $this->createAccessDeniedException("Vous ne pouvez éditer que le profil de votre propre personnage.");
        }

        $previousDescription = $character->getDescription();
        $form = $this->createForm(PlayerCharacterType::class, $character, ['is_gm' => $isGm]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($isGm) {
                $character->setDescription($baliseur->baliser($character->getDescription()));
            } else {
                $character->setDescription($baliseur->rebaliser($this->withoutHtml($character->getDescription()), $previousDescription));
                $character->setPlayerNotes($this->withoutHtml($character->getPlayerNotes()));
            }

            $em->flush();
            $this->addFlash('success', 'Le profil a bien été modifié.');
        } else {
            $this->addFlash('danger', "Le profil n'a pas pu être modifié : données invalides.");
        }

        return $this->redirectToRoute('characters_profile', ['id' => $character->getId()]);
    }

    private function withoutHtml(?string $text): ?string
    {
        return $text === null ? null : strip_tags($text);
    }

    private function isOwnCharacter(Personnage $character): bool
    {
        $user = $this->getUser();

        return $user !== null
            && $character->getJoueur() !== null
            && $character->getJoueur()->getId() == $user->getId();
    }
}
