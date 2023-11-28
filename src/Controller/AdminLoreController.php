<?php

namespace App\Controller;

use App\Entity\Lore;
use App\Form\AdminLoreType;
use App\Service\Numeroteur;
use App\Service\FileHandler;
use App\Repository\LoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminLoreController extends AbstractController
{
    /**
     * @Route("/admin/lore", name="admin_lore")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminLores(LoreRepository $loreRepository): Response
    {
        $lores = $loreRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $lores,
            'element' => 'lore',
            'label' => 'Lore',
            'labels' => 'Lores',
            'genre' => 'M',
            'determinant' => 'un',
            'img_size' => '48',
            'extra_col2' => 'numero',
        ]);
    }

    /**
     * @Route("/admin/lore/create", name="admin_lore_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addLore(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, LoreRepository $loreRepository, Numeroteur $numeroteur): Response
    {
        $lore = new Lore;
        $form = $this->createForm(AdminLoreType::class, $lore);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            // IMAGE file handling
            $image = $form->get('image')->getData();
            if (!empty($image)) {
                $prefix = 'lore-' . $lore->getNom() . '-image';
                $lore->setImage($fileHandler->handle($image, null, $prefix, 'lores'));
            }

            // PDF file handling
            $pdf = $form->get('pdf')->getData();
            if (!empty($pdf)) {
                $prefix = 'lore-' . $lore->getNom() . '-pdf';
                $lore->setPdf($fileHandler->handle($pdf, null, $prefix, '../pdf/lores'));
            }

            $em->persist($lore);
            $em->flush();
            $this->addFlash('success', 'Le Lore a bien été ajouté');

            // RE-ORDERING
            $fratrieArrivee = $loreRepository->findAll();
            $numeroteur->reordonnerNumero($lore->getId(), -1, $lore->getNumero(), [], $fratrieArrivee);

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'lore')
                return $this->redirectToRoute('empire_lore', ['id' => $lore->getId()]);

            return $this->redirectToRoute('admin_lore');
        }

        // RENDER
        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'lore',
            'label' => 'Lore',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/lore/{id}/edit", name="admin_lore_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editLore(Request $request, Lore $lore, FileHandler $fileHandler, LoreRepository $loreRepository, Numeroteur $numeroteur): Response
    {
        // Saving numbler and ID for re-ordering later
        $numeroDepart = $lore->getNumero();

        $form = $this->createForm(AdminLoreType::class, $lore);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            // IMAGE File Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'lore-' . $lore->getNom() . '-image';
                $lore->setImage($fileHandler->handle($nouvelleImage, $lore->getImage(), $prefix, 'lores'));
            }

            // PDF File Handling
            $nouveauPDF = $form->get('pdf')->getData();
            if (!empty($nouveauPDF)) {
                $prefix = 'lore-' . $lore->getNom() . '-pdf';
                $lore->setPdf($fileHandler->handle($nouveauPDF, $lore->getPdf(), $prefix, '../pdf/lores'));
            }

            // RE-ORDERING : if number has changed or if parent has changed
            if ($numeroDepart != $lore->getNumero())
            {
                $fratrie = $loreRepository->findAll();
                $numeroteur->reordonnerNumero($lore->getId(), $numeroDepart, $lore->getNumero(), $fratrie, $fratrie);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le Lore a bien été modifiée');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'lore')
                return $this->redirectToRoute('empire_lore', ['id' => $lore->getId()]);

            return $this->redirectToRoute('admin_lore');
        }

        // RENDER
        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'lore' => $lore,
            'entity' => 'lore',
            'label' => 'Lore',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/lore/{id}/delete", name="admin_lore_delete")
     * @IsGranted("ROLE_MJ")
     */
    public function deleteLore(Request $request, Lore $lore, FileHandler $fileHandler, EntityManagerInterface $em, LoreRepository $loreRepository, Numeroteur $numeroteur): Response
    {
        if ( $this->isCsrfTokenValid('delete' . $lore->getId(), $request->query->get('csrf')))
        {
            // File Image and PDF handling
            $fileHandler->handle(null, $lore->getImage(), null, 'lores');
            $fileHandler->handle(null, $lore->getPdf(), null, '../pdf/lores');

            // RE-ORDERING
            $fratrie = $loreRepository->findAll();
            $numeroteur->reordonnerNumero($lore->getId(), $lore->getNumero(), -1, $fratrie, []);

            $em->remove($lore);
            $em->flush();
            $this->addFlash('success', 'Le Lore a bien été supprimé');    
        }

        return $this->redirectToRoute('admin_lore');
    }
}
