<?php

namespace App\Controller;

use App\Entity\Scene;
use App\Service\FileHandler;
use App\Service\Numeroteur;
use App\Service\Baliseur;
use App\Service\ParticipationHandler;
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
    public function addScene(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, Baliseur $baliseur,
                            ParticipationHandler $participationHandler, PersonnageRepository $personnageRepository,
                            EpisodeRepository $episodeRepository, Numeroteur $numeroteur, SceneRepository $sceneRepository) {

        $scene = new Scene;

        // List of Character for Adding them as Participants in JS — triés par clan puis nom pour grouping en optgroup
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
                $prefix = 'scene-s' . $scene->getEpisodeParent()->getChapitreParent()->getSaisonParent()->getNumero()
                . '-ch' . $scene->getEpisodeParent()->getChapitreParent()->getNumero()
                . '-ep' . $scene->getEpisodeParent()->getNumero() . '-scn' . $scene->getNumero();
                $scene->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'scenes', 'landscape720'));
            }

            $participants_a_ajoutes = $participationHandler->fusionnerParticipants($request->get('participants'), $request->get('participants_xp'),
                                                                                    $request->get('participants_mort'), $request->get('participants_pnjs'),
                                                                                    $request->get('participants_pnjs_mort'), $request->get('participants_xpBonus'));

            // Create and Add Participations Entity from Participants
            $participationHandler->ajouterParticipations($participants_a_ajoutes, $scene);

            // CHARACTER TAGGER : capture words between [], check if character exists, replace with Link
            $scene->setTexte($baliseur->baliserPersonnages($scene->getTexte()));

            // LOCATION TAGGER : capture words between {}, check if location exists, replace with Link
            $scene->setTexte($baliseur->baliserLieux($scene->getTexte()));
            
            $em->persist($scene);
            $em->flush();
            $this->addFlash('success', 'La scène a bien été crée.');

            $fratrieArrivee = $sceneRepository->findBy(['episodeParent' => $scene->getEpisodeParent()->getId()]);
            $numeroteur->reordonnerNumero($scene->getId(), -1, $scene->getNumero(), [], $fratrieArrivee);

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'episode')
                return $this->redirectToRoute('aventure_episode', ['id' => $scene->getEpisodeParent()->getId(),'_fragment' => 'scn' . $scene->getNumero()]);

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
    public function editScene(Request $request, Scene $scene, FileHandler $fileHandler, Numeroteur $numeroteur, Baliseur $baliseur,
                                ParticipationHandler $participationHandler, PersonnageRepository $personnageRepository,
                                ParticipationRepository $participationRepository, SceneRepository $sceneRepository): Response {

        $numeroDepart = $scene->getNumero();
        $fratrieDepartId = $scene->getEpisodeParent()->getId();
        
        // Preparing all Characters for JS lists — triés par clan puis nom pour grouping en optgroup
        $tout_pjs  = $personnageRepository->findAllPJsSorted();
        $tout_pnjs = $personnageRepository->findAllPNJsSorted();
        $participations_pjs = $participationRepository->findBy(array('scene' => $scene, 'estPj' => true));
        $participations_pnjs = $participationRepository->findBy(array('scene' => $scene, 'estPj' => false));

        $scene->setTexte($baliseur->debaliserPersonnages($scene->getTexte()));

        $scene->setTexte($baliseur->debaliserLieux($scene->getTexte()));

        $form = $this->createForm(AdminSceneType::class, $scene);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'scene-s' . $scene->getEpisodeParent()->getChapitreParent()->getSaisonParent()->getNumero()
                . '-ch' . $scene->getEpisodeParent()->getChapitreParent()->getNumero()
                . '-ep' . $scene->getEpisodeParent()->getNumero() . '-scn' . $scene->getNumero();
                $scene->setImage($fileHandler->handle($nouvelleImage, $scene->getImage(), $prefix, 'scenes', 'landscape720'));
            } elseif ($request->request->get('remove_image') === '1' && $scene->getImage()) {
                $fileHandler->handle(null, $scene->getImage(), null, 'scenes');
                $scene->setImage(null);
            }

            // Formating and Fusioning Data from Meta from Participants
            $participants_modifies = $participationHandler->fusionnerParticipants($request->get('participants'), $request->get('participants_xp'),
                                                                                    $request->get('participants_mort'), $request->get('participants_pnjs'),
                                                                                    $request->get('participants_pnjs_mort'), $request->get('participants_xpBonus'));

            $toutes_participations = array_merge($participations_pjs, $participations_pnjs);

            // If Participants from Data exists, edit them or delete them
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
                        $this->addFlash('warning', 'Le personnage ' . $une_participation->getPersonnage()->getPrenom() . ' a bien été modifié des participant !');
                    }
                }

                if ($trouvee == false) {
                    $this->addFlash('danger', 'Le personnage ' . $une_participation->getPersonnage()->getPrenom() . ' a bien été retiré des participants !');
                    $this->getDoctrine()->getManager()->remove($une_participation);
                }
            }

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
                    $this->addFlash('success', 'Le personnage ' . $nouvelle_participation->getPersonnage()->getPrenom() . ' a bien été ajouté en participant !');
                }
            }

            $scene->setTexte($baliseur->baliserPersonnages($scene->getTexte()));

            $scene->setTexte($baliseur->baliserLieux($scene->getTexte()));

            // RE-ORDERING if Number has changed or if Parent has changed
            if ($numeroDepart != $scene->getNumero() || $fratrieDepartId != $scene->getEpisodeParent()->getId())
            {
                $fratrieDepart = $sceneRepository->findBy(['episodeParent' => $fratrieDepartId]);
                $fratrieArrivee = $sceneRepository->findBy(['episodeParent' => $scene->getEpisodeParent()->getId()]);
                $numeroteur->reordonnerNumero($scene->getId(), $numeroDepart, $scene->getNumero(), $fratrieDepart, $fratrieArrivee);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La scène a bien été modifiée.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'episode')
                return $this->redirectToRoute('aventure_episode', ['id' => $scene->getEpisodeParent()->getId(),'_fragment' => 'scn' . $scene->getNumero()]);
    
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
    public function deleteScene(Request $request, Scene $scene, Numeroteur $numeroteur, SceneRepository $sceneRepository, FileHandler $fileHandler): Response {
        
        $episodeParent = $scene->getEpisodeParent(); // Saving Parent for later Redirection after deletion

        if ($this->isCsrfTokenValid('delete' . $scene->getId(), $request->request->get('_csrf_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $nomImageASupprimer = basename($scene->getImage());

            $fileHandler->handle(null, $scene->getImage(), null, 'scenes');

            $fratrieDepartId = $scene->getEpisodeParent()->getId();
            $fratrieDepart = $sceneRepository->findBy(['episodeParent' => $fratrieDepartId]);
            $numeroteur->reordonnerNumero($scene->getId(), $scene->getNumero(), -1, $fratrieDepart, []);

            $entityManager->remove($scene);
            $entityManager->flush();
            $this->addFlash('success', 'La scène a bien été supprimée.');
        }

        if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'episode')
            return $this->redirectToRoute('aventure_episode', ['id' => $episodeParent->getId()]);

        return $this->redirectToRoute('admin_scene');
    }
}