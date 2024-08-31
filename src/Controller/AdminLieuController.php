<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Service\FileHandler;
use App\Form\AdminLieuType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\Baliseur;

class AdminLieuController extends AbstractController
{
    /**
     * @Route("/admin/lieu", name="admin_lieu")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminLieux(LieuRepository $lieuRepository): Response {
        $lieux = $lieuRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $lieux,
            'element' => 'lieu',
            'label' => 'Lieu',
            'labels' => 'Lieux',
            'genre' => 'M',
            'determinant' => 'un',
            'img_size' => '96'
        ]);
    }

    /**
     * @Route("/admin/lieu/create", name="admin_lieu_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addLieu(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, Baliseur $baliseur) {

        $lieu = new Lieu;
        $form = $this->createForm(AdminLieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // File Image Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-image';
                $lieu->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'lieux'));
            }

            // File Intérior Plan Image Handling
            $nouvelleCarte = $form->get('carte')->getData();
            if (!empty($nouvelleCarte)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-plan';
                $lieu->setCarte($fileHandler->handle($nouvelleCarte, null, $prefix, 'lieux'));
            }

            // File Region Map Image Handling
            $nouvelleRegion = $form->get('region')->getData();
            if (!empty($nouvelleRegion)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-map';
                $lieu->setRegion($fileHandler->handle($nouvelleRegion, null, $prefix, 'lieux'));
            }

            // File Icon Handling
            $nouvelleIcone = $form->get('icone')->getData();
            if (!empty($nouvelleIcone)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-icone';
                $lieu->setIcone($fileHandler->handle($nouvelleIcone, null, $prefix, 'lieux'));
            }

            // CHARACTER TAGGER : capture words between [], check if character exist, replace by a link
            $lieu->setDescription($baliseur->baliserPersonnages($lieu->getDescription()));

            // LOCATION TAGGER: capture words between {}, check if location exist, replace by a link
            $lieu->setDescription($baliseur->baliserLieux($lieu->getDescription()));

            $em->persist($lieu);
            $em->flush();
            $this->addFlash('success', 'Le Lieu a bien été ajouté.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'lieu')
                return $this->redirectToRoute('empire_lieu', ['id' => $lieu->getId()]);

            return $this->redirectToRoute('admin_lieu');
        }
        
        // RENDER
        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'lieu',
            'label' => 'Lieu',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/lieu/{id}/edit", name="admin_lieu_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editLieu(Request $request, Lieu $lieu, FileHandler $fileHandler, Baliseur $baliseur): Response {

        // CHARACTER UNTAGGER : capture words in character-links, check if character exist and replace with []
        $lieu->setDescription($baliseur->debaliserPersonnages($lieu->getDescription()));

        // LOCATION UNTAGGER : capture words in location-links, check if location exist and replace with {}
        $lieu->setDescription($baliseur->debaliserLieux($lieu->getDescription()));

        $form = $this->createForm(AdminLieuType::class, $lieu);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // File Image Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-image';
                $lieu->setImage($fileHandler->handle($nouvelleImage, $lieu->getImage(), $prefix, 'lieux'));
            }

            // File Map Image Handling
            $nouvelleCarte = $form->get('carte')->getData();
            if (!empty($nouvelleCarte)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-carte';
                $lieu->setCarte($fileHandler->handle($nouvelleCarte, $lieu->getCarte(), $prefix, 'lieux'));
            }

            // File Region Image Handling
            $nouvelleRegion = $form->get('region')->getData();
            if (!empty($nouvelleRegion)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-region';
                $lieu->setRegion($fileHandler->handle($nouvelleRegion, $lieu->getRegion(), $prefix, 'lieux'));
            }

            // File Icon Image Handling
            $nouvelleIcone = $form->get('icone')->getData();
            if (!empty($nouvelleIcone)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-icone';
                $lieu->setIcone($fileHandler->handle($nouvelleIcone, $lieu->getIcone(), $prefix, 'lieux'));
            }

            // CHARACTER TAGGER : capture words between [], check if character exist, replace by a link
            $lieu->setDescription($baliseur->baliserPersonnages($lieu->getDescription()));

            // LOCATION TAGGER: capture words between {}, check if location exist, replace by a link
            $lieu->setDescription($baliseur->baliserLieux($lieu->getDescription()));

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le Lieu a bien été modifié.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'lieu')
                return $this->redirectToRoute('empire_lieu', ['id' => $lieu->getId()]);

            return $this->redirectToRoute('admin_lieu');
        }

        // RENDER
        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'lieu' => $lieu,
            'entity' => 'lieu',
            'label' => 'Lieu',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/lieu/{id}/delete", name="admin_lieu_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteLieu(Request $request, Lieu $lieu, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $lieu->getId(), $request->query->get('csrf'))) {

            $entityManager = $this->getDoctrine()->getManager();

            // Files Images Handling
            $fileHandler->handle(null, $lieu->getImage(), null, 'lieux');
            $fileHandler->handle(null, $lieu->getCarte(), null, 'lieux');
            $fileHandler->handle(null, $lieu->getRegion(), null, 'lieux');
            $fileHandler->handle(null, $lieu->getIcone(), null, 'lieux');

            $entityManager->remove($lieu);
            $entityManager->flush();
            $this->addFlash('success', 'Le Lieu a bien été supprimé.');
        }

        return $this->redirectToRoute('admin_lieu');
    }
}