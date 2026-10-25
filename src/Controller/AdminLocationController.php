<?php

namespace App\Controller;

use App\Service\TableFilter;
use App\Entity\Lieu;
use App\Service\FileHandler;
use App\Form\AdminLocationType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\NameTagger;
use App\Service\Unlocker;

class AdminLocationController extends AbstractController
{
  private const TABLE_FILTERS = [
    'clan.nom:Clan',
  ];

  /**
   * @Route("/admin/location", name="admin_location")
   * @IsGranted("ROLE_MJ")
   */
  public function viewAdminLocations(LieuRepository $locationRepository): Response {
    $locations = $locationRepository->findBy( [] , ['id' => 'DESC'] );

    return $this->render('back_office/list-element.html.twig', [
      'elements' => $locations,
      'element' => 'location',
      'label' => 'Lieu',
      'labels' => 'Lieux',
      'genre' => 'M',
      'determinant' => 'un',
      'table_filters' => self::TABLE_FILTERS,
      'table_cols' => [
        'icone:Icone:symbol:NA_LIEU_ICO',
        'nom:Nom::bold',
        'surnom',
        'image:Image:image:NA_LIEU',
        'clan.nom:Clan',
        'type:Type',
        'population:Population:number',
        'description:Description:bool',
        'locX:LocX:number',
        'locY:LocY:number',
        'regionDesc:RégionText:bool',
        'planMap:Plan:bool',
        'planDesc:PlanText:bool',
        'access:Accès:access',
        'publishedAt:Publié le:date',
      ],
    ]);
  }

  /**
   * @Route("/admin/location/create", name="admin_location_create")
   * @IsGranted("ROLE_MJ")
   */
  public function addLocation(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, NameTagger $nameTagger, Unlocker $unlocker, TableFilter $tableFilter) {

    $location = new Lieu;
    $tableFilter->prefill($location, self::TABLE_FILTERS, $request->query->all('filter'));
    $form = $this->createForm(AdminLocationType::class, $location);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

      $newImage = $form->get('image')->getData();
      if (!empty($newImage)) {
        $prefix = 'lieu-' . $location->getNom() . '-image';
        $location->setImage($fileHandler->handle($newImage, null, $prefix, 'lieux', 'landscape720'));
      }

      $newPlan = $form->get('planMap')->getData();
      if (!empty($newPlan)) {
        $prefix = 'lieu-' . $location->getNom() . '-plan';
        $location->setPlanMap($fileHandler->handle($newPlan, null, $prefix, 'lieux', 'map1920'));
      }

      $newIcon = $form->get('icone')->getData();
      if (!empty($newIcon)) {
        $prefix = 'lieu-' . $location->getNom() . '-icone';
        $location->setIcone($fileHandler->handle($newIcon, null, $prefix, 'lieux', 'square192'));
      }

      $location->setDescription($nameTagger->tag($location->getDescription()));
      $location->setRegionDesc($nameTagger->tag($location->getRegionDesc()));

      $em->persist($location);
      $em->flush();

      $unlocker->sync($location, $form->get('unlockedBy')->getData());
      $unlocker->syncAccess($location);
      $em->flush();

      $this->addFlash('success', 'Le Lieu a bien été ajouté.');

      if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'location')
        return $this->redirectToRoute('world_location', ['id' => $location->getId()]);

      return $this->redirectToRoute('admin_location');
    }

    return $this->render('back_office/create.html.twig', [
      'type' => 'Créer',
      'entity' => 'location',
      'label' => 'Lieu',
      'genre' => 'M',
      'determinant' => 'un',
      'form' => $form->createView()
    ]);
  }

  /**
   * @Route("/admin/location/{id}/edit", name="admin_location_edit")
   * @IsGranted("ROLE_MJ")
   */
  public function editLocation(Request $request, Lieu $location, FileHandler $fileHandler, NameTagger $nameTagger, Unlocker $unlocker): Response {

    $location->setDescription($nameTagger->untag($location->getDescription()));
    $location->setRegionDesc($nameTagger->untag($location->getRegionDesc()));

    $form = $this->createForm(AdminLocationType::class, $location);
    $form->get('unlockedBy')->setData($unlocker->charactersOf($location));
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {

      $newImage = $form->get('image')->getData();
      if (!empty($newImage)) {
        $prefix = 'lieu-' . $location->getNom() . '-image';
        $location->setImage($fileHandler->handle($newImage, $location->getImage(), $prefix, 'lieux', 'landscape720'));
      } elseif ($request->request->get('remove_image') === '1' && $location->getImage()) {
        $fileHandler->handle(null, $location->getImage(), null, 'lieux');
        $location->setImage(null);
      }

      $newPlan = $form->get('planMap')->getData();
      if (!empty($newPlan)) {
        $prefix = 'lieu-' . $location->getNom() . '-plan';
        $location->setPlanMap($fileHandler->handle($newPlan, $location->getPlanMap(), $prefix, 'lieux', 'map1920'));
      }

      $newIcon = $form->get('icone')->getData();
      if (!empty($newIcon)) {
        $prefix = 'lieu-' . $location->getNom() . '-icone';
        $location->setIcone($fileHandler->handle($newIcon, $location->getIcone(), $prefix, 'lieux', 'square192'));
      } elseif ($request->request->get('remove_icone') === '1' && $location->getIcone()) {
        $nameTagger->replaceWithDefaultIcon($location);
        $fileHandler->handle(null, $location->getIcone(), null, 'lieux');
        $location->setIcone(null);
      }

      $location->setDescription($nameTagger->tag($location->getDescription()));
      $location->setRegionDesc($nameTagger->tag($location->getRegionDesc()));

      $unlocker->sync($location, $form->get('unlockedBy')->getData());
      $unlocker->syncAccess($location);

      $this->getDoctrine()->getManager()->flush();
      $this->addFlash('success', 'Le Lieu a bien été modifié.');

      if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'location')
        return $this->redirectToRoute('world_location', ['id' => $location->getId()]);

      return $this->redirectToRoute('admin_location');
    }

    return $this->renderForm('back_office/edit.html.twig', [
      'type' => 'Modifier',
      'location' => $location,
      'entity' => 'location',
      'label' => 'Lieu',
      'genre' => 'M',
      'determinant' => 'un',
      'form' => $form,
    ]);
  }

  /**
   * @Route("/admin/location/{id}/delete", name="admin_location_delete", methods={"POST"})
   * @IsGranted("ROLE_MJ")
   */
  public function deleteLocation(Request $request, Lieu $location, FileHandler $fileHandler, NameTagger $nameTagger, Unlocker $unlocker): Response {

    if ($this->isCsrfTokenValid('delete' . $location->getId(), $request->request->get('_csrf_token'))) {

      $entityManager = $this->getDoctrine()->getManager();

      $fileHandler->handle(null, $location->getImage(), null, 'lieux');
      $fileHandler->handle(null, $location->getPlanMap(), null, 'lieux');
      $nameTagger->replaceWithDefaultIcon($location);
      $fileHandler->handle(null, $location->getIcone(), null, 'lieux');

      $unlocker->forget($location);
      $entityManager->remove($location);
      $entityManager->flush();
      $this->addFlash('success', 'Le Lieu a bien été supprimé.');
    }

    return $this->redirectToRoute('admin_location');
  }
}
