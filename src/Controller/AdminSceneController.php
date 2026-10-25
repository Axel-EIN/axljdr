<?php

namespace App\Controller;

use App\Service\TableFilter;
use App\Entity\Scene;
use App\Service\FileHandler;
use App\Service\Orderer;
use App\Service\NameTagger;
use App\Service\Locator;
use App\Service\ParticipationHandler;
use App\Service\Unlocker;
use App\Form\AdminSceneType;
use App\Entity\Participation;
use App\Repository\EpisodeRepository;
use App\Repository\ParticipationRepository;
use App\Repository\SceneRepository;
use App\Repository\PersonnageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminSceneController extends AbstractController
{
    private const TABLE_FILTERS = [
        'episodeParent.chapitreParent.titre:Chapitre',
        'episodeParent.titre:Épisode',
    ];

    /**
     * @Route("/admin/scene", name="admin_scene")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminScenes(SceneRepository $sceneRepository): Response
    {
        $scenes = $sceneRepository->findBy(array(), array('id' => 'DESC'));

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $scenes,
            'element' => 'scene',
            'label' => 'Scène',
            'labels' => 'Scènes',
            'gender' => 'F',
            'article' => 'une',
            'table_filters' => self::TABLE_FILTERS,
            'table_cols' => [
                'image:Image:image:NA_SCENE',
                'titre:Titre::bold',
                'episodeParent.titre:Épisode',
                'numero:Ordre:number',
                'lieu.nom:Lieu',
                'temps:Temps',
            ],
        ]);
    }

    /**
     * @Route("/admin/scene/create", name="admin_scene_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addScene(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, NameTagger $nameTagger,
                            ParticipationHandler $participationHandler, PersonnageRepository $characterRepository,
                            EpisodeRepository $episodeRepository, Orderer $orderer, SceneRepository $sceneRepository, Unlocker $unlocker, TableFilter $tableFilter, Locator $locator) {

        $scene = new Scene;
        $tableFilter->prefill($scene, self::TABLE_FILTERS, $request->query->all('filter'));

        $allPcs  = $characterRepository->findAllPJsSorted();
        $allNpcs = $characterRepository->findAllPNJsSorted();

        if ( !empty($request->query->get('number')) && $request->query->get('number') > 0
        && !empty($request->query->get('episodeID')) && $request->query->get('episodeID') > 0 )
        {
            $scene->setNumero($request->query->get('number'));
            $episodeParent = $episodeRepository->find($request->query->get('episodeID'));
            if ($episodeParent !== null)
                $scene->setEpisodeParent($episodeParent);
        }

        $form = $this->createForm(AdminSceneType::class, $scene);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = $this->imagePrefix($scene);
                $scene->setImage($fileHandler->handle($newImage, null, $prefix, 'scenes', 'landscape720'));
            }

            $participantsToAdd = $participationHandler->mergeParticipants($request->get('participants'), $request->get('participants_xp'),
                                                                                    $request->get('participants_dead'), $request->get('npc_participants'),
                                                                                    $request->get('npc_participants_dead'), $request->get('participants_xpBonus'));

            $participationHandler->addParticipations($participantsToAdd, $scene);

            $scene->setTexte($nameTagger->tag($scene->getTexte()));
            
            $em->persist($scene);
            $em->flush();

            $unlocker->syncScene($scene);
            $em->flush();

            $this->addFlash('success', 'La scène a bien été créée.');

            $newSiblings = $sceneRepository->findBy(['episodeParent' => $scene->getEpisodeParent()->getId()]);
            $orderer->reorderNumber($scene->getId(), -1, $scene->getNumero(), [], $newSiblings);

            $this->flashClearedPositions($locator->clearFreePositions($scene));

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'episode')
                return $this->redirectToRoute('story_episode', ['id' => $scene->getEpisodeParent()->getId(),'_fragment' => 'scn' . $scene->getNumero()]);

            return $this->redirectToRoute('admin_scene');
        }

        return $this->render('back_office/create.html.twig', [
            'type' => 'create',
            'entity' => 'scene',
            'label' => 'Scène',
            'gender' => 'F',
            'article' => 'une',
            'all_pcs' => $allPcs,
            'all_npcs' => $allNpcs,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/scene/{id}/edit", name="admin_scene_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editScene(Request $request, Scene $scene, FileHandler $fileHandler, Orderer $orderer, NameTagger $nameTagger,
                                ParticipationHandler $participationHandler, PersonnageRepository $characterRepository,
                                ParticipationRepository $participationRepository, SceneRepository $sceneRepository, Unlocker $unlocker, Locator $locator): Response {

        $previousNumber = $scene->getNumero();
        $previousSiblingsId = $scene->getEpisodeParent()->getId();
        $previousLocation = $scene->getLieu();
        
        $allPcs  = $characterRepository->findAllPJsSorted();
        $allNpcs = $characterRepository->findAllPNJsSorted();
        $pcParticipations = $participationRepository->findBy(array('scene' => $scene, 'estPj' => true));
        $npcParticipations = $participationRepository->findBy(array('scene' => $scene, 'estPj' => false));

        $scene->setTexte($nameTagger->untag($scene->getTexte()));

        $form = $this->createForm(AdminSceneType::class, $scene);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = $this->imagePrefix($scene);
                $scene->setImage($fileHandler->handle($newImage, $scene->getImage(), $prefix, 'scenes', 'landscape720'));
            } elseif ($request->request->get('remove_image') === '1' && $scene->getImage()) {
                $fileHandler->handle(null, $scene->getImage(), null, 'scenes');
                $scene->setImage(null);
            }

            $updatedParticipants = $participationHandler->mergeParticipants($request->get('participants'), $request->get('participants_xp'),
                                                                                    $request->get('participants_dead'), $request->get('npc_participants'),
                                                                                    $request->get('npc_participants_dead'), $request->get('participants_xpBonus'));

            $allParticipations = array_merge($pcParticipations, $npcParticipations);

            foreach ($allParticipations as $participation) {
                $found = false;

                foreach ($updatedParticipants as $updatedParticipant) {
                    if ($participation->getPersonnage()->getId() == $updatedParticipant['id']) {
                        $found = true;
                        break;
                    }
                }

                if ($found == true) {
                    $xpBonus = !empty($updatedParticipant['xpBonus']);
                    if ($participation->getXpGagne() != $updatedParticipant['xp']
                        || $participation->getEstMort() != $updatedParticipant['mort']
                        || $participation->getXpBonus() != $xpBonus) {
                        $participation->setXpGagne($updatedParticipant['xp']);
                        $participation->setEstMort($updatedParticipant['mort']);
                        $participation->setXpBonus($xpBonus);
                        $this->addFlash('warning', 'Le personnage ' . $participation->getPersonnage()->getPrenom() . ' a bien été modifié dans les participants !');
                    }
                }

                if ($found == false) {
                    $this->addFlash('danger', 'Le personnage ' . $participation->getPersonnage()->getPrenom() . ' a bien été retiré des participants !');
                    $this->getDoctrine()->getManager()->remove($participation);
                }
            }

            $added = [];
            foreach ($updatedParticipants as $updatedParticipant) {
                $found = false;

                foreach($allParticipations as $participation) {
                    if($updatedParticipant['id'] == $participation->getPersonnage()->getId()) {
                        $found = true;
                        break;
                    }
                }

                if($found == false) {
                    $character = $characterRepository->find($updatedParticipant['id']);
                    $newParticipation = new Participation;
                    $newParticipation->setScene($scene);
                    $newParticipation->setPersonnage($character);
                    $newParticipation->setXpGagne($updatedParticipant['xp']);
                    $newParticipation->setEstMort($updatedParticipant['mort']);
                    $newParticipation->setEstPj($updatedParticipant['estPj']);
                    $newParticipation->setXpBonus(!empty($updatedParticipant['xpBonus']));
                    $this->getDoctrine()->getManager()->persist($newParticipation);
                    $added[] = $character;
                    $this->addFlash('success', 'Le personnage ' . $newParticipation->getPersonnage()->getPrenom() . ' a bien été ajouté en participant !');
                }
            }

            $scene->setTexte($nameTagger->tag($scene->getTexte()));

            if ($previousNumber != $scene->getNumero() || $previousSiblingsId != $scene->getEpisodeParent()->getId())
            {
                $previousSiblings = $sceneRepository->findBy(['episodeParent' => $previousSiblingsId]);
                $newSiblings = $sceneRepository->findBy(['episodeParent' => $scene->getEpisodeParent()->getId()]);
                $orderer->reorderNumber($scene->getId(), $previousNumber, $scene->getNumero(), $previousSiblings, $newSiblings);
            }

            $this->getDoctrine()->getManager()->flush();

            $unlocker->syncScene($scene);
            $this->getDoctrine()->getManager()->flush();

            $this->flashClearedPositions($locator->clearFreePositions($scene, $previousLocation === $scene->getLieu() ? $added : null));

            $this->addFlash('success', 'La scène a bien été modifiée.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'episode')
                return $this->redirectToRoute('story_episode', ['id' => $scene->getEpisodeParent()->getId(),'_fragment' => 'scn' . $scene->getNumero()]);
    
            return $this->redirectToRoute('admin_scene');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'edit',
            'scene' => $scene,
            'entity' => 'scene',
            'label' => 'Scène',
            'gender' => 'F',
            'article' => 'une',
            'form' => $form,
            'pc_participations' => $pcParticipations,
            'npc_participations' => $npcParticipations,
            'all_pcs' => $allPcs,
            'all_npcs' => $allNpcs,
        ]);
    }

    /**
     * @Route("/admin/scene/{id}/delete", name="admin_scene_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteScene(Request $request, Scene $scene, Orderer $orderer, SceneRepository $sceneRepository, FileHandler $fileHandler): Response {
        
        $episodeParent = $scene->getEpisodeParent();

        if ($this->isCsrfTokenValid('delete' . $scene->getId(), $request->request->get('_csrf_token'))) {

            $entityManager = $this->getDoctrine()->getManager();

            $fileHandler->handle(null, $scene->getImage(), null, 'scenes');

            $previousSiblingsId = $scene->getEpisodeParent()->getId();
            $previousSiblings = $sceneRepository->findBy(['episodeParent' => $previousSiblingsId]);
            $orderer->reorderNumber($scene->getId(), $scene->getNumero(), -1, $previousSiblings, []);

            $entityManager->remove($scene);
            $entityManager->flush();
            $this->addFlash('success', 'La scène a bien été supprimée.');
        }

        if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'episode')
            return $this->redirectToRoute('story_episode', ['id' => $episodeParent->getId()]);

        return $this->redirectToRoute('admin_scene');
    }

    private function flashClearedPositions(array $characters): void
    {
        foreach ($characters as $character) {
            $this->addFlash('warning', 'La position libre de ' . $character->getPrenom() . ' a été effacée : cette scène est sa dernière.');
        }
    }

    private function imagePrefix(Scene $scene): string
    {
        return 'scene-s' . $scene->getEpisodeParent()->getChapitreParent()->getSaisonParent()->getNumero()
            . '-ch' . $scene->getEpisodeParent()->getChapitreParent()->getNumero()
            . '-ep' . $scene->getEpisodeParent()->getNumero() . '-scn' . $scene->getNumero()
            . (empty($scene->getTitre()) ? '' : '-' . $scene->getTitre());
    }
}
