<?php

namespace App\Controller;

use App\Service\TableFilter;
use App\Entity\Chapitre;
use App\Service\FileHandler;
use App\Service\Orderer;
use App\Form\AdminChapterType;
use App\Repository\SaisonRepository;
use App\Repository\ChapitreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminChapterController extends AbstractController
{
    private const TABLE_FILTERS = [
        'saisonParent.titre:Saison',
    ];

    /**
     * @Route("/admin/chapter", name="admin_chapter")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminChapters(ChapitreRepository $chapterRepository): Response
    {
        $chapters = $chapterRepository->findBy(array(), array('id' => 'DESC'));

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $chapters,
            'element' => 'chapter',
            'label' => 'Chapitre',
            'labels' => "Chapitres",
            'gender' => 'M',
            'article' => 'un',
            'table_filters' => self::TABLE_FILTERS,
            'table_cols' => [
                'image:Image:image:NA_CHAPITRE',
                'titre:Titre::bold',
                'saisonParent.titre:Saison',
                'numero:Ordre:number',
                'couleur:Couleur:color',
                'citation:Citation:bool',
            ],
        ]);
    }

    /**
     * @Route("/admin/chapter/create", name="admin_chapter_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addChapter(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, SaisonRepository $seasonRepository, Orderer $orderer, ChapitreRepository $chapterRepository, TableFilter $tableFilter) {

        $chapter = new Chapitre;
        $tableFilter->prefill($chapter, self::TABLE_FILTERS, $request->query->all('filter'));

        if ( !empty($request->query->get('number')) && $request->query->get('number') > 0
          && !empty($request->query->get('seasonID')) && $request->query->get('seasonID') > 0 )
        {
                $chapter->setNumero($request->query->get('number'));
                $parentSeason = $seasonRepository->find($request->query->get('seasonID'));
                if ($parentSeason !== null)
                    $chapter->setSaisonParent($parentSeason);
        }

        $form = $this->createForm(AdminChapterType::class, $chapter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = $this->imagePrefix($chapter);
                $chapter->setImage($fileHandler->handle($newImage, null, $prefix, 'chapitres', 'landscape1080'));
            }

            $em->persist($chapter);
            $em->flush();
            $this->addFlash('success', 'Le chapitre a bien été crée.');

            $newSiblings = $chapterRepository->findBy(['saisonParent' => $chapter->getSaisonParent()->getId()]);
            $orderer->reorderNumber($chapter->getId(), -1, $chapter->getNumero(), [], $newSiblings);

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'story')
                return $this->redirectToRoute('story_season', ['id' => $chapter->getSaisonParent()->getId(),'_fragment' => 'read-head-ch-id' . $chapter->getId()]);
            
            return $this->redirectToRoute('admin_chapter');
        }
        

        return $this->render('back_office/create.html.twig', [
            'type' => 'create',
            'entity' => 'chapter',
            'label' => 'Chapitre',
            'gender' => 'M',
            'article' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/chapter/{id}/edit", name="admin_chapter_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editChapter(Request $request, Chapitre $chapter, FileHandler $fileHandler, Orderer $orderer, ChapitreRepository $chapterRepository): Response {

        $previousNumber = $chapter->getNumero();
        $previousSiblingsId = $chapter->getSaisonParent()->getId();

        $form = $this->createForm(AdminChapterType::class, $chapter);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = $this->imagePrefix($chapter);
                $chapter->setImage($fileHandler->handle($newImage, $chapter->getImage(), $prefix, 'chapitres', 'landscape1080'));
            } elseif ($request->request->get('remove_image') === '1' && $chapter->getImage()) {
                $fileHandler->handle(null, $chapter->getImage(), null, 'chapitres');
                $chapter->setImage(null);
            }

            if ($previousNumber != $chapter->getNumero() || $previousSiblingsId != $chapter->getSaisonParent()->getId())
            {
                $previousSiblings = $chapterRepository->findBy(['saisonParent' => $previousSiblingsId]);
                $newSiblings = $chapterRepository->findBy(['saisonParent' => $chapter->getSaisonParent()->getId()]);
                $orderer->reorderNumber($chapter->getId(), $previousNumber, $chapter->getNumero(), $previousSiblings, $newSiblings);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le chapitre a bien été modifié.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'story')
                return $this->redirectToRoute('story_season', ['id' => $chapter->getSaisonParent()->getId(),'_fragment' => 'read-head-ch-id' . $chapter->getId()]);
            
            return $this->redirectToRoute('admin_chapter');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'edit',
            'chapter' => $chapter,
            'entity' => 'chapter',
            'label' => 'Chapitre',
            'gender' => 'M',
            'article' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/chapter/{id}/delete", name="admin_chapter_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteChapter(Request $request, Chapitre $chapter, Orderer $orderer, FileHandler $fileHandler, ChapitreRepository $chapterRepository): Response {

        $parentSeason = $chapter->getSaisonParent();

        if ($this->isCsrfTokenValid('delete' . $chapter->getId(), $request->request->get('_csrf_token'))) {

            if (!$chapter->getEpisodes()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les épisodes enfants au prélable !');
                return $this->redirectToRoute('admin_chapter');
            }

            $fileHandler->handle(null, $chapter->getImage(), null, 'chapitres');

            $previousSiblingsId = $chapter->getSaisonParent()->getId();
            $previousSiblings = $chapterRepository->findBy(['saisonParent' => $previousSiblingsId]);
            $orderer->reorderNumber($chapter->getId(), $chapter->getNumero(), -1, $previousSiblings, []);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($chapter);
            $entityManager->flush();
            $this->addFlash('success', 'Le chapitre a bien été supprimé.');

        } else
            $this->addFlash('danger', 'Token de protection invalide !');

        if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'story')
            return $this->redirectToRoute('story_season', ['id' => $parentSeason->getId()], Response::HTTP_SEE_OTHER);
        
        return $this->redirectToRoute('admin_chapter');
    }

    private function imagePrefix(Chapitre $chapter): string
    {
        return 'chapitre-s' . $chapter->getSaisonParent()->getNumero() . '-ch' . $chapter->getNumero()
            . (empty($chapter->getTitre()) ? '' : '-' . $chapter->getTitre());
    }
}
