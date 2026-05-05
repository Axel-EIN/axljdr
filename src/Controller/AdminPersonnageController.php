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
            'table_cols' => [
              'estPj:PJ:bool',
              'joueur.pseudo:Player',
              'icone:Portrait:symbol:NA_PERSO_PORTRAIT_{genre}',
              'prenom:Prénom::bold',
              'illustration:Illu:image:NA_PERSO_ILLU_{genre}',
              'nom:Nom',
              'famille.nom:Famille',
              'ecole.nom:Ecole',
              'clan.nom:Clan',
              'locked:Verrou:bool',
              'estMort:Mort:bool',
              'description:Text:bool',
            ],
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
                $personnage->setIcone($fileHandler->handle($nouvelleIcone, null, $prefix, 'personnages', 'square240'));
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
                $personnage->setIllustration($fileHandler->handle($nouvelleIllustration, null, $prefix, 'personnages', 'vertical450'));
            }

            // CHARACTER & LOCATION TAGGER (skip if description is empty/null)
            if (!empty($personnage->getDescription())) {
                $personnage->setDescription($baliseur->baliserPersonnages($personnage->getDescription()));
                $personnage->setDescription($baliseur->baliserLieux($personnage->getDescription()));
            }

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
    public function editPersonnage(Request $request, Personnage $personnage, FileHandler $fileHandler, Baliseur $baliseur): Response {

        // CHARACTER & LOCATION UNTAGGER (skip if description is empty/null)
        if (!empty($personnage->getDescription())) {
            $personnage->setDescription($baliseur->debaliserPersonnages($personnage->getDescription()));
            $personnage->setDescription($baliseur->debaliserLieux($personnage->getDescription()));
        }

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
                $personnage->setIcone($fileHandler->handle($nouvelleIcone, $personnage->getIcone(), $prefix, 'personnages', 'square240'));
            } elseif ($request->request->get('remove_icone') === '1' && $personnage->getIcone()) {
                $fileHandler->handle(null, $personnage->getIcone(), null, 'personnages');
                $personnage->setIcone(null);
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
                $personnage->setIllustration($fileHandler->handle($nouvelleIllustration, $personnage->getIllustration(), $prefix, 'personnages', 'vertical450'));
            } elseif ($request->request->get('remove_illustration') === '1' && $personnage->getIllustration()) {
                $fileHandler->handle(null, $personnage->getIllustration(), null, 'personnages');
                $personnage->setIllustration(null);
            }

            // CHARACTER & LOCATION TAGGER (skip if description is empty/null)
            if (!empty($personnage->getDescription())) {
                $personnage->setDescription($baliseur->baliserPersonnages($personnage->getDescription()));
                $personnage->setDescription($baliseur->baliserLieux($personnage->getDescription()));
            }

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
        ]);
    }

    /**
     * @Route("/admin/personnage/{id}/delete", name="admin_personnage_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deletePersonnage(Request $request, Personnage $personnage, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $personnage->getId(), $request->request->get('_csrf_token'))) {

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