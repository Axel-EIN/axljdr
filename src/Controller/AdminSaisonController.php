<?php

namespace App\Controller;

use App\Entity\Saison;
use App\Service\FileHandler;
use App\Service\Numeroteur;
use App\Form\AdminSaisonType;
use App\Repository\SaisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminSaisonController extends AbstractController
{
    /**
     * @Route("/admin/saison", name="admin_saison")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminSaisons(SaisonRepository $saisonRepository): Response
    {
        $saisons = $saisonRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $saisons,
            'element' => 'saison',
            'label' => 'Saison',
            'labels' => 'Saisons',
            'genre' => 'F',
            'determinant' => 'une',
            'img_size' => '320',
            'extra_col1' => 'numero',
            'extra_col2' => 'couleur',
        ]);
    }

    /**
     * @Route("/admin/saison/create", name="admin_saison_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addSaison(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, SaisonRepository $saisonRepository, Numeroteur $numeroteur) {

        $saison = new Saison;

        // URL PARAMS PRE-FILL
        if (!empty($request->query->get('numero')) && $request->query->get('numero') > 0)
            $saison->setNumero($request->query->get('numero'));

        $form = $this->createForm(AdminSaisonType::class, $saison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // File Image Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'saison-' . $saison->getNumero();
                $saison->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'saisons'));
            }

            $em->persist($saison);
            $em->flush();
            $this->addFlash('success', 'La saison a bien été créee.');

            // RE-ORDERING
            $fratrieArrivee = $saisonRepository->findAll();
            $numeroteur->reordonnerNumero($saison->getId(), -1, $saison->getNumero(), [], $fratrieArrivee);

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'aventure')
                return $this->redirectToRoute('aventure_saison', ['id' => $saison->getId()]);

            return $this->redirectToRoute('admin_saison');

        }

        // RENDER
        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'saison',
            'label' => 'Saison',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/saison/{id}/edit", name="admin_saison_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editSaison(Request $request, Saison $saison, FileHandler $fileHandler, Numeroteur $numeroteur, SaisonRepository $saisonRepository): Response {

        // Saving Number and ID for later RE-Ordering
        $numeroDepart = $saison->getNumero();
        $fratrieDepart = $saisonRepository->findAll();

        $form = $this->createForm(AdminSaisonType::class, $saison);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // File Image Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'saison-' . $saison->getNumero();
                $saison->setImage($fileHandler->handle($nouvelleImage, $saison->getImage(), $prefix, 'saisons'));
            }

            // RE-ORDERING : if Number or Parent has changed
            if ($numeroDepart != $saison->getNumero())
            {
                $fratrieDepart = $saisonRepository->findAll();
                $fratrieArrivee = $saisonRepository->findAll();
                $numeroteur->reordonnerNumero($saison->getId(), $numeroDepart, $saison->getNumero(), $fratrieDepart, $fratrieArrivee);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La saison a bien été modifiée.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'aventure')
                return $this->redirectToRoute('aventure_saison', ['id' => $saison->getId()]);
            
            return $this->redirectToRoute('admin_saison');
        }

        // RENDER
        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'saison' => $saison,
            'entity' => 'saison',
            'label' => 'Saison',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form,
        ]); 
    }

    /**
     * @Route("/admin/saison/{id}/delete", name="admin_saison_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteSaison(Request $request, Saison $saison, Numeroteur $numeroteur, SaisonRepository $saisonRepository, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $saison->getId(), $request->query->get('csrf'))) {

            // CHECK if child exists
            if (!$saison->getChapitres()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les chapitres enfants au prélable !');
                return $this->redirectToRoute('admin_saison');
            }

            // File Image Handling
            $fileHandler->handle(null, $saison->getImage(), null, 'saisons');

            // RE-ORDERING
            $fratrieDepart = $saisonRepository->findAll();
            $numeroteur->reordonnerNumero($saison->getId(), $saison->getNumero(), -1, $fratrieDepart, []);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($saison);
            $entityManager->flush();
            $this->addFlash('success', 'La saison a bien été supprimée.');
        } else
            $this->addFlash('danger', 'Token de protection invalide !');

        // REDIRECTION
        if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'aventure')
            return $this->redirectToRoute('aventure');

        return $this->redirectToRoute('admin_saison');
    }
}