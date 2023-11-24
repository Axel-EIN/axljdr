<?php

namespace App\Controller;

use App\Entity\Ecole;
use App\Service\FileHandler;
use App\Form\AdminEcoleType;
use App\Repository\ClanRepository;
use App\Repository\EcoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use function PHPUnit\Framework\fileExists;

class AdminEcoleController extends AbstractController
{
    /**
     * @Route("/admin/ecole", name="admin_ecole")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminEcoles(EcoleRepository $ecoleRepository): Response {

        $ecoles = $ecoleRepository->findBy(array(), array('id' => 'DESC'));
        
        return $this->render('back_office/list-element.html.twig', [
            'elements' => $ecoles,
            'element' => 'ecole',
            'label' => 'Écoles',
            'labels' => 'Écoless',
            'genre' => 'F',
            'determinant' => 'une',
            'img_size' => '48',
        ]);
    }

     /**
     * @Route("/admin/ecole/create", name="admin_ecole_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addEcole(Request $request, EntityManagerInterface $em, ClanRepository $clanRepository, FileHandler $fileHandler) {

        $ecole = new Ecole;

        // URL PARAMS PRE-FILL
        if ( !empty($request->query->get('clanID')) && $request->query->get('clanID') > 0 )
        {
            $clan = $clanRepository->find($request->query->get('clanID'));
            if ($clan !== null)
                $ecole->setClan($clan);
        }

        $form = $this->createForm(AdminEcoleType::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // File Image Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'ecole-' . $ecole->getClan()->getNom() . '-' . $ecole->getNom();
                $ecole->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'ecoles'));
            }

            $em->persist($ecole);
            $em->flush();
            $this->addFlash('success', 'L\'école a bien été ajoutée.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'ecole')
                return $this->redirectToRoute('regles_ecole', ['id' => $ecole->getId()]);
                
            return $this->redirectToRoute('admin_ecole');    
        }

        // RENDER
        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'ecole',
            'label' => 'École',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/ecole/{id}/edit", name="admin_ecole_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editEcole(Request $request, Ecole $ecole, FileHandler $fileHandler): Response {

        $form = $this->createForm(AdminEcoleType::class, $ecole);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // File Image Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'ecole' . '-' . $ecole->getClan()->getNom() . '-' . $ecole->getNom();
                $ecole->setImage($fileHandler->handle($nouvelleImage, $ecole->getImage(), $prefix, 'ecoles'));
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'L\'école a bien été modifiée.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'ecole')
                return $this->redirectToRoute('regles_ecole', ['id' => $ecole->getId()]);

            return $this->redirectToRoute('admin_ecole');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'ecole' => $ecole,
            'entity' => 'ecole',
            'label' => 'École',
            'genre' => 'F',
            'determinant' => 'une',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/ecole/{id}/delete", name="admin_ecole_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteEcole(Request $request, Ecole $ecole, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $ecole->getId(), $request->query->get('csrf'))) {

            // CHECK if child exists
            if (!$ecole->getPersonnages()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les personnages enfants au prélable !');
                return $this->redirectToRoute('admin_ecole');
            }

            // Image File Handling
            $fileHandler->handle(null, $ecole->getImage(), null, 'ecoles');

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($ecole);
            $entityManager->flush();
            $this->addFlash('success', 'L\'école a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_ecole');
    }
}