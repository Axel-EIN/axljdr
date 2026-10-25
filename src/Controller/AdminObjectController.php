<?php

namespace App\Controller;

use App\Service\TableFilter;
use App\Entity\Objet;
use App\Service\Orderer;
use App\Form\AdminObjectType;
use App\Service\FileHandler;
use App\Repository\ObjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\Unlocker;

class AdminObjectController extends AbstractController
{
    private const TABLE_FILTERS = [
        'categorie:Catégorie',
    ];

    /**
     * @Route("/admin/object", name="admin_object")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminObjects( ObjetRepository $objectRepository ): Response
    {
        $objects = $objectRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $objects,
            'element' => 'object',
            'label' => 'Objet',
            'labels' => 'Objets',
            'gender' => 'M',
            'article' => 'un',
            'table_filters' => self::TABLE_FILTERS,
            'table_cols' => [
                'icon:Icône:symbol:NA_ICON',
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
     * @Route("/admin/object/create", name="admin_object_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addObject(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, ObjetRepository $objectRepository, Orderer $orderer, Unlocker $unlocker, TableFilter $tableFilter) {

        $object = new Objet;
        $tableFilter->prefill($object, self::TABLE_FILTERS, $request->query->all('filter'));

        if ( !empty($request->query->get('tab')) )
                $object->setType($request->query->get('tab'));

        $form = $this->createForm(AdminObjectType::class, $object);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newIcon = $form->get('icon')->getData();
            if (!empty($newIcon)) {
                $prefix = 'objet-' . $object->getNom() . '-icon';
                $object->setIcon($fileHandler->handle($newIcon, null, $prefix, 'objets', 'square320'));
            }

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = 'objet-' . $object->getNom() . '-illustration';
                $object->setImage($fileHandler->handle($newImage, null, $prefix, 'objets', 'landscape720'));
            }

            $formNumber = $form->get('numero')->getData();
            $newSiblings = $objectRepository->findBy(['type' => $form->get('type')->getData()]);

            if ( empty($formNumber) || !is_numeric($formNumber) || $formNumber < 1 )
                $object->setNumero( count($newSiblings) + 1 );
            else
                $object->setNumero( $formNumber );

            $em->persist($object);
            $em->flush();

            $unlocker->sync($object, $form->get('unlockedBy')->getData());
            $unlocker->syncAccess($object);
            $em->flush();
            $this->addFlash('success', "L'Objet a bien été ajouté.");

            $orderer->reorderNumber( $object->getId() , -1 , $object->getNumero() , [] , $newSiblings );

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'rules_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $object->getCategorie() ] );

            return $this->redirectToRoute('admin_object');
        }
        

        return $this->render('back_office/create.html.twig', [
            'type' => 'create',
            'entity' => 'object',
            'label' => 'Objet',
            'gender' => 'M',
            'article' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/object/{id}/edit", name="admin_object_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editObject(Request $request, Objet $object, FileHandler $fileHandler, ObjetRepository $objectRepository, Orderer $orderer, Unlocker $unlocker): Response {

        if ( empty( $object->getNumero() ) || !is_numeric( $object->getNumero() ) || $object->getNumero() < 0)
            $previousNumber = -1;
        else
            $previousNumber = $object->getNumero();

        $previousSiblingsId = $object->getType();

        $form = $this->createForm(AdminObjectType::class, $object);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($object));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $newIcon = $form->get('icon')->getData();
            if (!empty($newIcon)) {
                $prefix = 'objet-' . $object->getNom() . '-icon';
                $object->setIcon($fileHandler->handle($newIcon, $object->getIcon(), $prefix, 'objets', 'square320'));
            } elseif ($request->request->get('remove_icon') === '1' && $object->getIcon()) {
                $fileHandler->handle(null, $object->getIcon(), null, 'objets');
                $object->setIcon(null);
            }

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = 'objet-' . $object->getNom() . '-illustration';
                $object->setImage($fileHandler->handle($newImage, $object->getImage(), $prefix, 'objets', 'landscape720'));
            } elseif ($request->request->get('remove_image') === '1' && $object->getImage()) {
                $fileHandler->handle(null, $object->getImage(), null, 'objets');
                $object->setImage(null);
            }

            if ($form->get('numero')->getData() < 1)
                $object->setNumero(1);

            if ($previousNumber != $object->getNumero() || $previousSiblingsId != $object->getType())
            {
                $previousSiblings = $objectRepository->findBy(['type' => $previousSiblingsId]);
                $newSiblings = $objectRepository->findBy(['type' => $object->getType()]);
                $orderer->reorderNumber($object->getId(), $previousNumber, $object->getNumero(), $previousSiblings, $newSiblings);
            }

            $unlocker->sync($object, $form->get('unlockedBy')->getData());
            $unlocker->syncAccess($object);

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', "L'objet a bien été modifié.");

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'rules_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $object->getCategorie() ] );

            if ($request->query->get('redirect') === 'object')
                return $this->redirectToRoute('rules_object', ['id' => $object->getId()]);

            return $this->redirectToRoute('admin_object');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'edit',
            'object' => $object,
            'entity' => 'object',
            'label' => 'Objet',
            'gender' => 'M',
            'article' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/object/{id}/delete", name="admin_object_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteObject(Request $request, Objet $object, FileHandler $fileHandler, Unlocker $unlocker): Response {

        if ($this->isCsrfTokenValid('delete' . $object->getId(), $request->request->get('_csrf_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $fileHandler->handle(null, $object->getIcon(), null, 'objets');
            $fileHandler->handle(null, $object->getImage(), null, 'objets');

            $unlocker->forget($object);
            $entityManager->remove($object);
            $entityManager->flush();
            $this->addFlash('success', "L'objet a bien été supprimé.");
        }

        return $this->redirectToRoute('admin_object');
    }
}
