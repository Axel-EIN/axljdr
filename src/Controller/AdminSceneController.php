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
            'genre' => 'F',
            'determinant' => 'une',
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
                            ParticipationHandler $participationHandler, PersonnageRepository $personnageRepository,
                            EpisodeRepository $episodeRepository, Orderer $orderer, SceneRepository $sceneRepository, Unlocker $unlocker, TableFilter $tableFilter, Locator $locator) {

        $scene = new Scene;
        $tableFilter->prefill($scene, self::TABLE_FILTERS, $request->query->all('filter'));

        $tout_pjs  = $personnageRepository->findAllPJsSorted();
        $tout_pnjs = $personnageRepository->findAllPNJsSorted();

        if ( !empty($request->query->get('numero')) && $request->query->get('numero') > 0
        && !empty($request->query->get('episodeID')) && $request->query->get('episodeID') > 0 )
        {
            $scene->setNumero($request->query->get('numero'));
            $episodeParent = $episodeRepository->find($request->query->get('episodeID'));
            if ($episodeParent !== null)
                $scene->setEpisodeParent($episodeParent);
        }

        $form = $this->createForm(AdminSceneType::class, $scene);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = $this->prefixeImage($scene);
                $scene->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'scenes', 'landscape720'));
            }

            $participants_a_ajoutes = $participationHandler->fusionnerParticipants($request->get('participants'), $request->get('participants_xp'),
                                                                                    $request->get('participants_mort'), $request->get('participants_pnjs'),
                                                                                    $request->get('participants_pnjs_mort'), $request->get('participants_xpBonus'));

            $participationHandler->ajouterParticipations($participants_a_ajoutes, $scene);

            $scene->setTexte($nameTagger->tag($scene->getTexte()));
            
            $em->persist($scene);
            $em->flush();

            $unlocker->syncScene($scene);
            $em->flush();

            $this->addFlash('success', 'La scène a bien été créée.');

            $fratrieArrivee = $sceneRepository->findBy(['episodeParent' => $scene->getEpisodeParent()->getId()]);
            $orderer->reorderNumber($scene->getId(), -1, $scene->getNumero(), [], $fratrieArrivee);

            $this->signalerPositionsEffacees($locator->clearFreePositions($scene));

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'episode')
                return $this->redirectToRoute('story_episode', ['id' => $scene->getEpisodeParent()->getId(),'_fragment' => 'scn' . $scene->getNumero()]);

            return $this->redirectToRoute('admin_scene');
        }

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'scene',
            'label' => 'Scène',
            'genre' => 'F',
            'determinant' => 'une',
            'tout_pjs' => $tout_pjs,
            'tout_pnjs' => $tout_pnjs,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/scene/{id}/edit", name="admin_scene_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editScene(Request $request, Scene $scene, FileHandler $fileHandler, Orderer $orderer, NameTagger $nameTagger,
                                ParticipationHandler $participationHandler, PersonnageRepository $personnageRepository,
                                ParticipationRepository $participationRepository, SceneRepository $sceneRepository, Unlocker $unlocker, Locator $locator): Response {

        $numeroDepart = $scene->getNumero();
        $fratrieDepartId = $scene->getEpisodeParent()->getId();
        $lieuDepart = $scene->getLieu();
        
        $tout_pjs  = $personnageRepository->findAllPJsSorted();
        $tout_pnjs = $personnageRepository->findAllPNJsSorted();
        $participations_pjs = $participationRepository->findBy(array('scene' => $scene, 'estPj' => true));
        $participations_pnjs = $participationRepository->findBy(array('scene' => $scene, 'estPj' => false));

        $scene->setTexte($nameTagger->untag($scene->getTexte()));

        $form = $this->createForm(AdminSceneType::class, $scene);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = $this->prefixeImage($scene);
                $scene->setImage($fileHandler->handle($nouvelleImage, $scene->getImage(), $prefix, 'scenes', 'landscape720'));
            } elseif ($request->request->get('remove_image') === '1' && $scene->getImage()) {
                $fileHandler->handle(null, $scene->getImage(), null, 'scenes');
                $scene->setImage(null);
            }

            $participants_modifies = $participationHandler->fusionnerParticipants($request->get('participants'), $request->get('participants_xp'),
                                                                                    $request->get('participants_mort'), $request->get('participants_pnjs'),
                                                                                    $request->get('participants_pnjs_mort'), $request->get('participants_xpBonus'));

            $toutes_participations = array_merge($participations_pjs, $participations_pnjs);

            foreach ($toutes_participations as $une_participation) {
                $trouvee = false;

                foreach ($participants_modifies as $un_participant_modifie) {
                    if ($une_participation->getPersonnage()->getId() == $un_participant_modifie['id']) {
                        $trouvee = true;
                        break;
                    }
                }

                if ($trouvee == true) {
                    $xpBonusModifie = !empty($un_participant_modifie['xpBonus']);
                    if ($une_participation->getXpGagne() != $un_participant_modifie['xp']
                        || $une_participation->getEstMort() != $un_participant_modifie['mort']
                        || $une_participation->getXpBonus() != $xpBonusModifie) {
                        $une_participation->setXpGagne($un_participant_modifie['xp']);
                        $une_participation->setEstMort($un_participant_modifie['mort']);
                        $une_participation->setXpBonus($xpBonusModifie);
                        $this->addFlash('warning', 'Le personnage ' . $une_participation->getPersonnage()->getPrenom() . ' a bien été modifié dans les participants !');
                    }
                }

                if ($trouvee == false) {
                    $this->addFlash('danger', 'Le personnage ' . $une_participation->getPersonnage()->getPrenom() . ' a bien été retiré des participants !');
                    $this->getDoctrine()->getManager()->remove($une_participation);
                }
            }

            $ajoutes = [];
            foreach ($participants_modifies as $un_participant_modifie) {
                $trouvee = false;

                foreach($toutes_participations as $une_participation) {
                    if($un_participant_modifie['id'] == $une_participation->getPersonnage()->getId()) {
                        $trouvee = true;
                        break;
                    }
                }

                if($trouvee == false) {
                    $personnage = $personnageRepository->find($un_participant_modifie['id']);
                    $nouvelle_participation = new Participation;
                    $nouvelle_participation->setScene($scene);
                    $nouvelle_participation->setPersonnage($personnage);
                    $nouvelle_participation->setXpGagne($un_participant_modifie['xp']);
                    $nouvelle_participation->setEstMort($un_participant_modifie['mort']);
                    $nouvelle_participation->setEstPj($un_participant_modifie['estPj']);
                    $nouvelle_participation->setXpBonus(!empty($un_participant_modifie['xpBonus']));
                    $this->getDoctrine()->getManager()->persist($nouvelle_participation);
                    $ajoutes[] = $personnage;
                    $this->addFlash('success', 'Le personnage ' . $nouvelle_participation->getPersonnage()->getPrenom() . ' a bien été ajouté en participant !');
                }
            }

            $scene->setTexte($nameTagger->tag($scene->getTexte()));

            if ($numeroDepart != $scene->getNumero() || $fratrieDepartId != $scene->getEpisodeParent()->getId())
            {
                $fratrieDepart = $sceneRepository->findBy(['episodeParent' => $fratrieDepartId]);
                $fratrieArrivee = $sceneRepository->findBy(['episodeParent' => $scene->getEpisodeParent()->getId()]);
                $orderer->reorderNumber($scene->getId(), $numeroDepart, $scene->getNumero(), $fratrieDepart, $fratrieArrivee);
            }

            $this->getDoctrine()->getManager()->flush();

            $unlocker->syncScene($scene);
            $this->getDoctrine()->getManager()->flush();

            $this->signalerPositionsEffacees($locator->clearFreePositions($scene, $lieuDepart === $scene->getLieu() ? $ajoutes : null));

            $this->addFlash('success', 'La scène a bien été modifiée.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'episode')
                return $this->redirectToRoute('story_episode', ['id' => $scene->getEpisodeParent()->getId(),'_fragment' => 'scn' . $scene->getNumero()]);
    
            return $this->redirectToRoute('admin_scene');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'scene' => $scene,
            'entity' => 'scene',
            'label' => 'Scène',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form,
            'participations_pjs' => $participations_pjs,
            'participations_pnjs' => $participations_pnjs,
            'tout_pjs' => $tout_pjs,
            'tout_pnjs' => $tout_pnjs,
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

            $fratrieDepartId = $scene->getEpisodeParent()->getId();
            $fratrieDepart = $sceneRepository->findBy(['episodeParent' => $fratrieDepartId]);
            $orderer->reorderNumber($scene->getId(), $scene->getNumero(), -1, $fratrieDepart, []);

            $entityManager->remove($scene);
            $entityManager->flush();
            $this->addFlash('success', 'La scène a bien été supprimée.');
        }

        if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'episode')
            return $this->redirectToRoute('story_episode', ['id' => $episodeParent->getId()]);

        return $this->redirectToRoute('admin_scene');
    }

    private function signalerPositionsEffacees(array $personnages): void
    {
        foreach ($personnages as $personnage) {
            $this->addFlash('warning', 'La position libre de ' . $personnage->getPrenom() . ' a été effacée : cette scène est sa dernière.');
        }
    }

    private function prefixeImage(Scene $scene): string
    {
        return 'scene-s' . $scene->getEpisodeParent()->getChapitreParent()->getSaisonParent()->getNumero()
            . '-ch' . $scene->getEpisodeParent()->getChapitreParent()->getNumero()
            . '-ep' . $scene->getEpisodeParent()->getNumero() . '-scn' . $scene->getNumero()
            . (empty($scene->getTitre()) ? '' : '-' . $scene->getTitre());
    }
}
