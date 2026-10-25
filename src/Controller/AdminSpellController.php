<?php

namespace App\Controller;

use App\Service\TableFilter;
use App\Entity\Sort;
use App\Form\AdminSpellType;
use App\Service\Numeroteur;
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
    public function addSpell(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, SortRepository $spellRepository, Numeroteur $numeroteur, Unlocker $unlocker, TableFilter $tableFilter) {

        $spell = new Sort;
        $tableFilter->prefill($spell, self::TABLE_FILTERS, $request->query->all('filter'));

        $param_numero = $request->query->get('numero');
        if ( !empty($param_numero) && is_numeric($param_numero) && $param_numero > 0 )
            $spell->setNumero($param_numero);

        $param_categorie = $request->query->get('tab');
        if ( !empty($param_categorie) && !is_numeric($param_categorie) )
            $spell->setCategorie($param_categorie);

        $param_anneau = $request->query->get('subtab');
        if ( !empty($param_anneau) && !is_numeric($param_anneau) )
            $spell->setAnneau($param_anneau);

        $form = $this->createForm(AdminSpellType::class, $spell);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formNumero = $form->get('numero')->getData();
            $newSiblings = $spellRepository->findBy(['anneau' => $form->get('anneau')->getData()]);

            if ( empty($formNumero) || !is_numeric($formNumero) || $formNumero < 1 )
                $spell->setNumero( count($newSiblings) + 1 );
            else
                $spell->setNumero( $formNumero );

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
    public function editSpell(Request $request, Sort $spell, SortRepository $spellRepository, Numeroteur $numeroteur, Unlocker $unlocker): Response {

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
                $numeroteur->reordonnerNumero($spell->getId(), $previousNumber, $spell->getNumero(), $previousSiblings, $newSiblings);
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
