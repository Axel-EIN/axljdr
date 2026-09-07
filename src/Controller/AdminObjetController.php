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
use App\Service\Unlocker;

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
            'table_cols' => [
                'image:Image:image:NA_ICON',
                'nom:Nom::bold',
                'categorie:Catégorie',
                'type:Type',
                'numero:Ordre:number',
                'prix:Prix:number',
                'description:Text:bool',
                'regles:Rule:bool',
                'taille:Taille',
                'poids:Poids',
                'vd:VD',
                'reduction:Reduc',
                'NDarmure:ND',
                'forceArc:F(arc)',
                'access:Accès:access',
                'publishedAt:Publié le:date',
            ],
        ]);
    }

    /**
     * @Route("/admin/objet/create", name="admin_objet_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addObjet(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, ObjetRepository $objetRepository, Numeroteur $numeroteur, Unlocker $unlocker) {

        $objet = new Objet;

        if ( !empty($request->query->get('tab')) )
                $objet->setType($request->query->get('tab'));

        $form = $this->createForm(AdminObjetType::class, $objet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'objet-' . $objet->getNom() . '-image';
                $objet->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'objets'));
            }

            $formNumero = $form->get('numero')->getData();
            $fratrieArrivee = $objetRepository->findBy(['type' => $form->get('type')->getData()]);

            if ( empty($formNumero) || !is_numeric($formNumero) || $formNumero < 1 )
                $objet->setNumero( count($fratrieArrivee) + 1 );
            else
                $objet->setNumero( $formNumero );

            $em->persist($objet);
            $em->flush();

            $unlocker->sync($objet, $form->get('unlockedBy')->getData());
            $em->flush();
            $this->addFlash('success', "L'Objet a bien été ajouté.");

            $numeroteur->reordonnerNumero( $objet->getId() , -1 , $objet->getNumero() , [] , $fratrieArrivee );

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'regles_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $objet->getCategorie() ] );

            return $this->redirectToRoute('admin_objet');
        }
        

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
    public function editObjet(Request $request, Objet $objet, FileHandler $fileHandler, ObjetRepository $objetRepository, Numeroteur $numeroteur, Unlocker $unlocker): Response {

        if ( empty( $objet->getNumero() ) || !is_numeric( $objet->getNumero() ) || $objet->getNumero() < 0)
            $numeroDepart = -1;
        else
            $numeroDepart = $objet->getNumero();

        $fratrieDepartId = $objet->getType();

        $form = $this->createForm(AdminObjetType::class, $objet);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($objet));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'objet-' . $objet->getNom() . '-image';
                $objet->setImage($fileHandler->handle($nouvelleImage, $objet->getImage(), $prefix, 'objets'));
            } elseif ($request->request->get('remove_image') === '1' && $objet->getImage()) {
                $fileHandler->handle(null, $objet->getImage(), null, 'objets');
                $objet->setImage(null);
            }

            if ($form->get('numero')->getData() < 1)
                $objet->setNumero(1);

            if ($numeroDepart != $objet->getNumero() || $fratrieDepartId != $objet->getType())
            {
                $fratrieDepart = $objetRepository->findBy(['type' => $fratrieDepartId]);
                $fratrieArrivee = $objetRepository->findBy(['type' => $objet->getType()]);
                $numeroteur->reordonnerNumero($objet->getId(), $numeroDepart, $objet->getNumero(), $fratrieDepart, $fratrieArrivee);
            }

            $unlocker->sync($objet, $form->get('unlockedBy')->getData());

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', "L'objet a bien été modifié.");

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'regles_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $objet->getCategorie() ] );

            return $this->redirectToRoute('admin_objet');
        }

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
     * @Route("/admin/objet/{id}/delete", name="admin_objet_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteObjet(Request $request, Objet $objet, FileHandler $fileHandler, Unlocker $unlocker): Response {

        if ($this->isCsrfTokenValid('delete' . $objet->getId(), $request->request->get('_csrf_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $fileHandler->handle(null, $objet->getImage(), null, 'objets');

            $unlocker->forget($objet);
            $entityManager->remove($objet);
            $entityManager->flush();
            $this->addFlash('success', "L'objet a bien été supprimé.");
        }

        return $this->redirectToRoute('admin_objet');
    }
}
