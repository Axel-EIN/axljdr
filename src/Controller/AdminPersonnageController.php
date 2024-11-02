<?php

namespace App\Controller;

use App\Service\FileHandler;
use App\Service\Baliseur;
use App\Entity\Personnage;
use App\Form\AdminPersonnageType;
use App\Repository\PersonnageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\SceneRepository;
use App\Repository\DevelopmentRepository;
use App\Entity\Development;

class AdminPersonnageController extends AbstractController
{
    /**
     * @Route("/admin/personnage", name="admin_personnage")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminPersonnages(PersonnageRepository $personnageRepository): Response
    {
        $personnages = $personnageRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $personnages,
            'element' => 'personnage',
            'label' => 'Personnage',
            'labels' => 'Personnages',
            'genre' => 'M',
            'determinant' => 'un',
            'img_size' => '48',
            'extra_col1' => 'estPj',
            'extra_col2' => 'locked',
            'extra_col3' => 'estMort',
        ]);
    }

    /**
     * @Route("/admin/personnage/create", name="admin_personnage_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addPersonnage(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, Baliseur $baliseur) {

        $personnage = new Personnage;
        $form = $this->createForm(AdminPersonnageType::class, $personnage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // File Icon Image Handling
            $nouvelleIcone = $form->get('icone')->getData();
            if (!empty($nouvelleIcone)) {
                $prefix = 'personnage';

                if (!empty($personnage->getNom()))
                    $prefix = $prefix . '-' . $personnage->getNom();

                if (!empty($personnage->getPrenom()))
                    $prefix = $prefix . '-' . $personnage->getPrenom();

                $prefix = $prefix . '-portrait';
                $personnage->setIcone($fileHandler->handle($nouvelleIcone, null, $prefix, 'personnages'));
            }

            // File Illustration Image Handling
            $nouvelleIllustration = $form->get('illustration')->getData();
            if (!empty($nouvelleIllustration)) {
                $prefix = 'personnage';

                if (!empty($personnage->getNom()))
                    $prefix = $prefix . '-' . $personnage->getNom();

                if (!empty($personnage->getPrenom()))
                    $prefix = $prefix . '-' . $personnage->getPrenom();

                $prefix = $prefix . '-illustration';
                $personnage->setIllustration($fileHandler->handle($nouvelleIllustration, null, $prefix, 'personnages'));
            }

            // CHARACTER TAGGER : capture words between [], check if character exist, replace by a link
            $personnage->setDescription($baliseur->baliserPersonnages($personnage->getDescription()));

            // LOCATION TAGGER: capture words between {}, check if location exist, replace by a link
            $personnage->setDescription($baliseur->baliserLieux($personnage->getDescription()));

            $em->persist($personnage);
            $em->flush();
            $this->addFlash('success', 'Le personnage a bien été ajouté.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'personnage')
                return $this->redirectToRoute('personnages');

            return $this->redirectToRoute('admin_personnage');
        }

        // RENDER
        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'personnage',
            'label' => 'Personnage',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/personnage/{id}/edit", name="admin_personnage_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editPersonnage(
        Request $request,
        Personnage $personnage,
        FileHandler $fileHandler,
        Baliseur $baliseur,
        SceneRepository $sceneRepository,
        DevelopmentRepository $DevelopmentRepository): Response {

        // Preparing all Characters for JS lists
        $toutes_scenes = $sceneRepository->findAll();
        $developpements_pj = $DevelopmentRepository->findBy(array('protagonist' => $personnage));

        // CHARACTER UNTAGGER : capture words in character-links, check if character exist and replace with []
        $personnage->setDescription($baliseur->debaliserPersonnages($personnage->getDescription()));

        // LOCATION UNTAGGER : capture words in location-links, check if location exist and replace with {}
        $personnage->setDescription($baliseur->debaliserLieux($personnage->getDescription()));

        $form = $this->createForm(AdminPersonnageType::class, $personnage);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // File Icon Image Handling
            $nouvelleIcone = $form->get('icone')->getData();
            if (!empty($nouvelleIcone)) {
                $prefix = 'personnage';

                if (!empty($personnage->getNom()))
                    $prefix = $prefix . '-' . $personnage->getNom();

                if (!empty($personnage->getPrenom()))
                    $prefix = $prefix . '-' . $personnage->getPrenom();

                $prefix = $prefix . '-portrait';
                $personnage->setIcone($fileHandler->handle($nouvelleIcone, $personnage->getIcone(), $prefix, 'personnages'));
            }

            // File Illustration Image Handling
            $nouvelleIllustration = $form->get('illustration')->getData();
            if (!empty($nouvelleIllustration)) {
                $prefix = 'personnage';

                if (!empty($personnage->getNom()))
                    $prefix = $prefix . '-' . $personnage->getNom();

                if (!empty($personnage->getPrenom()))
                    $prefix = $prefix . '-' . $personnage->getPrenom();

                $prefix = $prefix . '-illustration';
                $personnage->setIllustration($fileHandler->handle($nouvelleIllustration, $personnage->getIllustration(), $prefix, 'personnages'));
            }

            // Getting data for Developpements
            $developpements_modifies = $request->get('developpements');
            var_dump($developpements_modifies);

            // If Developpments Data exists, edit or delete them, this browse all current developpments and edit the matching scenes or delete if not matching
            if (!empty($developpements_pj)) {
                var_dump('HELLO');
                exit;
                foreach ($developpements_pj as $un_developpement_pj_existant) {
                    $trouve = false;
    
                    foreach ($developpements_modifies as $un_developpement_modifie) {
                        if ($un_developpement_pj_existant->getScene() == $un_developpement_modifie['scene']) {
                            $trouve = true;
                            break;
                        }
                    }
    
                    if ($trouve == true) {
                        $un_developpement_pj_existant->setType($un_developpement_modifie['type']);
                        $un_developpement_pj_existant->setResume($un_developpement_modifie['resume']);
                        $this->addFlash(
                            'warning', 'Le développement du personnage ' . $un_developpement_pj_existant->getProtagonist()->getPrenom()
                            . ' dans la scène ' . $un_developpement_pj_existant->getScene()->getTitre() . ' a bien été modifié !');
                    }
    
                    if ($trouve == false) {
                        $this->addFlash(
                            'danger', 'Le développement du personnage ' . $personnage->getPrenom()
                            . ' dans la scène ' . $un_developpement_pj_existant->getScene()->getTitre() . ' a bien été supprimé !');
                    }
                }
            }

            // Add new Developpements
            foreach ($developpements_modifies as $un_developpement_modifie) {
                $trouve = false;
                var_dump('HEYYY');

                foreach($developpements_pj as $un_developpement_pj_existant) {
                    if ($un_developpement_modifie['scene'] == $un_developpement_pj_existant->getScene()) {
                        $trouve = true;
                        var_dump('TROUVEEE!!!');
                        break;
                    }
                }

                if ($trouve == false) {
                    var_dump('PAS TROUVEE !!!');
                    $scene_trouvee = $sceneRepository->find($un_developpement_modifie['scene']);
                    $nouveau_developpement = new Development;
                    $nouveau_developpement->setProtagonist($personnage);
                    $nouveau_developpement->setScene($scene_trouvee);
                    $nouveau_developpement->setType($un_developpement_modifie['type']);
                    $nouveau_developpement->setResume($un_developpement_modifie['resume']);
                    $this->getDoctrine()->getManager()->persist($nouveau_developpement);
                    $this->addFlash('success', 'Le développement du personnage ' . $personnage->getPrenom()
                        . ' dans la scène ' . $scene_trouvee->getTitre() . ' a bien été ajouté !');
                }
            }

            // CHARACTER TAGGER : capture words between [], check if character exist, replace by a link
            $personnage->setDescription($baliseur->baliserPersonnages($personnage->getDescription()));

            // LOCATION TAGGER: capture words between {}, check if location exist, replace by a link
            $personnage->setDescription($baliseur->baliserLieux($personnage->getDescription()));

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le personnage a bien été modifié.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'personnage')
                return $this->redirectToRoute('personnage_profil', ['id' => $personnage->getId()]);

            return $this->redirectToRoute('admin_personnage');
        }

        // RENDER
        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'personnage' => $personnage,
            'entity' => 'personnage',
            'label' => 'Personnage',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form,
            'toutes_scenes' => $toutes_scenes,
            'developpements_pj' => $developpements_pj
        ]);
    }

    /**
     * @Route("/admin/personnage/{id}/delete", name="admin_personnage_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function deletePersonnage(Request $request, Personnage $personnage, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $personnage->getId(), $request->query->get('csrf'))) {

            $entityManager = $this->getDoctrine()->getManager();

            // Image Files Handling
            $fileHandler->handle(null, $personnage->getIcone(), null, 'personnages');
            $fileHandler->handle(null, $personnage->getIllustration(), null, 'personnages');

            $entityManager->remove($personnage);
            $entityManager->flush();
            $this->addFlash('success', 'Le personnage a bien été supprimé.');
        }

        return $this->redirectToRoute('admin_personnage');
    }
}