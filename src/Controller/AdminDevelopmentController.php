<?php

namespace App\Controller;

use App\Entity\Development;
use App\Form\AdminDevelopmentType;
use App\Repository\DevelopmentRepository;
use App\Repository\PersonnageRepository;
use App\Service\Baliseur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminDevelopmentController extends AbstractController
{
    /**
     * @Route("/admin/development", name="admin_development")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminDevelopments(DevelopmentRepository $developmentRepository): Response
    {
        $developments = $developmentRepository->findBy([], ['id' => 'DESC']);

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $developments,
            'element' => 'development',
            'label' => 'Développement',
            'labels' => 'Développements',
            'genre' => 'M',
            'determinant' => 'un',
            'table_cols' => [
                'participation.personnage.icone:Portrait:symbol',
                'participation.personnage.prenom:Personnage::bold',
                'participation.scene.episodeParent.titre:Épisode',
                'participation.scene.numero:Scène:number',
                'participation.scene.titre:Titre de la scène',
                'participation.estMort:Mort:bool',
                'content:Texte:bool',
            ],
        ]);
    }

    /**
     * @Route("/admin/development/create", name="admin_development_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addDevelopment(Request $request, EntityManagerInterface $em, PersonnageRepository $personnageRepository, Baliseur $baliseur): Response
    {
        $development = new Development;
        $form = $this->createForm(AdminDevelopmentType::class, $development);

        if ($personnageId = $request->query->get('personnage')) {
            $form->get('personnage')->setData($personnageRepository->find($personnageId));
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $development->setContent($baliseur->baliser($development->getContent()));
            $em->persist($development);
            $em->flush();
            $this->addFlash('success', 'Le Développement a bien été ajouté');

            return $this->redirectApres($request, $development);
        }

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'development',
            'label' => 'Développement',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/development/{id}/edit", name="admin_development_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editDevelopment(Request $request, Development $development, EntityManagerInterface $em, Baliseur $baliseur): Response
    {
        $form = $this->createForm(AdminDevelopmentType::class, $development);
        $form->get('personnage')->setData($development->getPersonnage());
        $form->get('content')->setData($baliseur->debaliser($development->getContent()));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $development->setContent($baliseur->baliser($development->getContent()));
            $em->flush();
            $this->addFlash('success', 'Le Développement a bien été modifié');

            return $this->redirectApres($request, $development);
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'development' => $development,
            'entity' => 'development',
            'label' => 'Développement',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/development/{id}/delete", name="admin_development_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteDevelopment(Request $request, Development $development, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $development->getId(), $request->request->get('_csrf_token'))) {
            $em->remove($development);
            $em->flush();
            $this->addFlash('success', 'Le Développement a bien été supprimé');
        }

        return $this->redirectToRoute('admin_development');
    }

    private function redirectApres(Request $request, Development $development): Response
    {
        if ($request->query->get('redirect') === 'development') {
            return $this->redirectToRoute('personnage_profil', ['id' => $development->getPersonnage()->getId()]);
        }

        return $this->redirectToRoute('admin_development');
    }
}
