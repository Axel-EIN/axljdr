<?php

namespace App\Controller;

use App\Repository\CompetenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Competence;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\AdminCompetenceType;

class AdminCompetenceController extends AbstractController
{
    /**
     * @Route("/admin/competence", name="admin_competence")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminCompetences( CompetenceRepository $competenceRepository ): Response
    {
        $competences = $competenceRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $competences,
            'element' => 'competence',
            'label' => 'Compétence',
            'labels' => 'Compétences',
            'genre' => 'F',
            'determinant' => 'une',
            'img_size' => '48',
            'extra_col1' => 'categorie',
        ]);
    }

    /**
     * @Route("/admin/competence/create", name="admin_competence_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addCompetence(Request $request, EntityManagerInterface $em) {

        $competence = new Competence;
        $form = $this->createForm(AdminCompetenceType::class, $competence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($competence);
            $em->flush();
            $this->addFlash('success', "La compétence a bien été ajoutée.");

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'regles_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $competence->getCategorie() ] );

            return $this->redirectToRoute('admin_competence');
        }
        
        // RENDER
        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'competence',
            'label' => 'Compétence',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/competence/{id}/edit", name="admin_competence_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editCompetence(Request $request, Competence $competence): Response {

        $form = $this->createForm(AdminCompetenceType::class, $competence);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La compétence a bien été modifiée.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'regles_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $competence->getCategorie() ] );

            return $this->redirectToRoute('admin_competence');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'competence' => $competence,
            'entity' => 'competence',
            'label' => 'Compétence',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/competence/{id}/delete", name="admin_competence_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteCompetence(Request $request, Competence $competence): Response {

        if ($this->isCsrfTokenValid('delete' . $competence->getId(), $request->query->get('csrf'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($competence);
            $entityManager->flush();
            $this->addFlash('success', 'La compétence a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_competence');
    }
}