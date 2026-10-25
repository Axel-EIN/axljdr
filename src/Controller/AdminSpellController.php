<?php

namespace App\Controller;

use App\Service\TableFilter;
use App\Entity\Sort;
use App\Form\AdminSpellType;
use App\Service\Orderer;
use App\Service\FileHandler;
use App\Repository\SortRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\Unlocker;

class AdminSpellController extends AbstractController
{
    private const TABLE_FILTERS = [
        'categorie:Catégorie',
        'anneau:Anneau',
    ];

    /**
     * @Route("/admin/spell", name="admin_spell")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminSpells( SortRepository $spellRepository ): Response
    {
        $spells = $spellRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $spells,
            'label' => 'Sort',
            'labels' => "Sorts",
            'genre' => 'M',
            'determinant' => 'un',
            'element' => 'spell',
            'table_filters' => self::TABLE_FILTERS,
            'table_cols' => [
                'nom:Nom::bold',
                'originalName:NomOri:bool',
                'categorie:Catégorie',
                'anneau:Anneau',
                'niveau:LV:number',
                'numero:Ordre:number',
                'portee',
                'zone',
                'duree',
                'augmentations:Aug:bool',
                'description:Text:bool',
                'kihoType',
                'keyword1:K1:bool',
                'keyword2:K2:bool',
                'keyword3:K3:bool',
                'access:Accès:access',
                'publishedAt:Publié le:date',
            ],
        ]);
    }

    /**
     * @Route("/admin/spell/create", name="admin_spell_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addSpell(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, SortRepository $spellRepository, Orderer $orderer, Unlocker $unlocker, TableFilter $tableFilter) {

        $spell = new Sort;
        $tableFilter->prefill($spell, self::TABLE_FILTERS, $request->query->all('filter'));

        $numberParam = $request->query->get('numero');
        if ( !empty($numberParam) && is_numeric($numberParam) && $numberParam > 0 )
            $spell->setNumero($numberParam);

        $categoryParam = $request->query->get('tab');
        if ( !empty($categoryParam) && !is_numeric($categoryParam) )
            $spell->setCategorie($categoryParam);

        $ringParam = $request->query->get('subtab');
        if ( !empty($ringParam) && !is_numeric($ringParam) )
            $spell->setAnneau($ringParam);

        $form = $this->createForm(AdminSpellType::class, $spell);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formNumber = $form->get('numero')->getData();
            $newSiblings = $spellRepository->findBy(['anneau' => $form->get('anneau')->getData()]);

            if ( empty($formNumber) || !is_numeric($formNumber) || $formNumber < 1 )
                $spell->setNumero( count($newSiblings) + 1 );
            else
                $spell->setNumero( $formNumber );

            $em->persist($spell);
            $em->flush();

            $unlocker->sync($spell, $form->get('unlockedBy')->getData());
            $em->flush();
            $this->addFlash('success', "Le sort a bien été ajouté.");

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'rules_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $spell->getCategorie() , 'subtab' => $spell->getAnneau() ] );

            return $this->redirectToRoute('admin_spell');
        }
        

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'spell',
            'label' => 'Sort',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/spell/{id}/edit", name="admin_spell_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editSpell(Request $request, Sort $spell, SortRepository $spellRepository, Orderer $orderer, Unlocker $unlocker): Response {

        if ( empty( $spell->getNumero() ) || !is_numeric( $spell->getNumero() ) || $spell->getNumero() < 0)
            $previousNumber = -1;
        else
            $previousNumber = $spell->getNumero();

        $previousSiblingsId = $spell->getAnneau();

        $form = $this->createForm(AdminSpellType::class, $spell);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($spell));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            if ($previousNumber != $spell->getNumero() || $previousSiblingsId != $spell->getAnneau())
            {
                $previousSiblings = $spellRepository->findBy(['anneau' => $previousSiblingsId]);
                $newSiblings = $spellRepository->findBy(['anneau' => $spell->getAnneau()]);
                $orderer->reorderNumber($spell->getId(), $previousNumber, $spell->getNumero(), $previousSiblings, $newSiblings);
            }

            $unlocker->sync($spell, $form->get('unlockedBy')->getData());

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', "Le Sort a bien été modifié.");

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
            && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'rules_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $spell->getCategorie() , 'subtab' => $spell->getAnneau() ] );

            return $this->redirectToRoute('admin_spell');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'spell' => $spell,
            'entity' => 'spell',
            'label' => 'Sort',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/spell/{id}/delete", name="admin_spell_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteSpell(Request $request, Sort $spell, FileHandler $fileHandler, Unlocker $unlocker): Response {

        if ($this->isCsrfTokenValid('delete' . $spell->getId(), $request->request->get('_csrf_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $unlocker->forget($spell);
            $entityManager->remove($spell);
            $entityManager->flush();
            $this->addFlash('success', "Le Sort a bien été supprimé.");
        }

        return $this->redirectToRoute('admin_spell');
    }

}
