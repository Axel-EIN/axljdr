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
use App\Service\Unlocker;

class AdminEpisodeController extends AbstractController
{
    /**
     * @Route("/admin/episode", name="admin_episode")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminEpisodes(EpisodeRepository $episodeRepository): Response
    {
        $episodes = $episodeRepository->findBy(array(), array('id' => 'DESC'));

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $episodes,
            'element' => 'episode',
            'label' => 'Épisode',
            'labels' => 'Épisodes',
            'genre' => 'M',
            'determinant' => 'un',
            'table_cols' => [
                'image:Image:image:NA_SESSION',
                'titre:Titre::bold',
                'chapitreParent.titre:Chapitre',
                'numero:Ordre:number',
                'numeroSaison:Enième:number',
                'issue:Issue',
                'resume:Résumé:bool',
                'access:Accès:access',
                'publishedAt:Publié le:date',
            ],
        ]);
    }

    /**
     * @Route("/admin/episode/create", name="admin_episode_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addEpisode(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, ChapitreRepository $chapitreRepository, Numeroteur $numeroteur, EpisodeRepository $episodeRepository, Unlocker $unlocker) {

        $episode = new Episode;

        if ( !empty($request->query->get('numero')) && $request->query->get('numero') > 0
          && !empty($request->query->get('chapitreID')) && $request->query->get('chapitreID') > 0 )
        {
                $episode->setNumero($request->query->get('numero'));
                $chapitreParent = $chapitreRepository->find($request->query->get('chapitreID'));
                if ($chapitreParent !== null)
                    $episode->setChapitreParent($chapitreParent);
        }

        $episode->setNumeroSaison($episodeRepository->nextCampaignNumber());

        $form = $this->createForm(AdminEpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'episode-s' . $episode->getChapitreParent()->getSaisonParent()->getNumero()
                . '-ch' . $episode->getChapitreParent()->getNumero() . '-ep' . $episode->getNumero();
                $episode->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'episodes', 'landscape720'));
            }

            $em->persist($episode);
            $em->flush();

            $unlocker->sync($episode, $form->get('unlockedBy')->getData());
            $em->flush();
            $this->addFlash('success', 'L\'épisode a bien été crée.');

            $fratrieArrivee = $episodeRepository->findBy(['chapitreParent' => $episode->getChapitreParent()->getId()]);
            $numeroteur->reordonnerNumero($episode->getId(), -1, $episode->getNumero(), [], $fratrieArrivee);

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'aventure')
                return $this->redirectToRoute('aventure_saison', ['id' => $episode->getChapitreParent()->getSaisonParent()->getId(),'_fragment' => 'read-head-ch-id' . $episode->getChapitreParent()->getId()]);
            
            return $this->redirectToRoute('admin_episode');
        } 

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'episode',
            'label' => 'Épisode',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/admin/episode/{id}/edit", name="admin_episode_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editEpisode(Request $request, Episode $episode, FileHandler $fileHandler, Numeroteur $numeroteur, EpisodeRepository $episodeRepository, Unlocker $unlocker): Response {

        $numeroDepart = $episode->getNumero();
        $fratrieDepartId = $episode->getChapitreParent()->getId();

        $form = $this->createForm(AdminEpisodeType::class, $episode);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($episode));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'episode-s'
                . $episode->getChapitreParent()->getSaisonParent()->getNumero()
                . '-ch' . $episode->getChapitreParent()->getNumero() . '-ep' . $episode->getNumero();
                $episode->setImage($fileHandler->handle($nouvelleImage, $episode->getImage(), $prefix, 'episodes', 'landscape720'));
            } elseif ($request->request->get('remove_image') === '1' && $episode->getImage()) {
                $fileHandler->handle(null, $episode->getImage(), null, 'episodes');
                $episode->setImage(null);
            }

            if ($numeroDepart != $episode->getNumero() || $fratrieDepartId != $episode->getChapitreParent()->getId())
            {
                $fratrieDepart = $episodeRepository->findBy(['chapitreParent' => $fratrieDepartId]);
                $fratrieArrivee = $episodeRepository->findBy(['chapitreParent' => $episode->getChapitreParent()->getId()]);
                $numeroteur->reordonnerNumero($episode->getId(), $numeroDepart, $episode->getNumero(), $fratrieDepart, $fratrieArrivee);
            }

            $unlocker->sync($episode, $form->get('unlockedBy')->getData());

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'L\'épisode a bien été modifié.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'aventure')
                return $this->redirectToRoute('aventure_saison', ['id' => $episode->getChapitreParent()->getSaisonParent()->getId(),'_fragment' => 'read-head-ch-id' . $episode->getChapitreParent()->getId()]);

            return $this->redirectToRoute('admin_episode');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'episode' => $episode,
            'entity' => 'episode',
            'label' => 'Épisode',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/episode/{id}/delete", name="admin_episode_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteEpisode(Request $request, Episode $episode, Numeroteur $numeroteur, EpisodeRepository $episodeRepository, FileHandler $fileHandler, Unlocker $unlocker): Response {
        $chapitreParent = $episode->getChapitreParent();

        if ($this->isCsrfTokenValid('delete' . $episode->getId(), $request->request->get('_csrf_token'))) {

            if (!$episode->getScenes()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les scènes enfants au prélable !');
                return $this->redirectToRoute('admin_episode');
            }

            $fileHandler->handle(null, $episode->getImage(), null, 'episodes');

            $fratrieDepartId = $episode->getChapitreParent()->getId();
            $fratrieDepart = $episodeRepository->findBy(['chapitreParent' => $fratrieDepartId]);
            $numeroteur->reordonnerNumero($episode->getId(), $episode->getNumero(), -1, $fratrieDepart, []);

            $entityManager = $this->getDoctrine()->getManager();
            $unlocker->forget($episode);
            $entityManager->remove($episode);
            $entityManager->flush();
            $this->addFlash('success', 'L\'épisode a bien été supprimé.');
        }

        if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'aventure')
            return $this->redirectToRoute('aventure_saison', ['id' => $chapitreParent->getSaisonParent()->getId(), '_fragment' => 'read-head-ch-id' . $chapitreParent->getId()]);

        return $this->redirectToRoute('admin_episode');
    }
}