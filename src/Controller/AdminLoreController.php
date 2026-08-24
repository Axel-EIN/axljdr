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
            'table_cols' => [
                'image:Image:image:NA_LORE',
                'nom:Nom::bold',
                'pdf:PDF:bool',
                'numero:Ordre:number',
                'part1:part1text:bool',
                'part2:part1text:bool',
                'part3:part1text:bool',
                'locked:Bloqué:boolInt',
            ],
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

            $image = $form->get('image')->getData();
            if (!empty($image)) {
                $prefix = 'lore-' . $lore->getNom() . '-image';
                $lore->setImage($fileHandler->handle($image, null, $prefix, 'lores', 'fourthird900'));
            }

            $pdf = $form->get('pdf')->getData();
            if (!empty($pdf)) {
                $prefix = 'lore-' . $lore->getNom() . '-pdf';
                $lore->setPdf($fileHandler->handle($pdf, null, $prefix, 'pdf-lores'));
            }

            $em->persist($lore);
            $em->flush();
            $this->addFlash('success', 'Le Lore a bien été ajouté');

            $fratrieArrivee = $loreRepository->findAll();
            $numeroteur->reordonnerNumero($lore->getId(), -1, $lore->getNumero(), [], $fratrieArrivee);

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'lore')
                return $this->redirectToRoute('empire_lore', ['id' => $lore->getId()]);

            return $this->redirectToRoute('admin_lore');
        }

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
        $numeroDepart = $lore->getNumero();

        $form = $this->createForm(AdminLoreType::class, $lore);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'lore-' . $lore->getNom() . '-image';
                $lore->setImage($fileHandler->handle($nouvelleImage, $lore->getImage(), $prefix, 'lores', 'fourthird900'));
            } elseif ($request->request->get('remove_image') === '1' && $lore->getImage()) {
                $fileHandler->handle(null, $lore->getImage(), null, 'lores');
                $lore->setImage(null);
            }

            $nouveauPDF = $form->get('pdf')->getData();
            if (!empty($nouveauPDF)) {
                $prefix = 'lore-' . $lore->getNom() . '-pdf';
                $lore->setPdf($fileHandler->handle($nouveauPDF, $lore->getPdf(), $prefix, 'pdf-lores'));
            }

            // RE-ORDERING : if number has changed or if parent has changed
            if ($numeroDepart != $lore->getNumero())
            {
                $fratrie = $loreRepository->findAll();
                $numeroteur->reordonnerNumero($lore->getId(), $numeroDepart, $lore->getNumero(), $fratrie, $fratrie);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le Lore a bien été modifiée');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'lore')
                return $this->redirectToRoute('empire_lore', ['id' => $lore->getId()]);

            return $this->redirectToRoute('admin_lore');
        }

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
     * @Route("/admin/lore/{id}/delete", name="admin_lore_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteLore(Request $request, Lore $lore, FileHandler $fileHandler, EntityManagerInterface $em, LoreRepository $loreRepository, Numeroteur $numeroteur): Response
    {
        if ( $this->isCsrfTokenValid('delete' . $lore->getId(), $request->request->get('_csrf_token')))
        {

            $fileHandler->handle(null, $lore->getImage(), null, 'lores');
            $fileHandler->handle(null, $lore->getPdf(), null, 'pdf-lores');

            $fratrie = $loreRepository->findAll();
            $numeroteur->reordonnerNumero($lore->getId(), $lore->getNumero(), -1, $fratrie, []);

            $em->remove($lore);
            $em->flush();
            $this->addFlash('success', 'Le Lore a bien été supprimé');    
        }

        return $this->redirectToRoute('admin_lore');
    }
}
