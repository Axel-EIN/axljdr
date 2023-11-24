<?php

namespace App\Controller;

use App\Entity\Objet;
use App\Service\Numeroteur;
use App\Form\AdminObjetType;
use App\Service\FileHandler;
use App\Repository\ObjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminObjetController extends AbstractController
{
    /**
     * @Route("/admin/objet", name="admin_objet")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminObjets( ObjetRepository $objetRepository ): Response
    {
        $objets = $objetRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $objets,
            'element' => 'objet',
            'label' => 'Objet',
            'labels' => 'Objets',
            'genre' => 'M',
            'determinant' => 'un',
            'img_size' => '48',
            'extra_col1' => 'categorie',
            'extra_col2' => 'type',
            'extra_col3' => 'numero',
        ]);
    }

    /**
     * @Route("/admin/objet/create", name="admin_objet_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addObjet(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, ObjetRepository $objetRepository, Numeroteur $numeroteur) {

        $objet = new Objet;

        // URL PARAM PRE-FILL
        if ( !empty($request->query->get('tab')) )
                $objet->setType($request->query->get('tab'));

        $form = $this->createForm(AdminObjetType::class, $objet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Image
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'objet-' . $objet->getNom() . '-image';
                $objet->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'objets'));
            }

            // GET NUMERO
            $formNumero = $form->get('numero')->getData();
            $fratrieArrivee = $objetRepository->findBy(['type' => $form->get('type')->getData()]);

            if ( empty($formNumero) || !is_numeric($formNumero) || $formNumero < 1 )
                $objet->setNumero( count($fratrieArrivee) + 1 );
            else
                $objet->setNumero( $formNumero );

            $em->persist($objet);
            $em->flush();
            $this->addFlash('success', "L'Objet a bien été ajouté.");

            // RE-ORDER
            $numeroteur->reordonnerNumero( $objet->getId() , -1 , $objet->getNumero() , [] , $fratrieArrivee );

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'regles_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $objet->getCategorie() ] );

            return $this->redirectToRoute('admin_objet');
        }
        
        // RENDER
        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'objet',
            'label' => 'Objet',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/objet/{id}/edit", name="admin_objet_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editObjet(Request $request, Objet $objet, FileHandler $fileHandler, ObjetRepository $objetRepository, Numeroteur $numeroteur): Response {

        // Saving numbler and ID for re-ordering later
        if ( empty( $objet->getNumero() ) || !is_numeric( $objet->getNumero() ) || $objet->getNumero() < 0)
            $numeroDepart = -1;
        else
            $numeroDepart = $objet->getNumero();

        $fratrieDepartId = $objet->getType();

        $form = $this->createForm(AdminObjetType::class, $objet);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // Image Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'objet-' . $objet->getNom() . '-image';
                $objet->setImage($fileHandler->handle($nouvelleImage, $objet->getImage(), $prefix, 'objets'));
            }

            // FIX NEGATIVE NUMBER
            if ($form->get('numero')->getData() < 0 )
                $objet->setNumero(1);

            // RE-ORDERING : if number has changed or if parent has changed
            if ($numeroDepart != $objet->getNumero() || $fratrieDepartId != $objet->getType())
            {
                $fratrieDepart = $objetRepository->findBy(['type' => $fratrieDepartId]);
                $fratrieArrivee = $objetRepository->findBy(['type' => $objet->getType()]);
                $numeroteur->reordonnerNumero($objet->getId(), $numeroDepart, $objet->getNumero(), $fratrieDepart, $fratrieArrivee);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', "L'objet a bien été modifié.");

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'regles_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $objet->getCategorie() ] );

            return $this->redirectToRoute('admin_objet');
        }

        // RENDER
        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'objet' => $objet,
            'entity' => 'objet',
            'label' => 'Objet',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/objet/{id}/delete", name="admin_objet_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteObjet(Request $request, Objet $objet, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $objet->getId(), $request->query->get('csrf'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $fileHandler->handle(null, $objet->getImage(), null, 'objets');

            $entityManager->remove($objet);
            $entityManager->flush();
            $this->addFlash('success', "L'objet a bien été supprimé.");
        }

        return $this->redirectToRoute('admin_objet');
    }
}
