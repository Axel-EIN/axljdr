<?php

namespace App\Controller;

use App\Service\TableFilter;
use App\Entity\Famille;
use App\Service\FileHandler;
use App\Form\AdminFamilyType;
use App\Repository\ClanRepository;
use App\Repository\FamilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AdminFamilyController extends AbstractController
{
    private const TABLE_FILTERS = [
        'clan.nom:Clan',
    ];

    /**
     * @Route("/admin/family", name="admin_family")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminFamilies(FamilleRepository $familyRepository): Response
    {
        $families = $familyRepository->findBy(array(), array('id' => 'DESC'));

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $families,
            'element' => 'family',
            'label' => 'Famille',
            'labels' => 'Familles',
            'genre' => 'F',
            'determinant' => 'une',
            'table_filters' => self::TABLE_FILTERS,
            'table_cols' => [
                'mon:Mon:symbol:NA_MON',
                'nom:Nom::bold',
                'clan.nom:Clan',
                'chef.prenom:Chef',
                'bonusStatNom:Trait Bonus',
                'description:Description:bool',
            ],
        ]);
    }

    /**
     * @Route("/admin/family/create", name="admin_family_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addFamily(Request $request, EntityManagerInterface $em, ClanRepository $clanRepository, FileHandler $fileHandler, TableFilter $tableFilter) {

        $family = new Famille;
        $tableFilter->prefill($family, self::TABLE_FILTERS, $request->query->all('filter'));

        if ( !empty($request->query->get('clanID')) && $request->query->get('clanID') > 0 )
        {
            $clan = $clanRepository->find($request->query->get('clanID'));
            if ($clan !== null)
                $family->setClan($clan);
        }

        $form = $this->createForm(AdminFamilyType::class, $family);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newMon = $form->get('mon')->getData();
            if (!empty($newMon)) {
                $prefix = 'famille-' . $family->getNom() . '-mon';
                $family->setMon($fileHandler->handle($newMon, null, $prefix, 'familles', 'square320'));
            }

            $em->persist($family);
            $em->flush();
            $this->addFlash('success', 'Le Famille a bien été ajoutée.');

            if (in_array($request->query->get('redirect'), ['clan', 'family'], true))
                return $this->redirectToRoute('world_clan', ['id' => $family->getClan()->getId()]);
            
            return $this->redirectToRoute('admin_family');
        }
        

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'family',
            'label' => 'Famille',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/family/{id}/edit", name="admin_family_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editFamily(Request $request, Famille $family, FileHandler $fileHandler): Response {

        $form = $this->createForm(AdminFamilyType::class, $family);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $newMon = $form->get('mon')->getData();
            if (!empty($newMon)) {
                $prefix = 'famille-' . $family->getNom() . '-image';
                $family->setMon($fileHandler->handle($newMon, $family->getMon(), $prefix, 'familles', 'square320'));
            } elseif ($request->request->get('remove_mon') === '1' && $family->getMon()) {
                $fileHandler->handle(null, $family->getMon(), null, 'familles');
                $family->setMon(null);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La Famille a bien été modifiée.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'clan')
                return $this->redirectToRoute('world_clan', ['id' => $family->getClan()->getId()]);

            return $this->redirectToRoute('admin_family');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'family' => $family,
            'entity' => 'family',
            'label' => 'Famille',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/family/{id}/delete", name="admin_family_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteFamily(Request $request, Famille $family, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $family->getId(), $request->request->get('_csrf_token'))) {

            $entityManager = $this->getDoctrine()->getManager();

            $fileHandler->handle(null, $family->getMon(), null, 'familles');

            $entityManager->remove($family);
            $entityManager->flush();
            $this->addFlash('success', 'La Famille a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_family');
    }
}