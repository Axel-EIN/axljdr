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
use App\Service\Unlocker;

class AdminArchiveController extends AbstractController
{

    /**
     * @Route("/admin/archive", name="admin_archive")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminArchives(ArchiveRepository $archiveRepository): Response
    {
        $archives = $archiveRepository->findBy(array(), array('id' => 'DESC'));

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $archives,
            'element' => 'archive',
            'label' => 'Archive',
            'labels' => "Archives",
            'gender' => 'F',
            'article' => 'une',
            'table_cols' => [
                'image:Image:image:NA_ARCHIVE',
                'titre:Titre::bold',
                'auteur:Auteur',
                'contenu:Texte:bool',
                'access:Accès:access',
                'publishedAt:Publié le:date',
            ],
        ]);
    }

    /**
     * @Route("/admin/archive/create", name="admin_archive_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addArchive(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, Unlocker $unlocker) {

        $archive = new Archive;
        $form = $this->createForm(AdminArchiveType::class, $archive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = 'archive-' . $archive->getTitre() . '-image';
                $archive->setImage($fileHandler->handle($newImage, null, $prefix, 'archives', 'landscape720'));
            }

            $em->persist($archive);
            $em->flush();

            $unlocker->sync($archive, $form->get('unlockedBy')->getData());
            $em->flush();
            $this->addFlash('success', 'L\'Archive a bien été ajoutée.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'archive')
                return $this->redirectToRoute('world_archive', ['id' => $archive->getId()]);

            return $this->redirectToRoute('admin_archive');
        }

        return $this->render('back_office/create.html.twig', [
            'type' => 'create',
            'entity' => 'archive',
            'label' => 'Archive',
            'gender' => 'F',
            'article' => 'une',
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/admin/archive/{id}/edit", name="admin_archive_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editArchive(Request $request, Archive $archive, FileHandler $fileHandler, Unlocker $unlocker): Response {

        $form = $this->createForm(AdminArchiveType::class, $archive);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($archive));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = 'archive-' . $archive->getTitre() . '-image';
                $archive->setImage($fileHandler->handle($newImage, $archive->getImage(), $prefix, 'archives', 'landscape720'));
            } elseif ($request->request->get('remove_image') === '1' && $archive->getImage()) {
                $fileHandler->handle(null, $archive->getImage(), null, 'archives');
                $archive->setImage(null);
            }

            $unlocker->sync($archive, $form->get('unlockedBy')->getData());

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'L\'Archive a bien été modifiée.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'archive')
                return $this->redirectToRoute('world_archive', ['id' => $archive->getId()]);
            
            return $this->redirectToRoute('admin_archive');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'edit',
            'archive' => $archive,
            'entity' => 'archive',
            'label' => 'Archive',
            'gender' => 'F',
            'article' => 'une',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/archive/{id}/delete", name="admin_archive_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteArchive(Request $request, Archive $archive, FileHandler $fileHandler, Unlocker $unlocker): Response {

        if ($this->isCsrfTokenValid('delete' . $archive->getId(), $request->request->get('_csrf_token'))) {

            $entityManager = $this->getDoctrine()->getManager();

            $fileHandler->handle(null, $archive->getImage(), null, 'archives');

            $unlocker->forget($archive);
            $entityManager->remove($archive);
            $entityManager->flush();
            $this->addFlash('success', 'L\'archive a bien été supprimée.');
        }

        if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'world')
            return $this->redirectToRoute('world');
        
        return $this->redirectToRoute('admin_archive');
    }
}