<?php

namespace App\Controller;

use App\Entity\FichePersonnage;
use App\Form\AdminFichePersonnageType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetenceRepository;
use App\Repository\FichePersonnageRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminFicheController extends AbstractController
{
    /**
     * @Route("/admin/fiche", name="admin_fiche")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminFiches(FichePersonnageRepository $fichePersonnageRepository): Response
    {
        $fiches = $fichePersonnageRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $fiches,
            'element' => 'fiche',
            'label' => 'Fiche',
            'labels' => 'Fiches',
            'genre' => 'F',
            'determinant' => 'une',
            'table_cols' => [
                'personnage.icone:Portrait:symbol',
                'personnage.prenom:Prénom::bold',
                'creationExp:XPcreate:number',
                'avantage1:av1:bool',
                'avantage2:av2:bool',
                'desavantage1:des1:bool',
                'desavantage2:des2:bool',
                'arme:Arme:bool',
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
     * @Route("/admin/fiche/create", name="admin_fiche_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addFiche(Request $request, EntityManagerInterface $em, CompetenceRepository $competenceRepository) {

        $fiche = new FichePersonnage;
        $form = $this->createForm(AdminFichePersonnageType::class, $fiche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($fiche);
            $em->flush();
            $this->addFlash('success', 'La Fiche a bien été ajoutée.');

            return $this->redirectToRoute('admin_fiche');
        }

        // RENDER
        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'fiche',
            'label' => 'Fiche',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form->createView(),
            'competences_json' => $this->buildCompetencesJson($competenceRepository),
        ]);
    }

    /**
     * @Route("/admin/fiche/{id}/edit", name="admin_fiche_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editFiche(Request $request, FichePersonnage $fiche, CompetenceRepository $competenceRepository): Response {

        $form = $this->createForm(AdminFichePersonnageType::class, $fiche);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La fiche a bien été modifiée.');

            return $this->redirectToRoute('admin_fiche');
        }

        // RENDER
        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'fiche' => $fiche,
            'entity' => 'fiche',
            'label' => 'Fiche',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form,
            'competences_json' => $this->buildCompetencesJson($competenceRepository),
        ]);
    }

    private function buildCompetencesJson(CompetenceRepository $repo): array
    {
        $out = [];
        foreach ($repo->findBy([], ['nom' => 'ASC']) as $c) {
            $out[$c->getId()] = [
                'specialisations' => [
                    $c->getSpecialisation1(),
                    $c->getSpecialisation2(),
                    $c->getSpecialisation3(),
                    $c->getSpecialisation4(),
                    $c->getSpecialisation5(),
                    $c->getSpecialisation6(),
                ],
            ];
        }
        return $out;
    }

    /**
     * @Route("/admin/fiche/{id}/delete", name="admin_fiche_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteFiche(Request $request, FichePersonnage $fiche): Response {

        if ($this->isCsrfTokenValid('delete' . $fiche->getId(), $request->request->get('_csrf_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($fiche);
            $entityManager->flush();

            $this->addFlash('success', 'La fiche a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_fiche');
    }
}