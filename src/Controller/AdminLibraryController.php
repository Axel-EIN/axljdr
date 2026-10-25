<?php

namespace App\Controller;

use App\Entity\Library;
use App\Service\Orderer;
use App\Service\FileHandler;
use App\Form\AdminLibraryType;
use App\Repository\LibraryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\Unlocker;

class AdminLibraryController extends AbstractController
{
    /**
     * @Route("/admin/library", name="admin_library")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminLibraries(LibraryRepository $libraryRepository): Response
    {
        $libraries = $libraryRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $libraries,
            'element' => 'library',
            'label' => 'Bibliothèque',
            'labels' => 'Bibliothèques',
            'genre' => 'F',
            'determinant' => 'une',
            'table_cols' => [
                'image:Image:image:NA_LIBRARY',
                'nom:Nom::bold',
                'entity:Entité',
                'base:Base',
                'pdf:PDF:bool',
                'numero:Ordre:number',
                'description:Text:bool',
                'access:Accès:access',
                'publishedAt:Publié le:date',
            ],
        ]);
    }

    /**
     * @Route("/admin/library/create", name="admin_library_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addLibrary(Request $request, FileHandler $fileHandler, EntityManagerInterface $em, LibraryRepository $libraryRepository, Orderer $orderer, Unlocker $unlocker)
    {
        $library = new Library;
        $form = $this->createForm(AdminLibraryType::class, $library);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            $image = $form->get('image')->getData();
            if (!empty($image)) {
                $prefix = 'library-' . $library->getNom() . '-image';
                $library->setImage($fileHandler->handle($image, null, $prefix, 'libraries', 'fourthird900'));
            }

            $pdf = $form->get('pdf')->getData();
            if (!empty($pdf)) {
                $prefix = 'library-' . $library->getNom() . '-pdf';
                $library->setPdf($fileHandler->handle($pdf, null, $prefix, 'libraries-pdfs'));
            }

            $em->persist($library);
            $em->flush();
            $unlocker->sync($library, $form->get('unlockedBy')->getData());
            $em->flush();
            $this->addFlash('success', 'La Bibliothèque a bien été ajoutée');

            $newSiblings = $libraryRepository->findBy(['base' => $library->getBase()]);
            $orderer->reorderNumber($library->getId(), -1, $library->getNumero(), [], $newSiblings);

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library')
                return $this->redirectToRoute('rules_library', ['id' => $library->getId()]);

            return $this->redirectToRoute('admin_library');
        }

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'library',
            'label' => 'Bibliothèque',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/library/{id}/edit", name="admin_library_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editLibrary(Request $request, Library $library, FileHandler $fileHandler, LibraryRepository $libraryRepository, Orderer $orderer, Unlocker $unlocker): Response
    {
        $previousNumber = $library->getNumero();
        $previousSiblingsId = $library->getBase();

        $form = $this->createForm(AdminLibraryType::class, $library);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($library));
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = 'library-' . $library->getNom() . '-image';
                $library->setImage($fileHandler->handle($newImage, $library->getImage(), $prefix, 'libraries', 'fourthird900'));
            } elseif ($request->request->get('remove_image') === '1' && $library->getImage()) {
                $fileHandler->handle(null, $library->getImage(), null, 'libraries');
                $library->setImage(null);
            }

            $newPdf = $form->get('pdf')->getData();
            if (!empty($newPdf)) {
                $prefix = 'library-' . $library->getNom() . '-pdf';
                $library->setPdf($fileHandler->handle($newPdf, $library->getPdf(), $prefix, 'libraries-pdfs'));
            }

            if ($previousNumber != $library->getNumero() || $previousSiblingsId != $library->getBase())
            {
                $previousSiblings = $libraryRepository->findBy(['base' => $previousSiblingsId]);
                $newSiblings = $libraryRepository->findBy(['base' => $library->getBase()]);
                $orderer->reorderNumber($library->getId(), $previousNumber, $library->getNumero(), $previousSiblings, $newSiblings);
            }

            $unlocker->sync($library, $form->get('unlockedBy')->getData());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La Bibliothèque a bien été modifiée');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library')
                return $this->redirectToRoute('rules_library', ['id' => $library->getId()]);

            return $this->redirectToRoute('admin_library');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'library' => $library,
            'entity' => 'library',
            'label' => 'Bibliothèque',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/library/{id}/delete", name="admin_library_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteLibrary(Request $request, Library $library, FileHandler $fileHandler, LibraryRepository $libraryRepository, Orderer $orderer, Unlocker $unlocker): Response
    {
        if ( $this->isCsrfTokenValid('delete' . $library->getId(), $request->request->get('_csrf_token')))
        {

            $fileHandler->handle(null, $library->getImage(), null, 'libraries');
            $fileHandler->handle(null, $library->getPdf(), null, 'libraries-pdfs');

            $previousSiblingsId = $library->getBase();
            $previousSiblings = $libraryRepository->findBy(['base' => $previousSiblingsId]);
            $orderer->reorderNumber($library->getId(), $library->getNumero(), -1, $previousSiblings, []);

            $unlocker->forget($library);
            $this->getDoctrine()->getManager()->remove($library);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La Bibliothèque a bien été supprimée');    
        }

        return $this->redirectToRoute('admin_library');
    }
}