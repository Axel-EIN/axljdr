<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Service\FileHandler;
use App\Service\Numeroteur;
use App\Form\AdminEpisodeType;
use App\Repository\EpisodeRepository;
use App\Repository\ChapitreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminEpisodeController extends AbstractController
{
    /**
     * @Route("/admin/episode", name="admin_episode")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminEpisodes(EpisodeRepository $episodeRepository): Response
    {
        $episodes = $episodeRepository->findBy(array(), array('chapitreParent' => 'ASC'));
        return $this->render('admin_episode/index.html.twig', [
            'controller_name' => 'AdminEpisodeController',
            'episodes' => $episodes,
        ]);
    }

    /**
     * @Route("/admin/episode/create", name="admin_episode_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addEpisode(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, ChapitreRepository $chapitreRepository, Numeroteur $numeroteur, EpisodeRepository $episodeRepository) {

        $episode = new Episode;

        // URL PARAMS PRE-FILL
        if ( !empty($request->query->get('numero')) && $request->query->get('numero') > 0
          && !empty($request->query->get('chapitreID')) && $request->query->get('chapitreID') > 0 )
        {
                $episode->setNumero($request->query->get('numero'));
                $chapitreParent = $chapitreRepository->find($request->query->get('chapitreID'));
                if ($chapitreParent !== null)
                    $episode->setChapitreParent($chapitreParent);
        }

        $form = $this->createForm(AdminEpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // File Image Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'episode-s' . $episode->getChapitreParent()->getSaisonParent()->getNumero()
                . '-ch' . $episode->getChapitreParent()->getNumero() . '-ep' . $episode->getNumero();
                $episode->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'episodes'));
            }

            $em->persist($episode);
            $em->flush();
            $this->addFlash('success', 'L\'épisode a bien été crée.');

            // RE-ORDERING
            $fratrieArrivee = $episodeRepository->findBy(['chapitreParent' => $episode->getChapitreParent()->getId()]);
            $numeroteur->reordonnerNumero($episode->getId(), -1, $episode->getNumero(), [], $fratrieArrivee);

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'aventure')
                return $this->redirectToRoute('aventure_saison', ['id' => $episode->getChapitreParent()->getSaisonParent()->getId(),'_fragment' => 'read-head-ch-id' . $episode->getChapitreParent()->getId()]);
            
            return $this->redirectToRoute('admin_episode');
        } 

        return $this->render('admin_episode/create.html.twig', [
            'type' => 'Créer',
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/admin/episode/{id}/edit", name="admin_episode_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editEpisode(Request $request, Episode $episode, FileHandler $fileHandler, Numeroteur $numeroteur, EpisodeRepository $episodeRepository): Response {

        // NUMBER & ID save for re-ordering later
        $numeroDepart = $episode->getNumero();
        $fratrieDepartId = $episode->getChapitreParent()->getId();

        $form = $this->createForm(AdminEpisodeType::class, $episode);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // File Image Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'episode-s'
                . $episode->getChapitreParent()->getSaisonParent()->getNumero()
                . '-ch' . $episode->getChapitreParent()->getNumero() . '-ep' . $episode->getNumero();
                $episode->setImage($fileHandler->handle($nouvelleImage, $episode->getImage(), $prefix, 'episodes'));
            }

            // RE-ORDERING : If Order Number has changed OR ParentID have changed, then it needs RE-ORDERING
            if ($numeroDepart != $episode->getNumero() || $fratrieDepartId != $episode->getChapitreParent()->getId())
            {
                $fratrieDepart = $episodeRepository->findBy(['chapitreParent' => $fratrieDepartId]);
                $fratrieArrivee = $episodeRepository->findBy(['chapitreParent' => $episode->getChapitreParent()->getId()]);
                $numeroteur->reordonnerNumero($episode->getId(), $numeroDepart, $episode->getNumero(), $fratrieDepart, $fratrieArrivee);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'L\'épisode a bien été modifié.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'aventure')
                return $this->redirectToRoute('aventure_saison', ['id' => $episode->getChapitreParent()->getSaisonParent()->getId(),'_fragment' => 'read-head-ch-id' . $episode->getChapitreParent()->getId()]);

            return $this->redirectToRoute('admin_episode');
        }

        return $this->renderForm('admin_episode/edit.html.twig', [
            'episode' => $episode,
            'form' => $form,
            'type' => 'Modifier',
        ]);
    }

    /**
     * @Route("/admin/episode/{id}/delete", name="admin_episode_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteEpisode(Request $request, Episode $episode, Numeroteur $numeroteur, EpisodeRepository $episodeRepository, FileHandler $fileHandler): Response {
        $chapitreParent = $episode->getChapitreParent(); // Saving Parent for Redirection after Deletion

        if ($this->isCsrfTokenValid('delete' . $episode->getId(), $request->query->get('csrf'))) {

            // CHECK if childs exists
            if (!$episode->getScenes()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les scènes enfants au prélable !');
                return $this->redirectToRoute('admin_episode');
            }

            // File Image Handle
            $fileHandler->handle(null, $episode->getImage(), null, 'episodes');

            // RE-ORDERING
            $fratrieDepartId = $episode->getChapitreParent()->getId();
            $fratrieDepart = $episodeRepository->findBy(['chapitreParent' => $fratrieDepartId]);
            $numeroteur->reordonnerNumero($episode->getId(), $episode->getNumero(), -1, $fratrieDepart, []);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($episode);
            $entityManager->flush();
            $this->addFlash('success', 'L\'épisode a bien été supprimé.');
        }

        // REDIRECTION
        if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'aventure')
            return $this->redirectToRoute('aventure_saison', ['id' => $chapitreParent->getSaisonParent()->getId(), '_fragment' => 'read-head-ch-id' . $chapitreParent->getId()]);

        return $this->redirectToRoute('admin_episode');
    }
}