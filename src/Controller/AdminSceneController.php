<?php

namespace App\Controller;

use App\Entity\Scene;
use App\Service\Uploader;
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
use Symfony\Component\Filesystem\Filesystem;
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
    public function afficherAdminScenes(SceneRepository $sceneRepository): Response {
        $scenes = $sceneRepository->findBy(array(), array('episodeParent' => 'ASC'));

        return $this->render('admin_scene/index.html.twig', [
            'controller_name' => 'AdminSceneController',
            'scenes' => $scenes,
        ]);
    }

    /**
     * @Route("/admin/scene/create", name="admin_scene_create")
     * @IsGranted("ROLE_MJ")
     */
    public function creerScene(Request $request, EntityManagerInterface $em, Uploader $uploadeur, Baliseur $baliseur,
                            ParticipationHandler $participationHandler, PersonnageRepository $personnageRepository,
                            EpisodeRepository $episodeRepository, Numeroteur $numeroteur, SceneRepository $sceneRepository) {

        $scene = new Scene;

        // Création de liste pour l'affichage des listes déroulantes dynamiques en JS
        $tout_pjs = $personnageRepository->findBy(array('estPj' => true));
        $tout_pnjs = $personnageRepository->findBy(array('estPj' => false));

        // Gestion du pré-remplissage des champs
        if ( !empty($request->query->get('numero')) && $request->query->get('numero') > 0
        && !empty($request->query->get('episodeID')) && $request->query->get('episodeID') > 0 )
        {
            $scene->setNumero($request->query->get('numero'));
            $episodeParent = $episodeRepository->find($request->query->get('episodeID'));
            if ($episodeParent !== null)
                $scene->setEpisodeParent($episodeParent);
        }

        // Création du Formulaire
        $form = $this->createForm(AdminSceneType::class, $scene);
        $form->handleRequest($request);

        // Gestion du Formulaire posté et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // CREATION FICHIER IMAGE & UPLOAD
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'scene-s' . $scene->getEpisodeParent()->getChapitreParent()->getSaisonParent()->getNumero()
                            . '-ch' . $scene->getEpisodeParent()->getChapitreParent()->getNumero()
                            . '-ep' . $scene->getEpisodeParent()->getNumero() . '-scn' . $scene->getNumero();
                $nouvelleImageNomFichier = $uploadeur->upload($nouvelleImage,$prefix , 'scenes');
                $nouveauCheminRelatif = 'assets/img/scenes/' . $nouvelleImageNomFichier;
                $scene->setImage($nouveauCheminRelatif);
            } else { $scene->setImage('assets/img/placeholders/1280x720.jpg'); }

            // Formatage et fusion des données formulaire postées concernant les Participants
            $participants_a_ajoutes = $participationHandler->fusionnerParticipants($request->get('participants'), $request->get('participants_xp'),
                                                                                    $request->get('participants_mort'), $request->get('participants_pnjs'),
                                                                                    $request->get('participants_pnjs_mort'));

            // Ajout des Participations
            $participationHandler->ajouterParticipations($participants_a_ajoutes, $scene);

            // BALISAGE : capture les mots entre [], vérifie si un prénom personnage correspondant existe, remplace par un lien personnage HTML
            $scene->setTexte($baliseur->baliserPersonnages($scene->getTexte()));
            
            // CREATION ENTITE
            $em->persist($scene);
            $em->flush();
            $this->addFlash('success', 'La scène a bien été crée !');

            // NUMEROTAGE : ajuste le numéro si des scènes ont été supprimés ou intercalés
            $fratrieArrivee = $sceneRepository->findBy(['episodeParent' => $scene->getEpisodeParent()->getId()]);
            $numeroteur->reordonnerNumero($scene->getId(), -1, $scene->getNumero(), [], $fratrieArrivee);

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'episode')
                return $this->redirectToRoute('aventure_episode', ['id' => $scene->getEpisodeParent()->getId(),'_fragment' => 'scn' . $scene->getId()]);
        
            return $this->redirectToRoute('admin_scene');

        } else {
            // AFFICHAGE FORMULAIRE si formulaire pas soumis ou pas valide
            return $this->render('admin_scene/create.html.twig', [
                'type' => 'Créer',
                'form' => $form->createView(),
                'tout_pjs' => $tout_pjs,
                'tout_pnjs' => $tout_pnjs,
            ]);
        }
    }

    /**
     * @Route("/admin/scene/{id}/edit", name="admin_scene_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editerScene(Request $request, Scene $scene, Uploader $uploadeur, Numeroteur $numeroteur, Baliseur $baliseur,
                                ParticipationHandler $participationHandler, PersonnageRepository $personnageRepository,
                                ParticipationRepository $participationRepository, SceneRepository $sceneRepository): Response {

        // Stockage du numéro et de l'ID de l'episode avant édition
        $numeroDepart = $scene->getNumero();
        $fratrieDepartId = $scene->getEpisodeParent()->getId();
        
        // Personnages & Participations pour Affichage & JS
        $tout_pjs = $personnageRepository->findBy(array('estPj' => true));
        $tout_pnjs = $personnageRepository->findBy(array('estPj' => false));
        $participations_pjs = $participationRepository->findBy(array('scene' => $scene, 'estPj' => true));
        $participations_pnjs = $participationRepository->findBy(array('scene' => $scene, 'estPj' => false));

        // DEBALISEUR des PERSONNAGES : dans le texte, capture les prénoms entre balises <a><img>, vérifie si le personnage existe, remplace les balises par des crochets []
        $scene->setTexte($baliseur->debaliserPersonnages($scene->getTexte()));

        // DEBALISEUR des LIEUX {}
        $scene->setTexte($baliseur->debaliserLieux($scene->getTexte()));

        // FORM VIEW
        $form = $this->createForm(AdminSceneType::class, $scene);
        $form->handleRequest($request);

        // GESTION FORMULAIRE VALIDE
        if($form->isSubmitted() && $form->isValid()) {

            // EDITION FICHIER IMAGE & UPLOAD
            $nouvelleImage = $form->get('image')->getData();

            if (!empty($nouvelleImage)) {

                $AncienneImageNomFichier = basename($scene->getImage());

                $nouvelleImageNomFichier = $uploadeur->upload($nouvelleImage, 'scene-s'
                    . $scene->getEpisodeParent()->getChapitreParent()->getSaisonParent()->getNumero()
                    . '-ch' . $scene->getEpisodeParent()->getChapitreParent()->getNumero()
                    . '-ep' . $scene->getEpisodeParent()->getNumero()
                    . '-scn' . $scene->getNumero(), 'scenes');
                $nouveauChemingRelatif = 'assets/img/scenes/' . $nouvelleImageNomFichier;
                $scene->setImage($nouveauChemingRelatif);

                $ancienneImageCheminComplet = $this->getParameter('image_directory') . '/scenes/' . $AncienneImageNomFichier;
                $filesystem = new Filesystem();
                $filesystem->remove($ancienneImageCheminComplet);
            }

            // Formatage et fusion des données formulaire postées concernant les Participants
            $participants_modifies = $participationHandler->fusionnerParticipants($request->get('participants'), $request->get('participants_xp'),
                                                                                    $request->get('participants_mort'), $request->get('participants_pnjs'),
                                                                                    $request->get('participants_pnjs_mort'));

            $toutes_participations = array_merge($participations_pjs, $participations_pnjs);

            // Modification des Participations correspondante trouvée sinon suppression
            foreach ($toutes_participations as $une_participation) {
                $trouvee = false;

                foreach ($participants_modifies as $un_participant_modifie) {
                    if ($une_participation->getPersonnage()->getId() == $un_participant_modifie['id']) {
                        $trouvee = true;
                        break;
                    }
                }

                if ($trouvee == true) {
                    if ($une_participation->getXpGagne() != $un_participant_modifie['xp'] || $une_participation->getEstMort() != $un_participant_modifie['mort'] ) {
                        $une_participation->setXpGagne($un_participant_modifie['xp']);
                        $une_participation->setEstMort($un_participant_modifie['mort']);
                        $this->addFlash('warning', 'Le personnage ' . $une_participation->getPersonnage()->getPrenom() . ' a bien été modifié des participant !');
                    }
                }

                if ($trouvee == false) {
                    $this->addFlash('danger', 'Le personnage ' . $une_participation->getPersonnage()->getPrenom() . ' a bien été retiré des participants !');
                    $this->getDoctrine()->getManager()->remove($une_participation);
                }
            }

            // Ajout des Participations necessaires
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
                    $this->getDoctrine()->getManager()->persist($nouvelle_participation);
                    $this->addFlash('success', 'Le personnage ' . $nouvelle_participation->getPersonnage()->getPrenom() . ' a bien été ajouté en participant !');
                }
            }

            // BALISAGE : capture les mots entre [], vérifie si un prénom personnage correspondant existe, remplace par un lien personnage HTML
            $scene->setTexte($baliseur->baliserPersonnages($scene->getTexte()));

            // BALISAGE des LIEUX : capture les mots entre {}, vérifie si un nom de lieu correspondant existe, remplace par un lien vers la fiche du lieu en HTML
            $scene->setTexte($baliseur->baliserLieux($scene->getTexte()));

            // NUMEROTAGE : augmente ou réduit le numéro de la scène si une scne a été supprimée ou intercalée
            if ($numeroDepart != $scene->getNumero() || $fratrieDepartId != $scene->getEpisodeParent()->getId())
            {
                $fratrieDepart = $sceneRepository->findBy(['episodeParent' => $fratrieDepartId]);
                $fratrieArrivee = $sceneRepository->findBy(['episodeParent' => $scene->getEpisodeParent()->getId()]);
                $numeroteur->reordonnerNumero($scene->getId(), $numeroDepart, $scene->getNumero(), $fratrieDepart, $fratrieArrivee);
            }

            // EDITION ENTITE
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La scène a bien été modifiée !');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'episode')
                return $this->redirectToRoute('aventure_episode', ['id' => $scene->getEpisodeParent()->getId(),'_fragment' => 'scn' . $scene->getId()]);
    
            return $this->redirectToRoute('admin_scene');
        }

        // AFFICHAGE FORMULAIRE si formulaire pas soumis et valide
        return $this->renderForm('admin_scene/edit.html.twig', [
            'scene' => $scene,
            'form' => $form,
            'type' => 'Modifier',
            'tout_pjs' => $tout_pjs,
            'tout_pnjs' => $tout_pnjs,
            'participations_pjs' => $participations_pjs,
            'participations_pnjs' => $participations_pnjs,
        ]); 
    }

    /**
     * @Route("/admin/scene/{id}/delete", name="admin_scene_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function supprimerScene(Request $request, Scene $scene, Numeroteur $numeroteur, SceneRepository $sceneRepository): Response {
        
        $episodeParent = $scene->getEpisodeParent(); // Pour la partie redirection

        // GESTION FORMULAIRE VALIDE
        // -------------------------
        if ($this->isCsrfTokenValid('delete' . $scene->getId(), $request->query->get('csrf'))) {

            // VERIFICATION ENFANTS
            // --------------------
            $entityManager = $this->getDoctrine()->getManager();
            $nomImageASupprimer = basename($scene->getImage());

            // SUPPRESSION FICHIER IMAGE
            // -------------------------
            if ($nomImageASupprimer != '1280x720.png') {
                $cheminImageASupprimer = $this->getParameter('image_directory') . '/scenes/' . $nomImageASupprimer;

                if (file_exists($cheminImageASupprimer)) {
                    $filesystem = new Filesystem();
                    $filesystem->remove($cheminImageASupprimer);
                }
            }

            // NUMEROTEUR
            // ----------
            $fratrieDepartId = $scene->getEpisodeParent()->getId();
            $fratrieDepart = $sceneRepository->findBy(['episodeParent' => $fratrieDepartId]);
            $numeroteur->reordonnerNumero($scene->getId(), $scene->getNumero(), -1, $fratrieDepart, []);

            // SUPPRESSION ENTITE
            // ------------------
            $entityManager->remove($scene);
            $entityManager->flush();
            $this->addFlash('success', 'La scène a bien été supprimée !');

        }

        // REDIRECTION
        // -----------
        if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'episode')
            return $this->redirectToRoute('aventure_episode', ['id' => $episodeParent->getId()]);

        return $this->redirectToRoute('admin_scene');
    }
}
