<?php

namespace App\Controller;

use App\Entity\FichePersonnage;
use App\Form\AdminSheetType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetenceRepository;
use App\Repository\FichePersonnageRepository;
use App\Repository\PersonnageRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminSheetController extends AbstractController
{
    /**
     * @Route("/admin/sheet", name="admin_sheet")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminSheets(FichePersonnageRepository $sheetRepository): Response
    {
        $sheets = $sheetRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $sheets,
            'element' => 'sheet',
            'label' => 'Fiche',
            'labels' => 'Fiches',
            'genre' => 'F',
            'determinant' => 'une',
            'table_filters' => [
                'personnage.estPj:Type:PJ|PNJ',
                'personnage.clan.nom:Clan',
            ],
            'table_cols' => [
                'personnage.icone:Portrait:symbol',
                'personnage.prenom:Prénom::bold',
                'creationExp:XPcreate:number',
                'avantage1:av1:bool',
                'avantage2:av2:bool',
                'desavantage1:des1:bool',
                'desavantage2:des2:bool',
                'armeActuelle:Arme:bool',
                'armure:Armure:bool',
                'reductionModifier:Sreduc:number',
                'honneur:Honneur:number',
                'gloire:Gloire:number',
                'infamie:Infamie:number',
                'souillure:Souillure:number',
            ],
        ]);
    }

    /**
     * @Route("/admin/sheet/create", name="admin_sheet_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addSheet(Request $request, EntityManagerInterface $em, CompetenceRepository $skillRepository, PersonnageRepository $characterRepository) {

        $sheet = new FichePersonnage;
        $character = $characterRepository->find($request->query->getInt('character'));
        if ($character !== null) {
            $sheet->setPersonnage($character);
        }

        $form = $this->createForm(AdminSheetType::class, $sheet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($sheet);
            $em->flush();
            $this->addFlash('success', 'La Fiche a bien été ajoutée.');

            if ($request->query->get('redirect') === 'sheet') {
                return $this->redirectToRoute('characters_sheet', ['id' => $sheet->getId()]);
            }

            return $this->redirectToRoute('admin_sheet');
        }

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'sheet',
            'label' => 'Fiche',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form->createView(),
            'skills_json' => $this->buildSkillsJson($skillRepository),
        ]);
    }

    /**
     * @Route("/admin/sheet/{id}/edit", name="admin_sheet_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editSheet(Request $request, FichePersonnage $sheet, CompetenceRepository $skillRepository): Response {

        $form = $this->createForm(AdminSheetType::class, $sheet);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La fiche a bien été modifiée.');

            return $this->redirectToRoute('admin_sheet');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'sheet' => $sheet,
            'entity' => 'sheet',
            'label' => 'Fiche',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form,
            'skills_json' => $this->buildSkillsJson($skillRepository),
        ]);
    }

    private function buildSkillsJson(CompetenceRepository $repo): array
    {
        $out = [];
        foreach ($repo->findBy([], ['nom' => 'ASC']) as $skill) {
            $out[$skill->getId()] = [
                'specialisations' => [
                    $skill->getSpecialisation1(),
                    $skill->getSpecialisation2(),
                    $skill->getSpecialisation3(),
                    $skill->getSpecialisation4(),
                    $skill->getSpecialisation5(),
                    $skill->getSpecialisation6(),
                ],
            ];
        }
        return $out;
    }

    /**
     * @Route("/admin/sheet/{id}/delete", name="admin_sheet_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteSheet(Request $request, FichePersonnage $sheet): Response {

        if ($this->isCsrfTokenValid('delete' . $sheet->getId(), $request->request->get('_csrf_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($sheet);
            $entityManager->flush();

            $this->addFlash('success', 'La fiche a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_sheet');
    }
}