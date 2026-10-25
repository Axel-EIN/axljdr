<?php

namespace App\Controller;

use App\Service\TableFilter;
use App\Service\FileHandler;
use App\Service\Baliseur;
use App\Entity\Personnage;
use App\Form\AdminCharacterType;
use App\Repository\PersonnageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\Unlocker;

class AdminCharacterController extends AbstractController
{
    private const TABLE_FILTERS = [
        'estPj:Type:PJ|PNJ',
        'clan.nom:Clan',
    ];

    /**
     * @Route("/admin/character", name="admin_character")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminCharacters(PersonnageRepository $characterRepository): Response
    {
        $characters = $characterRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $characters,
            'element' => 'character',
            'label' => 'Personnage',
            'labels' => 'Personnages',
            'genre' => 'M',
            'determinant' => 'un',
            'table_filters' => self::TABLE_FILTERS,
            'table_cols' => [
              'estPj:PJ:bool',
              'joueur.pseudo:Player',
              'icone:Portrait:symbol:NA_PERSO_PORTRAIT_{genre}',
              'nom:Nom',
              'prenom:Prénom::bold',
              'illustration:Illu:image:NA_PERSO_ILLU_{genre}',
              'famille.nom:Famille',
              'ecole.nom:Ecole',
              'clan.nom:Clan',
              'access:Accès:access',
              'publishedAt:Publié le:date',
              'status:Statut:status',
              'description:Text:bool',
            ],
        ]);
    }

    /**
     * @Route("/admin/character/create", name="admin_character_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addCharacter(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, Baliseur $baliseur, Unlocker $unlocker, TableFilter $tableFilter) {

        $character = new Personnage;
        $tableFilter->prefill($character, self::TABLE_FILTERS, $request->query->all('filter'));
        $form = $this->createForm(AdminCharacterType::class, $character);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newIcon = $form->get('icone')->getData();
            if (!empty($newIcon)) {
                $prefix = 'personnage';

                if (!empty($character->getNom()))
                    $prefix = $prefix . '-' . $character->getNom();

                if (!empty($character->getPrenom()))
                    $prefix = $prefix . '-' . $character->getPrenom();

                $prefix = $prefix . '-portrait';
                $character->setIcone($fileHandler->handle($newIcon, null, $prefix, 'personnages', 'square240'));
            }

            $newIllustration = $form->get('illustration')->getData();
            if (!empty($newIllustration)) {
                $prefix = 'personnage';

                if (!empty($character->getNom()))
                    $prefix = $prefix . '-' . $character->getNom();

                if (!empty($character->getPrenom()))
                    $prefix = $prefix . '-' . $character->getPrenom();

                $prefix = $prefix . '-illustration';
                $character->setIllustration($fileHandler->handle($newIllustration, null, $prefix, 'personnages', 'vertical450'));
            }

            $character->setDescription($baliseur->baliser($character->getDescription()));

            $em->persist($character);
            $em->flush();

            $unlocker->sync($character, $form->get('unlockedBy')->getData());
            $unlocker->syncAccess($character);
            $em->flush();
            $this->addFlash('success', 'Le personnage a bien été ajouté.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'character')
                return $this->redirectToRoute('characters');

            return $this->redirectToRoute('admin_character');
        }

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'character',
            'label' => 'Personnage',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/character/{id}/edit", name="admin_character_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editCharacter(Request $request, Personnage $character, FileHandler $fileHandler, Baliseur $baliseur, Unlocker $unlocker): Response {

        $character->setDescription($baliseur->debaliser($character->getDescription()));

        $form = $this->createForm(AdminCharacterType::class, $character);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($character));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $newIcon = $form->get('icone')->getData();
            if (!empty($newIcon)) {
                $prefix = 'personnage';

                if (!empty($character->getNom()))
                    $prefix = $prefix . '-' . $character->getNom();

                if (!empty($character->getPrenom()))
                    $prefix = $prefix . '-' . $character->getPrenom();

                $prefix = $prefix . '-portrait';
                $character->setIcone($fileHandler->handle($newIcon, $character->getIcone(), $prefix, 'personnages', 'square240'));
            } elseif ($request->request->get('remove_icone') === '1' && $character->getIcone()) {
                $baliseur->remplacerIconeParDefaut($character);
                $fileHandler->handle(null, $character->getIcone(), null, 'personnages');
                $character->setIcone(null);
            }

            $newIllustration = $form->get('illustration')->getData();
            if (!empty($newIllustration)) {
                $prefix = 'personnage';

                if (!empty($character->getNom()))
                    $prefix = $prefix . '-' . $character->getNom();

                if (!empty($character->getPrenom()))
                    $prefix = $prefix . '-' . $character->getPrenom();

                $prefix = $prefix . '-illustration';
                $character->setIllustration($fileHandler->handle($newIllustration, $character->getIllustration(), $prefix, 'personnages', 'vertical450'));
            } elseif ($request->request->get('remove_illustration') === '1' && $character->getIllustration()) {
                $fileHandler->handle(null, $character->getIllustration(), null, 'personnages');
                $character->setIllustration(null);
            }

            $character->setDescription($baliseur->baliser($character->getDescription()));

            $unlocker->sync($character, $form->get('unlockedBy')->getData());
            $unlocker->syncAccess($character);

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le personnage a bien été modifié.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'character')
                return $this->redirectToRoute('characters_profile', ['id' => $character->getId()]);

            return $this->redirectToRoute('admin_character');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'character' => $character,
            'entity' => 'character',
            'label' => 'Personnage',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/character/{id}/delete", name="admin_character_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteCharacter(Request $request, Personnage $character, FileHandler $fileHandler, Baliseur $baliseur, Unlocker $unlocker): Response {

        if ($this->isCsrfTokenValid('delete' . $character->getId(), $request->request->get('_csrf_token'))) {

            $entityManager = $this->getDoctrine()->getManager();

            $baliseur->remplacerIconeParDefaut($character);
            $fileHandler->handle(null, $character->getIcone(), null, 'personnages');
            $fileHandler->handle(null, $character->getIllustration(), null, 'personnages');

            $unlocker->forget($character);
            $entityManager->remove($character);
            $entityManager->flush();
            $this->addFlash('success', 'Le personnage a bien été supprimé.');
        }

        return $this->redirectToRoute('admin_character');
    }
}