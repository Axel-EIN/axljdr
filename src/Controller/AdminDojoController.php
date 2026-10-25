<?php

namespace App\Controller;

use App\Service\TableFilter;
use App\Entity\Ecole;
use App\Service\FileHandler;
use App\Form\AdminDojoType;
use App\Repository\ClanRepository;
use App\Repository\EcoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Service\Unlocker;

class AdminDojoController extends AbstractController
{
    private const TABLE_FILTERS = [
        'classe.nom:Classe',
        'clan.nom:Clan',
    ];

    /**
     * @Route("/admin/dojo", name="admin_dojo")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminDojos(EcoleRepository $dojoRepository): Response {

        $dojos = $dojoRepository->findBy(array(), array('id' => 'DESC'));
        
        return $this->render('back_office/list-element.html.twig', [
            'elements' => $dojos,
            'element' => 'dojo',
            'label' => 'École',
            'labels' => 'Écoles',
            'gender' => 'F',
            'article' => 'une',
            'table_filters' => self::TABLE_FILTERS,
            'table_cols' => [
                'image:Image:image:NA_ECOLE',
                'nom:Nom::bold',
                'classe.nom:Classe',
                'clan.nom:Clan',
                'bonusStatNom:Bonus',
                'description:Description:bool',
                'competences:Comp.:bool',
                'equipements:Equip.:bool',
                'tech1Desc:T1:bool',
                'tech1Desc:T2:bool',
                'tech1Desc:T3:bool',
                'tech1Desc:T4:bool',
                'tech1Desc:T5:bool',
                'affinite:A:bool',
                'deficience:D:bool',
                'access:Accès:access',
                'publishedAt:Publié le:date',
            ],
        ]);
    }

     /**
     * @Route("/admin/dojo/create", name="admin_dojo_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addDojo(Request $request, EntityManagerInterface $em, ClanRepository $clanRepository, FileHandler $fileHandler, Unlocker $unlocker, TableFilter $tableFilter) {

        $dojo = new Ecole;
        $tableFilter->prefill($dojo, self::TABLE_FILTERS, $request->query->all('filter'));

        if ( !empty($request->query->get('clanID')) && $request->query->get('clanID') > 0 )
        {
            $clan = $clanRepository->find($request->query->get('clanID'));
            if ($clan !== null)
                $dojo->setClan($clan);
        }

        $form = $this->createForm(AdminDojoType::class, $dojo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = 'ecole-' . $dojo->getClan()->getNom() . '-' . $dojo->getNom();
                $dojo->setImage($fileHandler->handle($newImage, null, $prefix, 'ecoles', 'fourthird900'));
            }

            $em->persist($dojo);
            $em->flush();

            $unlocker->sync($dojo, $form->get('unlockedBy')->getData());
            $em->flush();
            $this->addFlash('success', 'L\'école a bien été ajoutée.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'dojo')
                return $this->redirectToRoute('rules_dojo', ['id' => $dojo->getId()]);
                
            return $this->redirectToRoute('admin_dojo');    
        }

        return $this->render('back_office/create.html.twig', [
            'type' => 'create',
            'entity' => 'dojo',
            'label' => 'École',
            'gender' => 'F',
            'article' => 'une',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/dojo/{id}/edit", name="admin_dojo_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editDojo(Request $request, Ecole $dojo, FileHandler $fileHandler, Unlocker $unlocker): Response {

        $form = $this->createForm(AdminDojoType::class, $dojo);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($dojo));
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = 'ecole' . '-' . $dojo->getClan()->getNom() . '-' . $dojo->getNom();
                $dojo->setImage($fileHandler->handle($newImage, $dojo->getImage(), $prefix, 'ecoles', 'fourthird900'));
            } elseif ($request->request->get('remove_image') === '1' && $dojo->getImage()) {
                $fileHandler->handle(null, $dojo->getImage(), null, 'ecoles');
                $dojo->setImage(null);
            }

            $unlocker->sync($dojo, $form->get('unlockedBy')->getData());

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'L\'école a bien été modifiée.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'dojo')
                return $this->redirectToRoute('rules_dojo', ['id' => $dojo->getId()]);

            return $this->redirectToRoute('admin_dojo');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'edit',
            'dojo' => $dojo,
            'entity' => 'dojo',
            'label' => 'École',
            'gender' => 'F',
            'article' => 'une',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/dojo/{id}/delete", name="admin_dojo_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteDojo(Request $request, Ecole $dojo, FileHandler $fileHandler, Unlocker $unlocker): Response {

        if ($this->isCsrfTokenValid('delete' . $dojo->getId(), $request->request->get('_csrf_token'))) {

            if (!$dojo->getPersonnages()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les personnages enfants au prélable !');
                return $this->redirectToRoute('admin_dojo');
            }

            $fileHandler->handle(null, $dojo->getImage(), null, 'ecoles');

            $entityManager = $this->getDoctrine()->getManager();
            $unlocker->forget($dojo);
            $entityManager->remove($dojo);
            $entityManager->flush();
            $this->addFlash('success', 'L\'école a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_dojo');
    }
}