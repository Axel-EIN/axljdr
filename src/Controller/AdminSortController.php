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
            'extra_col4' => 'numero',
        ]);
    }

    /**
     * @Route("/admin/sort/create", name="admin_sort_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addSort(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, SortRepository $sortRepository, Numeroteur $numeroteur) {

        $sort = new Sort;

        // URL PARAM PRE-FILL
        $param_numero = $request->query->get('numero');
        if ( !empty($param_numero) && is_numeric($param_numero) && $param_numero > 0 )
            $sort->setNumero($param_numero);

        $param_categorie = $request->query->get('tab');
        if ( !empty($param_categorie) && !is_numeric($param_categorie) )
            $sort->setCategorie($param_categorie);

        $param_anneau = $request->query->get('subtab');
        if ( !empty($param_anneau) && !is_numeric($param_anneau) )
            $sort->setAnneau($param_anneau);

        $form = $this->createForm(AdminSortType::class, $sort);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // GET NUMERO
            $formNumero = $form->get('numero')->getData();
            $fratrieArrivee = $sortRepository->findBy(['anneau' => $form->get('anneau')->getData()]);

            if ( empty($formNumero) || !is_numeric($formNumero) || $formNumero < 1 )
                $sort->setNumero( count($fratrieArrivee) + 1 );
            else
                $sort->setNumero( $formNumero );

            $em->persist($sort);
            $em->flush();
            $this->addFlash('success', "Le sort a bien été ajouté.");

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'regles_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $sort->getCategorie() , 'subtab' => $sort->getAnneau() ] );

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
    public function editSort(Request $request, Sort $sort, SortRepository $sortRepository, Numeroteur $numeroteur): Response {

        // Saving numbler and ID for re-ordering later
        if ( empty( $sort->getNumero() ) || !is_numeric( $sort->getNumero() ) || $sort->getNumero() < 0)
            $numeroDepart = -1;
        else
            $numeroDepart = $sort->getNumero();

        $fratrieDepartId = $sort->getAnneau();

        $form = $this->createForm(AdminSortType::class, $sort);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // RE-ORDERING : if number has changed or if parent has changed
            if ($numeroDepart != $sort->getNumero() || $fratrieDepartId != $sort->getAnneau())
            {
                $fratrieDepart = $sortRepository->findBy(['anneau' => $fratrieDepartId]);
                $fratrieArrivee = $sortRepository->findBy(['anneau' => $sort->getAnneau()]);
                $numeroteur->reordonnerNumero($sort->getId(), $numeroDepart, $sort->getNumero(), $fratrieDepart, $fratrieArrivee);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', "Le Sort a bien été modifié.");

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'regles_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $sort->getCategorie() , 'subtab' => $sort->getAnneau() ] );

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
            $this->addFlash('success', "Le Sort a bien été supprimé.");
        }

        return $this->redirectToRoute('admin_sort');
    }

}
