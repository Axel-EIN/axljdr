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

class AdminPersonnageController extends AbstractController
{
    /**
     * @Route("/admin/personnage", name="admin_personnage")
     */
    public function viewAdminPersonnages(PersonnageRepository $personnageRepository): Response
    {
        $personnages = $personnageRepository->findBy(array(), array('clan' => 'ASC'));
        return $this->render('admin_personnage/index.html.twig', [
            'personnages' => $personnages
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
                $prefix = 'personnage-' . $personnage->getNom() . '-' . $personnage->getPrenom() . '-icone';
                $personnage->setIcone($fileHandler->handle($nouvelleIcone, null, $prefix, 'personnages'));
            }

            // File Illustration Image Handling
            $nouvelleIllustration = $form->get('illustration')->getData();
            if (!empty($nouvelleIllustration)) {
                $prefix = 'personnage-' . $personnage->getNom() . '-' . $personnage->getPrenom() . '-illustration';
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
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'personnages')
                return $this->redirectToRoute('personnages');

            return $this->redirectToRoute('admin_personnage');
        }

        return $this->render('admin_personnage/create.html.twig', [
            'type' => 'Créer',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/personnage/{id}/edit", name="admin_personnage_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editPersonnage(Request $request, Personnage $personnage, FileHandler $fileHandler, Baliseur $baliseur): Response {

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
                $prefix = 'personnage-' . $personnage->getNom() . '-' . $personnage->getPrenom() . '-icone';
                $personnage->setIcone($fileHandler->handle($nouvelleIcone, $personnage->getIcone(), $prefix, 'personnages'));
            }

            // File Illustration Image Handling
            $nouvelleIllustration = $form->get('illustration')->getData();
            if (!empty($nouvelleIllustration)) {
                $prefix = 'personnage-' . $personnage->getNom() . '-' . $personnage->getPrenom() . '-illustration';
                $personnage->setIllustration($fileHandler->handle($nouvelleIllustration, $personnage->getIllustration(), $prefix, 'personnages'));
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

        return $this->renderForm('admin_personnage/edit.html.twig', [
            'personnage' => $personnage,
            'form' => $form,
            'type' => 'Modifier',
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