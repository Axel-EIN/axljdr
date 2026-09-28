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
use App\Service\Unlocker;

class AdminAvantageController extends AbstractController
{
    private const LISTS = [
        'Avantage' => ['element' => 'avantage', 'labels' => 'Avantages'],
        'Désavantage' => ['element' => 'desavantage', 'labels' => 'Désavantages'],
    ];

    private function listRoute(Avantage $avantage): string
    {
        return 'admin_' . self::LISTS[$avantage->getGenre()]['element'];
    }

    /**
     * @Route("/admin/avantage", name="admin_avantage", defaults={"genre": "Avantage"})
     * @Route("/admin/desavantage", name="admin_desavantage", defaults={"genre": "Désavantage"})
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminAvantages(string $genre, AvantageRepository $avantageRepository): Response
    {
        $avantages = $avantageRepository->findBy(array('genre' => $genre), array('id' => 'DESC'));

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $avantages,
            'element' => self::LISTS[$genre]['element'],
            'label' => $genre,
            'labels' => self::LISTS[$genre]['labels'],
            'genre' => 'M',
            'determinant' => 'un',
            'table_cols' => [
                'nom:Nom',
                'type:Type',
                'cout:Coût',
                'discount:Coût Discount',
                'exclusive.nom:Exclusif',
                'discountClans:Discount Clan:list',
                'discountClasses:Discount Classe:list',
                'summary:Résumé:bool',
                'access:Accès:access',
            ],
        ]);
    }

    /**
     * @Route("/admin/avantage/create", name="admin_avantage_create", defaults={"genre": "Avantage"})
     * @Route("/admin/desavantage/create", name="admin_desavantage_create", defaults={"genre": "Désavantage"})
     * @IsGranted("ROLE_MJ")
     */
    public function addAvantage(string $genre, Request $request, EntityManagerInterface $em, Unlocker $unlocker) {

        $avantage = new Avantage;
        $avantage->setGenre($genre);
        $form = $this->createForm(AdminAvantageType::class, $avantage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($avantage);
            $em->flush();

            $unlocker->sync($avantage, $form->get('unlockedBy')->getData());
            $em->flush();
            $this->addFlash('success', "L'Avantage a bien été ajouté.");

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
                && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'regles_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $avantage->getGenre(), 'subtab' => $avantage->getType() ] );

            return $this->redirectToRoute($this->listRoute($avantage));
        }
        

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'avantage',
            'label' => 'Avantage/Désavantage',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/avantage/{id}/edit", name="admin_avantage_edit")
     * @Route("/admin/desavantage/{id}/edit", name="admin_desavantage_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editAvantage(Request $request, Avantage $avantage, Unlocker $unlocker): Response {

        $form = $this->createForm(AdminAvantageType::class, $avantage);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($avantage));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $unlocker->sync($avantage, $form->get('unlockedBy')->getData());

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La Avantage a bien été modifiée.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'library'
                && !empty($request->query->get('libraryID')) && $request->query->get('libraryID') > 0)
                return $this->redirectToRoute( 'regles_library', [ 'id' => $request->query->get('libraryID') , 'tab' => $avantage->getGenre(), 'subtab' => $avantage->getType() ] );

            return $this->redirectToRoute($this->listRoute($avantage));
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'avantage' => $avantage,
            'entity' => 'avantage',
            'label' => 'Avantage/Désavantage',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/avantage/{id}/delete", name="admin_avantage_delete", methods={"POST"})
     * @Route("/admin/desavantage/{id}/delete", name="admin_desavantage_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteAvantage(Request $request, Avantage $avantage, Unlocker $unlocker): Response {

        if ($this->isCsrfTokenValid('delete' . $avantage->getId(), $request->request->get('_csrf_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $unlocker->forget($avantage);
            $entityManager->remove($avantage);
            $entityManager->flush();
            $this->addFlash('success', 'La Avantage a bien été supprimée.');
        }

        return $this->redirectToRoute($this->listRoute($avantage));
    }
}