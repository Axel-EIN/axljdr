<?php

namespace App\Controller;

use App\Entity\FichePersonnage;
use App\Form\AdminFichePersonnageType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FichePersonnageRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminFicheController extends AbstractController
{
    /**
     * @Route("/admin/fiche", name="admin_fiche")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminFiches(FichePersonnageRepository $fichePersonnageRepository): Response
    {
        $fiches = $fichePersonnageRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $fiches,
            'element' => 'fiche',
            'label' => 'Fiche',
            'labels' => 'Fiches',
            'genre' => 'F',
            'determinant' => 'une',
            'img_size' => '48',
        ]);
    }

    /**
     * @Route("/admin/fiche/create", name="admin_fiche_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addFiche(Request $request, EntityManagerInterface $em) {

        $fiche = new FichePersonnage;
        $form = $this->createForm(AdminFichePersonnageType::class, $fiche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($fiche);
            $em->flush();
            $this->addFlash('success', 'La Fiche a bien été ajoutée.');

            return $this->redirectToRoute('admin_fiche');
        }

        // RENDER
        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'fiche',
            'label' => 'Fiche',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/fiche/{id}/edit", name="admin_fiche_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editFiche(Request $request, FichePersonnage $fiche): Response {

        $form = $this->createForm(AdminFichePersonnageType::class, $fiche);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La fiche a bien été modifiée.');

            return $this->redirectToRoute('admin_fiche');
        }

        // RENDER
        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'fiche' => $fiche,
            'entity' => 'fiche',
            'label' => 'Fiche',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/fiche/{id}/delete", name="admin_fiche_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteFiche(Request $request, FichePersonnage $fiche): Response {

        if ($this->isCsrfTokenValid('delete' . $fiche->getId(), $request->query->get('csrf'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($fiche);
            $entityManager->flush();

            $this->addFlash('success', 'La fiche a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_fiche');
    }
}