<?php

namespace App\Controller;

use App\Service\FileHandler;
use App\Service\Baliseur;
use App\Entity\Personnage;
use App\Form\AdminPersonnageType;
use App\Repository\PersonnageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\Unlocker;

class AdminPersonnageController extends AbstractController
{
    private const LISTS = [
        'PJ' => ['element' => 'personnage', 'labels' => 'PJs'],
        'PNJ' => ['element' => 'pnj', 'labels' => 'PNJs'],
    ];

    private function listRoute(Personnage $personnage): string
    {
        return 'admin_' . self::LISTS[$personnage->getEstPj() ? 'PJ' : 'PNJ']['element'];
    }

    /**
     * @Route("/admin/personnage", name="admin_personnage", defaults={"type": "PJ"})
     * @Route("/admin/pnj", name="admin_pnj", defaults={"type": "PNJ"})
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminPersonnages(string $type, PersonnageRepository $personnageRepository): Response
    {
        $personnages = $personnageRepository->findBy( ['estPj' => $type === 'PJ'] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $personnages,
            'element' => self::LISTS[$type]['element'],
            'label' => $type,
            'labels' => self::LISTS[$type]['labels'],
            'genre' => 'M',
            'determinant' => 'un',
            'table_cols' => array_merge($type === 'PJ' ? ['joueur.pseudo:Player'] : [], [
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
            ]),
        ]);
    }

    /**
     * @Route("/admin/personnage/create", name="admin_personnage_create", defaults={"type": "PJ"})
     * @Route("/admin/pnj/create", name="admin_pnj_create", defaults={"type": "PNJ"})
     * @IsGranted("ROLE_MJ")
     */
    public function addPersonnage(string $type, Request $request, EntityManagerInterface $em, FileHandler $fileHandler, Baliseur $baliseur, Unlocker $unlocker) {

        $personnage = new Personnage;
        $personnage->setEstPj($type === 'PJ');
        $form = $this->createForm(AdminPersonnageType::class, $personnage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nouvelleIcone = $form->get('icone')->getData();
            if (!empty($nouvelleIcone)) {
                $prefix = 'personnage';

                if (!empty($personnage->getNom()))
                    $prefix = $prefix . '-' . $personnage->getNom();

                if (!empty($personnage->getPrenom()))
                    $prefix = $prefix . '-' . $personnage->getPrenom();

                $prefix = $prefix . '-portrait';
                $personnage->setIcone($fileHandler->handle($nouvelleIcone, null, $prefix, 'personnages', 'square240'));
            }

            $nouvelleIllustration = $form->get('illustration')->getData();
            if (!empty($nouvelleIllustration)) {
                $prefix = 'personnage';

                if (!empty($personnage->getNom()))
                    $prefix = $prefix . '-' . $personnage->getNom();

                if (!empty($personnage->getPrenom()))
                    $prefix = $prefix . '-' . $personnage->getPrenom();

                $prefix = $prefix . '-illustration';
                $personnage->setIllustration($fileHandler->handle($nouvelleIllustration, null, $prefix, 'personnages', 'vertical450'));
            }

            $personnage->setDescription($baliseur->baliser($personnage->getDescription()));

            $em->persist($personnage);
            $em->flush();

            $unlocker->sync($personnage, $form->get('unlockedBy')->getData());
            $unlocker->syncAccess($personnage);
            $em->flush();
            $this->addFlash('success', 'Le personnage a bien été ajouté.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'personnage')
                return $this->redirectToRoute('personnages');

            return $this->redirectToRoute($this->listRoute($personnage));
        }

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'personnage',
            'label' => 'Personnage',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/personnage/{id}/edit", name="admin_personnage_edit")
     * @Route("/admin/pnj/{id}/edit", name="admin_pnj_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editPersonnage(Request $request, Personnage $personnage, FileHandler $fileHandler, Baliseur $baliseur, Unlocker $unlocker): Response {

        $personnage->setDescription($baliseur->debaliser($personnage->getDescription()));

        $form = $this->createForm(AdminPersonnageType::class, $personnage);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($personnage));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $nouvelleIcone = $form->get('icone')->getData();
            if (!empty($nouvelleIcone)) {
                $prefix = 'personnage';

                if (!empty($personnage->getNom()))
                    $prefix = $prefix . '-' . $personnage->getNom();

                if (!empty($personnage->getPrenom()))
                    $prefix = $prefix . '-' . $personnage->getPrenom();

                $prefix = $prefix . '-portrait';
                $personnage->setIcone($fileHandler->handle($nouvelleIcone, $personnage->getIcone(), $prefix, 'personnages', 'square240'));
            } elseif ($request->request->get('remove_icone') === '1' && $personnage->getIcone()) {
                $fileHandler->handle(null, $personnage->getIcone(), null, 'personnages');
                $personnage->setIcone(null);
            }

            $nouvelleIllustration = $form->get('illustration')->getData();
            if (!empty($nouvelleIllustration)) {
                $prefix = 'personnage';

                if (!empty($personnage->getNom()))
                    $prefix = $prefix . '-' . $personnage->getNom();

                if (!empty($personnage->getPrenom()))
                    $prefix = $prefix . '-' . $personnage->getPrenom();

                $prefix = $prefix . '-illustration';
                $personnage->setIllustration($fileHandler->handle($nouvelleIllustration, $personnage->getIllustration(), $prefix, 'personnages', 'vertical450'));
            } elseif ($request->request->get('remove_illustration') === '1' && $personnage->getIllustration()) {
                $fileHandler->handle(null, $personnage->getIllustration(), null, 'personnages');
                $personnage->setIllustration(null);
            }

            $personnage->setDescription($baliseur->baliser($personnage->getDescription()));

            $unlocker->sync($personnage, $form->get('unlockedBy')->getData());
            $unlocker->syncAccess($personnage);

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le personnage a bien été modifié.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'personnage')
                return $this->redirectToRoute('personnage_profil', ['id' => $personnage->getId()]);

            return $this->redirectToRoute($this->listRoute($personnage));
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'personnage' => $personnage,
            'entity' => 'personnage',
            'label' => 'Personnage',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/personnage/{id}/delete", name="admin_personnage_delete", methods={"POST"})
     * @Route("/admin/pnj/{id}/delete", name="admin_pnj_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deletePersonnage(Request $request, Personnage $personnage, FileHandler $fileHandler, Unlocker $unlocker): Response {

        if ($this->isCsrfTokenValid('delete' . $personnage->getId(), $request->request->get('_csrf_token'))) {

            $entityManager = $this->getDoctrine()->getManager();

            $fileHandler->handle(null, $personnage->getIcone(), null, 'personnages');
            $fileHandler->handle(null, $personnage->getIllustration(), null, 'personnages');

            $unlocker->forget($personnage);
            $entityManager->remove($personnage);
            $entityManager->flush();
            $this->addFlash('success', 'Le personnage a bien été supprimé.');
        }

        return $this->redirectToRoute($this->listRoute($personnage));
    }
}