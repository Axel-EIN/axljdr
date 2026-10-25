<?php

namespace App\Controller;

use App\Service\TableFilter;
use App\Repository\AvantageRepository;
use App\Repository\FichePersonnageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Avantage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Form\AdminAdvantageType;
use App\Service\Unlocker;

class AdminAdvantageController extends AbstractController
{
    private const TABLE_FILTERS = [
        'genre:Genre',
    ];

    /**
     * @Route("/admin/advantage", name="admin_advantage")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminAdvantages(AvantageRepository $advantageRepository): Response
    {
        $advantages = $advantageRepository->findBy(array(), array('id' => 'DESC'));

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $advantages,
            'element' => 'advantage',
            'label' => 'Avantage',
            'labels' => 'Avantages / Désavantages',
            'genre' => 'M',
            'determinant' => 'un',
            'table_filters' => self::TABLE_FILTERS,
            'table_cols' => [
                'nom:Nom',
                'genre:Genre',
                'type:Type',
                'cout:Coût',
                'discount:Coût Discount',
                'exclusive.nom:Exclusif',
                'discountClans:Discount Clan:list',
                'discountClasses:Discount Classe:list',
                'summary:Résumé:bool',
                'access:Accès:access',
            ],
        ]);
    }

    /**
     * @Route("/admin/advantage/create", name="admin_advantage_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addAdvantage(Request $request, EntityManagerInterface $em, Unlocker $unlocker, TableFilter $tableFilter) {

        $advantage = new Avantage;
        $tableFilter->prefill($advantage, self::TABLE_FILTERS, $request->query->all('filter'));
        $form = $this->createForm(AdminAdvantageType::class, $advantage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($advantage);
            $em->flush();

            $unlocker->sync($advantage, $form->get('unlockedBy')->getData());
            $em->flush();
            $this->addFlash('success', "L'Avantage a bien été ajouté.");

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
                && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'rules_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $advantage->getGenre(), 'subtab' => $advantage->getType() ] );

            return $this->redirectToRoute('admin_advantage');
        }
        

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'advantage',
            'label' => 'Avantage/Désavantage',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/advantage/{id}/edit", name="admin_advantage_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editAdvantage(Request $request, Avantage $advantage, Unlocker $unlocker): Response {

        $form = $this->createForm(AdminAdvantageType::class, $advantage);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($advantage));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $unlocker->sync($advantage, $form->get('unlockedBy')->getData());

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La Avantage a bien été modifiée.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
                && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'rules_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $advantage->getGenre(), 'subtab' => $advantage->getType() ] );

            return $this->redirectToRoute('admin_advantage');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'advantage' => $advantage,
            'entity' => 'advantage',
            'label' => 'Avantage/Désavantage',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/advantage/{id}/delete", name="admin_advantage_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteAdvantage(Request $request, Avantage $advantage, Unlocker $unlocker, FichePersonnageRepository $sheetRepository): Response {

        if ($this->isCsrfTokenValid('delete' . $advantage->getId(), $request->request->get('_csrf_token'))) {

            $sheets = $sheetRepository->findByAvantage($advantage);

            if (!empty($sheets)) {
                $characters = array_map(fn($sheet) => $sheet->getPersonnage()->getNom(), $sheets);
                $this->addFlash('danger', 'Impossible de supprimer « ' . $advantage->getNom() . ' », utilisé par : ' . implode(', ', $characters) . '.');

                return $this->redirectToRoute('admin_advantage');
            }

            $entityManager = $this->getDoctrine()->getManager();
            $unlocker->forget($advantage);
            $entityManager->remove($advantage);
            $entityManager->flush();
            $this->addFlash('success', 'La Avantage a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_advantage');
    }
}