<?php

namespace App\Controller;

use App\Repository\AvantageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Avantage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Form\AdminAvantageType;

class AdminAvantageController extends AbstractController
{
    /**
     * @Route("/admin/avantage", name="admin_avantage")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminAvantages( AvantageRepository $avantageRepository): Response
    {
        $avantages = $avantageRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('admin_avantage/index.html.twig', [
            'avantages' => $avantages
        ]);
    }

    /**
     * @Route("/admin/avantage/create", name="admin_avantage_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addAvantage(Request $request, EntityManagerInterface $em) {

        $avantage = new Avantage;
        $form = $this->createForm(AdminAvantageType::class, $avantage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($avantage);
            $em->flush();
            $this->addFlash('success', "L'Avantage a bien été ajouté.");

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'avantage')
                return $this->redirectToRoute('regles_avantages');
            return $this->redirectToRoute('admin_avantage');
        }
        
        return $this->render('admin_avantage/create.html.twig', [
            'type' => 'Créer',
            'form' => $form->createView()
            ]);
    }

    /**
     * @Route("/admin/avantage/{id}/edit", name="admin_avantage_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editAvantage(Request $request, Avantage $avantage): Response {

        $form = $this->createForm(AdminAvantageType::class, $avantage);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La Avantage a bien été modifiée.');

            // REDIRECTION
            if (    !empty($request->query->get('redirect'))
                    && $request->query->get('redirect') == 'library'
                    && !empty($request->query->get('libraryID'))
                    && $request->query->get('libraryID') > 0  )
                return $this->redirectToRoute( 'regles_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $avantage->getGenre(), 'subtab' => $avantage->getType() ] );

            return $this->redirectToRoute('admin_avantage');
        }

        return $this->renderForm('admin_avantage/edit.html.twig', [
            'avantage' => $avantage,
            'form' => $form,
            'type' => 'Modifier',
        ]);
        
    }

    /**
     * @Route("/admin/avantage/{id}/delete", name="admin_avantage_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteAvantage(Request $request, Avantage $avantage): Response {

        if ($this->isCsrfTokenValid('delete' . $avantage->getId(), $request->query->get('csrf'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($avantage);
            $entityManager->flush();
            $this->addFlash('success', 'La Avantage a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_avantage');
    }
}