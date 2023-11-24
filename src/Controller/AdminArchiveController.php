<?php

namespace App\Controller;

use App\Entity\Archive;
use App\Service\FileHandler;
use App\Form\AdminArchiveType;
use App\Repository\ArchiveRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminArchiveController extends AbstractController
{

    /**
     * @Route("/admin/archive", name="admin_archive")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminArchives(ArchiveRepository $archiveRepository): Response {
        $archives = $archiveRepository->findAll();
        return $this->render('admin_archive/index.html.twig', [
            'archives' => $archives
        ]);
    }

    /**
     * @Route("/admin/archive/create", name="admin_archive_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addArchive(Request $request, EntityManagerInterface $em, FileHandler $fileHandler) {

        $archive = new Archive;
        $form = $this->createForm(AdminArchiveType::class, $archive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // IMAGE FILE HANDLING
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'archive-' . $archive->getTitre() . '-image';
                $archive->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'archives'));
            }

            $em->persist($archive);
            $em->flush();
            $this->addFlash('success', 'L\'Archive a bien été ajoutée.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'archive')
                return $this->redirectToRoute('empire_archive', ['id' => $archive->getId()]);

            return $this->redirectToRoute('admin_archive');
        }

        return $this->render('admin_archive/create.html.twig', [
            'type' => 'Créer',
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/admin/archive/{id}/edit", name="admin_archive_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editArchive(Request $request, Archive $archive, FileHandler $fileHandler): Response {

        $form = $this->createForm(AdminArchiveType::class, $archive);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // IMAGE FILE HANDLING
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'archive-' . $archive->getTitre() . '-image';
                $archive->setImage($fileHandler->handle($nouvelleImage, $archive->getImage(), $prefix, 'archives'));
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'L\'Archive a bien été modifiée.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'archive')
                return $this->redirectToRoute('empire_archive', ['id' => $archive->getId()]);
            
            return $this->redirectToRoute('admin_archive');
        }

        return $this->renderForm('admin_archive/edit.html.twig', [
            'archive' => $archive,
            'form' => $form,
            'type' => 'Modifier',
        ]);
        
    }

    /**
     * @Route("/admin/archive/{id}/delete", name="admin_archive_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteArchive(Request $request, Archive $archive, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $archive->getId(), $request->query->get('csrf'))) {

            $entityManager = $this->getDoctrine()->getManager();

            // IMAGE FILE HANDLING
            $fileHandler->handle(null, $archive->getImage(), null, 'archives');

            $entityManager->remove($archive);
            $entityManager->flush();
            $this->addFlash('success', 'L\'archive a bien été supprimée.');
        }

        // REDIRECTION
        if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'archive')
            return $this->redirectToRoute('empire');
        
        return $this->redirectToRoute('admin_archive');
    }
}