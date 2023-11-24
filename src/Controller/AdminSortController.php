<?php

namespace App\Controller;

use App\Entity\Sort;
use App\Form\AdminSortType;
use App\Service\Numeroteur;
use App\Service\FileHandler;
use App\Repository\SortRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminSortController extends AbstractController
{
    /**
     * @Route("/admin/sort", name="admin_sort")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminSorts( SortRepository $sortRepository ): Response
    {
        $sorts = $sortRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $sorts,
            'label' => 'Sort',
            'labels' => "Sorts",
            'genre' => 'M',
            'determinant' => 'un',
            'element' => 'sort',
            'img_size' => '32',
            'extra_col1' => 'categorie',
            'extra_col2' => 'anneau',
            'extra_col3' => 'niveau',
        ]);
    }

    /**
     * @Route("/admin/sort/create", name="admin_sort_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addSort(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, SortRepository $sortRepository, Numeroteur $numeroteur) {

        $sort = new Sort;

        $form = $this->createForm(AdminSortType::class, $sort);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($sort);
            $em->flush();
            $this->addFlash('success', "Le sort a bien été ajouté.");

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'regles_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $sort->getCategorie() ] );

            return $this->redirectToRoute('admin_sort');
        }
        
        // RENDER
        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'sort',
            'label' => 'Sort',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/sort/{id}/edit", name="admin_sort_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editSort(Request $request, Sort $sort, FileHandler $fileHandler, SortRepository $sortRepository, Numeroteur $numeroteur): Response {

        $form = $this->createForm(AdminSortType::class, $sort);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', "L'sort a bien été modifié.");

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'regles_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $sort->getCategorie() ] );

            return $this->redirectToRoute('admin_sort');
        }

        // RENDER
        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'sort' => $sort,
            'entity' => 'sort',
            'label' => 'Sort',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/sort/{id}/delete", name="admin_sort_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteSort(Request $request, Sort $sort, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $sort->getId(), $request->query->get('csrf'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($sort);
            $entityManager->flush();
            $this->addFlash('success', "L'sort a bien été supprimé.");
        }

        return $this->redirectToRoute('admin_sort');
    }

}
