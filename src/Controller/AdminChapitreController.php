<?php

namespace App\Controller;

use App\Entity\Chapitre;
use App\Service\FileHandler;
use App\Service\Numeroteur;
use App\Form\AdminChapitreType;
use App\Repository\SaisonRepository;
use App\Repository\ChapitreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminChapitreController extends AbstractController
{
    /**
     * @Route("/admin/chapitre", name="admin_chapitre")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminChapitres(ChapitreRepository $chapitreRepository): Response {
        $chapitres = $chapitreRepository->findBy(array(), array('saisonParent' => 'ASC'));
        return $this->render('admin_chapitre/index.html.twig', [
            'controller_name' => 'AdminChapitreController',
            'chapitres' => $chapitres,
        ]);
    }

    /**
     * @Route("/admin/chapitre/create", name="admin_chapitre_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addChapitre(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, SaisonRepository $saisonRepository, Numeroteur $numeroteur, ChapitreRepository $chapitreRepository) {

        $chapitre = new Chapitre;

        // URL PARAM PRE-FILL
        if ( !empty($request->query->get('numero')) && $request->query->get('numero') > 0
          && !empty($request->query->get('saisonID')) && $request->query->get('saisonID') > 0 )
        {
                $chapitre->setNumero($request->query->get('numero'));
                $saisonParent = $saisonRepository->find($request->query->get('saisonID'));
                if ($saisonParent !== null)
                    $chapitre->setSaisonParent($saisonParent);
        }

        $form = $this->createForm(AdminChapitreType::class, $chapitre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // FILE IMAGE HANDLING
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'chapitre-s' . $chapitre->getSaisonParent()->getNumero() . '-ch' . $chapitre->getNumero();
                $chapitre->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'chapitres'));
            }

            $em->persist($chapitre);
            $em->flush();
            $this->addFlash('success', 'Le chapitre a bien été crée.');

            // RE-ORDERING
            $fratrieArrivee = $chapitreRepository->findBy(['saisonParent' => $chapitre->getSaisonParent()->getId()]);
            $numeroteur->reordonnerNumero($chapitre->getId(), -1, $chapitre->getNumero(), [], $fratrieArrivee);

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'aventure')
                return $this->redirectToRoute('aventure_saison', ['id' => $chapitre->getSaisonParent()->getId(),'_fragment' => 'read-head-ch-id' . $chapitre->getId()]);
            
            return $this->redirectToRoute('admin_chapitre');
        }
        
        return $this->render('admin_chapitre/create.html.twig', [
            'type' => 'Créer',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/chapitre/{id}/edit", name="admin_chapitre_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editChapitre(Request $request, Chapitre $chapitre, FileHandler $fileHandler, Numeroteur $numeroteur, ChapitreRepository $chapitreRepository): Response {

        // Number and ID saves for RE-ORDERING
        $numeroDepart = $chapitre->getNumero();
        $fratrieDepartId = $chapitre->getSaisonParent()->getId();

        $form = $this->createForm(AdminChapitreType::class, $chapitre);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // FILE IMAGE HANDLING
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'chapitre-s' . $chapitre->getSaisonParent()->getNumero() . '-ch' . $chapitre->getNumero();
                $chapitre->setImage($fileHandler->handle($nouvelleImage, $chapitre->getImage(), $prefix, 'chapitres'));
            }

            // RE-ORDERING : If Order Number has changed OR ParentID have changed, then it needs RE-ORDERING
            if ($numeroDepart != $chapitre->getNumero() || $fratrieDepartId != $chapitre->getSaisonParent()->getId())
            {
                $fratrieDepart = $chapitreRepository->findBy(['saisonParent' => $fratrieDepartId]);
                $fratrieArrivee = $chapitreRepository->findBy(['saisonParent' => $chapitre->getSaisonParent()->getId()]);
                $numeroteur->reordonnerNumero($chapitre->getId(), $numeroDepart, $chapitre->getNumero(), $fratrieDepart, $fratrieArrivee);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le chapitre a bien été modifié.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'aventure')
                return $this->redirectToRoute('aventure_saison', ['id' => $chapitre->getSaisonParent()->getId(),'_fragment' => 'read-head-ch-id' . $chapitre->getId()]);
            
            return $this->redirectToRoute('admin_chapitre');
        }

        return $this->renderForm('admin_chapitre/edit.html.twig', [
            'chapitre' => $chapitre,
            'form' => $form,
            'type' => 'Modifier',
        ]);
    }

    /**
     * @Route("/admin/chapitre/{id}/delete", name="admin_chapitre_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteChapitre(Request $request, Chapitre $chapitre, Numeroteur $numeroteur, FileHandler $fileHandler, ChapitreRepository $chapitreRepository): Response {

        $saisonParent = $chapitre->getSaisonParent(); // Saving Parent before deletion to be able to redirect after

        if ($this->isCsrfTokenValid('delete' . $chapitre->getId(), $request->query->get('csrf'))) {

            // CHECKING if child exists
            if (!$chapitre->getEpisodes()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les épisodes enfants au prélable !');
                return $this->redirectToRoute('admin_chapitre');
            }

            // FILE IMAGE HANDLING
            $fileHandler->handle(null, $chapitre->getImage(), null, 'chapitres');

            // RE-ORDERING
            $fratrieDepartId = $chapitre->getSaisonParent()->getId();
            $fratrieDepart = $chapitreRepository->findBy(['saisonParent' => $fratrieDepartId]);
            $numeroteur->reordonnerNumero($chapitre->getId(), $chapitre->getNumero(), -1, $fratrieDepart, []);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($chapitre);
            $entityManager->flush();
            $this->addFlash('success', 'Le chapitre a bien été supprimé.');

        } else
            $this->addFlash('danger', 'Token de protection invalide !');

        // REDIRECTION
        if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'aventure')
            return $this->redirectToRoute('aventure_saison', ['id' => $saisonParent->getId()], Response::HTTP_SEE_OTHER);
        
        return $this->redirectToRoute('admin_chapitre');
    }
}