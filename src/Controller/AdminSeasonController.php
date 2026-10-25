<?php

namespace App\Controller;

use App\Entity\Saison;
use App\Service\FileHandler;
use App\Service\Orderer;
use App\Form\AdminSeasonType;
use App\Repository\SaisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminSeasonController extends AbstractController
{
    /**
     * @Route("/admin/season", name="admin_season")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminSeasons(SaisonRepository $seasonRepository): Response
    {
        $seasons = $seasonRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $seasons,
            'element' => 'season',
            'label' => 'Saison',
            'labels' => 'Saisons',
            'gender' => 'F',
            'article' => 'une',
            'table_cols' => [
                'numero:Ordre:number',
                'image:Image:image:NA_SAISON',
                'titre:Titre::bold',
                'couleur:Couleur:color',
            ],
        ]);
    }

    /**
     * @Route("/admin/season/create", name="admin_season_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addSeason(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, SaisonRepository $seasonRepository, Orderer $orderer) {

        $season = new Saison;

        if (!empty($request->query->get('number')) && $request->query->get('number') > 0)
            $season->setNumero($request->query->get('number'));

        $form = $this->createForm(AdminSeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = $this->imagePrefix($season);
                $season->setImage($fileHandler->handle($newImage, null, $prefix, 'saisons', 'landscape1080'));
            }

            $em->persist($season);
            $em->flush();
            $this->addFlash('success', 'La saison a bien été créee.');

            $newSiblings = $seasonRepository->findAll();
            $orderer->reorderNumber($season->getId(), -1, $season->getNumero(), [], $newSiblings);

            if (in_array($request->query->get('redirect'), ['story', 'season'], true))
                return $this->redirectToRoute('story_season', ['id' => $season->getId()]);

            return $this->redirectToRoute('admin_season');

        }

        return $this->render('back_office/create.html.twig', [
            'type' => 'create',
            'entity' => 'season',
            'label' => 'Saison',
            'gender' => 'F',
            'article' => 'une',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/season/{id}/edit", name="admin_season_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editSeason(Request $request, Saison $season, FileHandler $fileHandler, Orderer $orderer, SaisonRepository $seasonRepository): Response {

        $previousNumber = $season->getNumero();
        $previousSiblings = $seasonRepository->findAll();

        $form = $this->createForm(AdminSeasonType::class, $season);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = $this->imagePrefix($season);
                $season->setImage($fileHandler->handle($newImage, $season->getImage(), $prefix, 'saisons', 'landscape1080'));
            } elseif ($request->request->get('remove_image') === '1' && $season->getImage()) {
                $fileHandler->handle(null, $season->getImage(), null, 'saisons');
                $season->setImage(null);
            }

            if ($previousNumber != $season->getNumero())
            {
                $previousSiblings = $seasonRepository->findAll();
                $newSiblings = $seasonRepository->findAll();
                $orderer->reorderNumber($season->getId(), $previousNumber, $season->getNumero(), $previousSiblings, $newSiblings);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La saison a bien été modifiée.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'story')
                return $this->redirectToRoute('story_season', ['id' => $season->getId()]);
            
            return $this->redirectToRoute('admin_season');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'edit',
            'season' => $season,
            'entity' => 'season',
            'label' => 'Saison',
            'gender' => 'F',
            'article' => 'une',
            'form' => $form,
        ]); 
    }

    /**
     * @Route("/admin/season/{id}/delete", name="admin_season_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteSeason(Request $request, Saison $season, Orderer $orderer, SaisonRepository $seasonRepository, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $season->getId(), $request->request->get('_csrf_token'))) {

            if (!$season->getChapitres()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les chapitres enfants au prélable !');
                return $this->redirectToRoute('admin_season');
            }

            $fileHandler->handle(null, $season->getImage(), null, 'saisons');

            $previousSiblings = $seasonRepository->findAll();
            $orderer->reorderNumber($season->getId(), $season->getNumero(), -1, $previousSiblings, []);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($season);
            $entityManager->flush();
            $this->addFlash('success', 'La saison a bien été supprimée.');
        } else
            $this->addFlash('danger', 'Token de protection invalide !');

        if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'story')
            return $this->redirectToRoute('story');

        return $this->redirectToRoute('admin_season');
    }

    private function imagePrefix(Saison $season): string
    {
        return 'saison-' . $season->getNumero()
            . (empty($season->getTitre()) ? '' : '-' . $season->getTitre());
    }
}
