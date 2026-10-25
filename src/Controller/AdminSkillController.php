<?php

namespace App\Controller;

use App\Service\TableFilter;
use App\Repository\CompetenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Competence;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\AdminSkillType;
use App\Service\Unlocker;

class AdminSkillController extends AbstractController
{
    private const TABLE_FILTERS = [
        'categorie:Catégorie',
    ];

    /**
     * @Route("/admin/skill", name="admin_skill")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminSkills( CompetenceRepository $skillRepository ): Response
    {
        $skills = $skillRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $skills,
            'element' => 'skill',
            'label' => 'Compétence',
            'labels' => 'Compétences',
            'gender' => 'F',
            'article' => 'une',
            'table_filters' => self::TABLE_FILTERS,
            'table_cols' => [
                'nom:Nom',
                'trait:Trait',
                'categorie:Catégorie',
                'capacite:Maitrise1:bool',
                'capacite2:Maitrise2:bool',
                'description:Description:bool',
                'specialisation1:S1',
                'specialisation2:S2',
                'specialisation3:S3',
                'specialisation4:S4',
                'specialisation5:S5',
                'access:Accès:access',
                'publishedAt:Publié le:date',
            ],
        ]);
    }

    /**
     * @Route("/admin/skill/create", name="admin_skill_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addSkill(Request $request, EntityManagerInterface $em, Unlocker $unlocker, TableFilter $tableFilter) {

        $skill = new Competence;
        $tableFilter->prefill($skill, self::TABLE_FILTERS, $request->query->all('filter'));
        $form = $this->createForm(AdminSkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($skill);
            $em->flush();

            $unlocker->sync($skill, $form->get('unlockedBy')->getData());
            $em->flush();
            $this->addFlash('success', "La compétence a bien été ajoutée.");

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'rules_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $skill->getCategorie() ] );

            return $this->redirectToRoute('admin_skill');
        }
        

        return $this->render('back_office/create.html.twig', [
            'type' => 'create',
            'entity' => 'skill',
            'label' => 'Compétence',
            'gender' => 'F',
            'article' => 'une',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/skill/{id}/edit", name="admin_skill_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editSkill(Request $request, Competence $skill, Unlocker $unlocker): Response {

        $form = $this->createForm(AdminSkillType::class, $skill);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($skill));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $unlocker->sync($skill, $form->get('unlockedBy')->getData());

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La compétence a bien été modifiée.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'rules_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $skill->getCategorie() ] );

            return $this->redirectToRoute('admin_skill');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'edit',
            'skill' => $skill,
            'entity' => 'skill',
            'label' => 'Compétence',
            'gender' => 'F',
            'article' => 'une',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/skill/{id}/delete", name="admin_skill_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteSkill(Request $request, Competence $skill, Unlocker $unlocker): Response {

        if ($this->isCsrfTokenValid('delete' . $skill->getId(), $request->request->get('_csrf_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $unlocker->forget($skill);
            $entityManager->remove($skill);
            $entityManager->flush();
            $this->addFlash('success', 'La compétence a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_skill');
    }
}