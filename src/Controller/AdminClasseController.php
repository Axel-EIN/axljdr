<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Service\FileHandler;
use App\Form\AdminClasseType;
use App\Repository\ClasseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminClasseController extends AbstractController
{
    /**
     * @Route("/admin/classe", name="admin_classe")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminClasses(ClasseRepository $classeRepository): Response
    {
        $classes = $classeRepository->findBy(array(), array('id' => 'DESC'));
        return $this->render('admin_classe/index.html.twig', [
            'classes' => $classes
        ]);
    }

    /**
     * @Route("/admin/classe/create", name="admin_classe_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addClasse(Request $request, EntityManagerInterface $em, FileHandler $fileHandler) {

        $classe = new Classe;
        $form = $this->createForm(AdminClasseType::class, $classe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Icon Handling
            $nouvelleIcone = $form->get('icone')->getData();
            if (!empty($nouvelleIcone)) {
                $prefix = 'classe-' . $classe->getNom() . '-icon';
                $classe->setIcone($fileHandler->handle($nouvelleIcone, null, $prefix, 'classes'));
            }

            // Image Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'classe-' . $classe->getNom() . '-image';
                $classe->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'classes'));
            }

            $em->persist($classe);
            $em->flush();
            $this->addFlash('success', 'La classe a bien été ajoutée.');

            return $this->redirectToRoute('admin_classe');
        }
        
        return $this->render('admin_classe/create.html.twig', [
            'type' => 'Créer',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/classe/{id}/edit", name="admin_classe_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editClasse(Request $request, Classe $classe, FileHandler $fileHandler): Response {

        $form = $this->createForm(AdminClasseType::class, $classe);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // Icon Handling
            $nouvelleIcone = $form->get('icone')->getData();
            if (!empty($nouvelleIcone)) {
                $prefix = 'classe-' . $classe->getNom() . '-icon';
                $classe->setIcone($fileHandler->handle($nouvelleIcone, $classe->getIcone(), $prefix, 'classes'));
            }

            // Image Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'classe-' . $classe->getNom() . '-image';
                $classe->setImage($fileHandler->handle($nouvelleImage, $classe->getImage(), $prefix, 'classes'));
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La classe a bien été modifiée.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'classe')
                return $this->redirectToRoute('regles_classe', ['id' => $classe->getId()]);

            return $this->redirectToRoute('admin_classe');
        }

        return $this->renderForm('admin_classe/edit.html.twig', [
            'classe' => $classe,
            'form' => $form,
            'type' => 'Modifier',
        ]);
        
    }

    /**
     * @Route("/admin/classe/{id}/delete", name="admin_classe_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteClasse(Request $request, Classe $classe, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $classe->getId(), $request->query->get('csrf'))) {

            if (!$classe->getEcoles()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les écoles enfants au prélable !');
                return $this->redirectToRoute('admin_classe');
            }

            $entityManager = $this->getDoctrine()->getManager();

            // Files Handling
            $fileHandler->handle(null, $classe->getIcone(), null, 'classes');
            $fileHandler->handle(null, $classe->getImage(), null, 'classes');

            $entityManager->remove($classe);
            $entityManager->flush();
            $this->addFlash('success', 'La classe a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_classe');
    }
}