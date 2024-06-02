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

class AdminLieuController extends AbstractController
{
    /**
     * @Route("/admin/lieu", name="admin_lieu")
     */
    public function afficherAdminLieux(LieuRepository $lieuRepository): Response {
        $lieux = $lieuRepository->findAll();
        return $this->render('admin_lieu/index.html.twig', [
            'lieux' => $lieux
        ]);
    }

    /**
     * @Route("/admin/lieu/create", name="admin_lieu_create")
     * @IsGranted("ROLE_MJ")
     */
    public function ajouterLieu(Request $request, EntityManagerInterface $em, FileHandler $fileHandler) {

        $lieu = new Lieu;
        $form = $this->createForm(AdminLieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-image';
                $lieu->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'lieux'));
            } else { $lieu->setImage('assets/img/placeholders/1280x720.png'); }

            $nouvelleCarte = $form->get('carte')->getData();
            if (!empty($nouvelleCarte)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-carte';
                $lieu->setCarte($fileHandler->handle($nouvelleCarte, null, $prefix, 'lieux'));
            } else { $lieu->setCarte('assets/img/placeholders/1280x720.png'); }

            $nouvelleRegion = $form->get('region')->getData();
            if (!empty($nouvelleRegion)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-region';
                $lieu->setRegion($fileHandler->handle($nouvelleRegion, null, $prefix, 'lieux'));
            } else { $lieu->setRegion('assets/img/placeholders/1280x720.png'); }

            $em->persist($lieu);
            $em->flush();

            $this->addFlash('success', 'Le Lieu a bien été ajouté.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'lieu')
                return $this->redirectToRoute('empire_lieu', ['id' => $lieu->getId()]);
            return $this->redirectToRoute('admin_lieu');
        } else {
            return $this->render('admin_lieu/create.html.twig', [
                'type' => 'Créer',
                'form' => $form->createView()
            ]);
        }
    }

    /**
     * @Route("/admin/lieu/{id}/edit", name="admin_lieu_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editerLieu(Request $request, Lieu $lieu, FileHandler $fileHandler): Response {

        $form = $this->createForm(AdminLieuType::class, $lieu);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $nouvelleImage = $form->get('image')->getData();
            $nouvelleCarte = $form->get('carte')->getData();
            $nouvelleRegion = $form->get('region')->getData();


            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-image';
                $lieu->setImage($fileHandler->handle($nouvelleImage, $lieu->getImage(), $prefix, 'lieux'));
            }

            $nouvelleCarte = $form->get('carte')->getData();
            if (!empty($nouvelleCarte)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-carte';
                $lieu->setCarte($fileHandler->handle($nouvelleCarte, $lieu->getCarte(), $prefix, 'lieux'));
            }

            $nouvelleRegion = $form->get('region')->getData();
            if (!empty($nouvelleRegion)) {
                $prefix = 'lieu-' . $lieu->getNom() . '-region';
                $lieu->setRegion($fileHandler->handle($nouvelleRegion, $lieu->getRegion(), $prefix, 'lieux'));
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le Lieu a bien été modifié.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'lieu')
                return $this->redirectToRoute('empire_lieu', ['id' => $lieu->getId()]);
            return $this->redirectToRoute('admin_lieu');
        }

        return $this->renderForm('admin_lieu/edit.html.twig', [
            'lieu' => $lieu,
            'form' => $form,
            'type' => 'Modifier',
        ]);
        
    }

    /**
     * @Route("/admin/lieu/{id}/delete", name="admin_lieu_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function supprimerLieu(Request $request, Lieu $lieu, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $lieu->getId(), $request->query->get('csrf'))) {

            $entityManager = $this->getDoctrine()->getManager();

            $fileHandler->handle(null, $lieu->getImage(), null, 'lieux');
            $fileHandler->handle(null, $lieu->getCarte(), null, 'lieux');
            $fileHandler->handle(null, $lieu->getRegion(), null, 'lieux');

            $entityManager->remove($lieu);
            $entityManager->flush();

            $this->addFlash('success', 'Le Lieu a bien été supprimé.');
        }

        return $this->redirectToRoute('admin_lieu');
    }
}
