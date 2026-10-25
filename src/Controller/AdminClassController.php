<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Service\FileHandler;
use App\Form\AdminClassType;
use App\Repository\ClasseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminClassController extends AbstractController
{
    /**
     * @Route("/admin/class", name="admin_class")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminClasses(ClasseRepository $classRepository): Response
    {
        $classes = $classRepository->findBy(array(), array('id' => 'DESC'));

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $classes,
            'element' => 'class',
            'label' => 'Classe',
            'labels' => 'Classes',
            'genre' => 'F',
            'determinant' => 'une',
            'table_cols' => [
                'icone:Icone:symbol:NA_CLASS_ICO',
                'nom:Nom::bold',
                'image:Image:image:NA_CLASS',
                'couleur:Couleur:color',
                'citation:Citation:bool',
                'description:Description:bool',
            ],
        ]);
    }

    /**
     * @Route("/admin/class/create", name="admin_class_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addClass(Request $request, EntityManagerInterface $em, FileHandler $fileHandler) {

        $class = new Classe;
        $form = $this->createForm(AdminClassType::class, $class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newIcon = $form->get('icone')->getData();
            if (!empty($newIcon)) {
                $prefix = 'classe-' . $class->getNom() . '-icon';
                $class->setIcone($fileHandler->handle($newIcon, null, $prefix, 'classes', 'square320'));
            }

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = 'classe-' . $class->getNom() . '-image';
                $class->setImage($fileHandler->handle($newImage, null, $prefix, 'classes', 'landscape720'));
            }

            $em->persist($class);
            $em->flush();
            $this->addFlash('success', 'La classe a bien été ajoutée.');

            return $this->redirectToRoute('admin_class');
        }
        

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'class',
            'label' => 'Classe',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/class/{id}/edit", name="admin_class_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editClass(Request $request, Classe $class, FileHandler $fileHandler): Response {

        $form = $this->createForm(AdminClassType::class, $class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $newIcon = $form->get('icone')->getData();
            if (!empty($newIcon)) {
                $prefix = 'classe-' . $class->getNom() . '-icon';
                $class->setIcone($fileHandler->handle($newIcon, $class->getIcone(), $prefix, 'classes', 'square320'));
            } elseif ($request->request->get('remove_icone') === '1' && $class->getIcone()) {
                $fileHandler->handle(null, $class->getIcone(), null, 'classes');
                $class->setIcone(null);
            }

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = 'classe-' . $class->getNom() . '-image';
                $class->setImage($fileHandler->handle($newImage, $class->getImage(), $prefix, 'classes', 'landscape720'));
            } elseif ($request->request->get('remove_image') === '1' && $class->getImage()) {
                $fileHandler->handle(null, $class->getImage(), null, 'classes');
                $class->setImage(null);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La classe a bien été modifiée.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'class')
                return $this->redirectToRoute('rules_class', ['id' => $class->getId()]);

            return $this->redirectToRoute('admin_class');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'class' => $class,
            'entity' => 'class',
            'label' => 'Classe',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/class/{id}/delete", name="admin_class_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteClass(Request $request, Classe $class, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $class->getId(), $request->request->get('_csrf_token'))) {

            if (!$class->getEcoles()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les écoles enfants au prélable !');
                return $this->redirectToRoute('admin_class');
            }

            $entityManager = $this->getDoctrine()->getManager();

            $fileHandler->handle(null, $class->getIcone(), null, 'classes');
            $fileHandler->handle(null, $class->getImage(), null, 'classes');

            $entityManager->remove($class);
            $entityManager->flush();
            $this->addFlash('success', 'La classe a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_class');
    }
}