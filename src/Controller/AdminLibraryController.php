<?php

namespace App\Controller;

use App\Entity\Library;
use App\Service\Numeroteur;
use App\Service\FileHandler;
use App\Form\AdminLibraryType;
use App\Repository\LibraryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminLibraryController extends AbstractController
{
    /**
     * @Route("/admin/library", name="admin_library")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminLibraries(LibraryRepository $libraryRepository): Response
    {
        $libraries = $libraryRepository->findAll();

        return $this->render('admin_library/index.html.twig', [
            'elements' => $libraries,
            'label' => 'Bibliothèque',
            'labels' => "Bibliothèques",
            'genre' => 'F',
            'determinant' => 'une',
            'element' => 'library'
        ]);
    }

    /**
     * @Route("/admin/library/create", name="admin_library_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addLibrary(Request $request, FileHandler $fileHandler, EntityManagerInterface $em, LibraryRepository $libraryRepository, Numeroteur $numeroteur)
    {
        $library = new Library;
        $form = $this->createForm(AdminLibraryType::class, $library);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            // IMAGE file handling
            $image = $form->get('image')->getData();
            if (!empty($image)) {
                $prefix = 'library-' . $library->getNom() . '-image';
                $library->setImage($fileHandler->handle($image, null, $prefix, 'libraries'));
            }

            // PDF file handling
            $pdf = $form->get('pdf')->getData();
            if (!empty($pdf)) {
                $prefix = 'library-' . $library->getNom() . '-pdf';
                $library->setPdf($fileHandler->handle($pdf, null, $prefix, 'libraries-pdfs'));
            }

            $em->persist($library);
            $em->flush();
            $this->addFlash('success', 'La Bibliothèque a bien été ajoutée');

            // RE-ORDERING
            $fratrieArrivee = $libraryRepository->findBy(['base' => $library->getBase()]);
            $numeroteur->reordonnerNumero($library->getId(), -1, $library->getNumero(), [], $fratrieArrivee);

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library')
                return $this->redirectToRoute('regles_library', ['id' => $library->getId()]);

            return $this->redirectToRoute('admin_library');
        }

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'label' => 'Bibliothèque',
            'genre' => 'F',
            'determinant' => 'une',
            'entity' => 'library',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/library/{id}/edit", name="admin_library_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editLibrary(Request $request, Library $library, FileHandler $fileHandler, LibraryRepository $libraryRepository, Numeroteur $numeroteur): Response
    {
        // Saving numbler and ID for re-ordering later
        $numeroDepart = $library->getNumero();
        $fratrieDepartId = $library->getBase();

        $form = $this->createForm(AdminLibraryType::class, $library);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            // IMAGE File Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'library-' . $library->getNom() . '-image';
                $library->setImage($fileHandler->handle($nouvelleImage, $library->getImage(), $prefix, 'libraries'));
            }

            // PDF File Handling
            $nouveauPDF = $form->get('pdf')->getData();
            if (!empty($nouveauPDF)) {
                $prefix = 'library-' . $library->getNom() . '-pdf';
                $library->setPdf($fileHandler->handle($nouveauPDF, $library->getPdf(), $prefix, 'libraries-pdfs'));
            }

            // RE-ORDERING : if number has changed or if parent has changed
            if ($numeroDepart != $library->getNumero() || $fratrieDepartId != $library->getBase())
            {
                $fratrieDepart = $libraryRepository->findBy(['base' => $fratrieDepartId]);
                $fratrieArrivee = $libraryRepository->findBy(['base' => $library->getBase()]);
                $numeroteur->reordonnerNumero($library->getId(), $numeroDepart, $library->getNumero(), $fratrieDepart, $fratrieArrivee);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La Bibliothèque a bien été modifiée');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library')
                return $this->redirectToRoute('regles_library', ['id' => $library->getId()]);

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
     * @Route("/admin/library/{id}/delete", name="admin_library_delete")
     * @IsGranted("ROLE_MJ")
     */
    public function deleteLibrary(Request $request, Library $library, FileHandler $fileHandler, LibraryRepository $libraryRepository, Numeroteur $numeroteur): Response
    {
        if ( $this->isCsrfTokenValid('delete' . $library->getId(), $request->query->get('csrf')))
        {
            // File Image and PDF handling
            $fileHandler->handle(null, $library->getImage(), null, 'librarys');
            $fileHandler->handle(null, $library->getPdf(), null, 'librarys-pdfs');

            // RE-ORDERING
            $fratrieDepartId = $library->getBase();
            $fratrieDepart = $libraryRepository->findBy(['base' => $fratrieDepartId]);
            $numeroteur->reordonnerNumero($library->getId(), $library->getNumero(), -1, $fratrieDepart, []);

            $this->getDoctrine()->getManager()->remove($library);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La Bibliothèque a bien été supprimée');    
        }

        return $this->redirectToRoute('admin_library');
    }
}