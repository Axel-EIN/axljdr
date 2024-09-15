<?php

namespace App\Controller;

use App\Entity\Clan;
use App\Service\Uploader;
use App\Form\AdminClanType;
use App\Repository\ClanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminClanController extends AbstractController
{
    /**
     * @Route("/admin/clan", name="admin_clan")
     */
    public function afficherAdminClans(ClanRepository $clanRepository): Response {



        $clans = $clanRepository->findBy(array(), array('est_majeur' => 'DESC'));
        return $this->render('admin_clan/index.html.twig', [
            'controller_name' => 'AdminClanController',
            'clans' => $clans
        ]);
    }

    /**
     * @Route("/admin/clan/create", name="admin_clan_create")
     * @IsGranted("ROLE_MJ")
     */
    public function ajouterClan(Request $request, EntityManagerInterface $em, Uploader $uploadeur) {

        // // Première manière de faire : DENYACCESS redirige vers page erreur 500 automatiquement
        // $this->denyAccessUnlessGranted('ROLE_MJ'); // Test si Admin sinon affiche 500
        // $this->denyAccessUnlessGranted('ROLE_JOUEUR'); // Puis test si Joueur sinon affiche 500

        // // Deuxième manière de faire : ISGRANTED renvoi true, false donc on peut faire de la logique comme on veut
        // if( !$this->isGranted('ROLE_MJ') || !$this->isGranted('ROLE_JOUEUR') ) // Ici on test si l'un ou l'autre renvoi faux on redirige vers une autre page de notre choix
        //     return $this->redirectToRoute('admin_clan');

        $clan = new Clan;
        $form = $this->createForm(AdminClanType::class, $clan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nouveauMon = $form->get('mon')->getData();

            if (!empty($nouveauMon)) {
                $nouveauMonNomFichier = $uploadeur->upload($nouveauMon, 'clan-' . $clan->getNom() . '-mon', 'clans');
                $nouveauCheminRelatif = 'assets/img/clans/' . $nouveauMonNomFichier;
                $clan->setMon($nouveauCheminRelatif);
            } else { $clan->setMon('assets/img/placeholders/na_mon.png'); }

            $em->persist($clan);
            $em->flush();

            $this->addFlash('success', 'Le clan a bien été ajouté !');

            return $this->redirectToRoute('admin_clan');
        } else {
            return $this->render('admin_clan/create.html.twig', [
                'type' => 'Créer',
                'form' => $form->createView()
            ]);
        } 
    }

    /**
     * @Route("/admin/clan/{id}/edit", name="admin_clan_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editerClan(Request $request, Clan $clan, Uploader $uploadeur): Response {

        $form = $this->createForm(AdminClanType::class, $clan);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $nouveauMon = $form->get('mon')->getData();

            if (!empty($nouveauMon)) {

                $ancienMonNomFichier = basename($clan->getMon());

                $nouveauMonNomFichier = $uploadeur->upload($nouveauMon, 'clan-' . $clan->getNom() . '-mon', 'clans');
                $nouveauChemingRelatif = 'assets/img/clans/' . $nouveauMonNomFichier;
                $clan->setMon($nouveauChemingRelatif);

                $ancienMonCheminComplet = $this->getParameter('image_directory') . '/clans/' . $ancienMonNomFichier;
                $filesystem = new Filesystem();
                $filesystem->remove($ancienMonCheminComplet);

            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le clan a bien été modifié !');

            return $this->redirectToRoute('admin_clan');
        }

        return $this->renderForm('admin_clan/edit.html.twig', [
            'clan' => $clan,
            'form' => $form,
            'type' => 'Modifier',
        ]);
        
    }

    /**
     * @Route("/admin/clan/{id}/delete", name="admin_clan_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function supprimerClan(Request $request, Clan $clan): Response {

        if ($this->isCsrfTokenValid('delete' . $clan->getId(), $request->query->get('csrf'))) {

            if (!$clan->getEcoles()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les écoles enfants au prélable !');
                return $this->redirectToRoute('admin_clan');
            }

            $entityManager = $this->getDoctrine()->getManager();

            $nomMonASupprimer = basename($clan->getMon());
            $cheminMonASupprimer = $this->getParameter('image_directory') . '/clans/' . $nomMonASupprimer;

            if (file_exists($cheminMonASupprimer)) {
                $filesystem = new Filesystem();
                $filesystem->remove($cheminMonASupprimer);
            }

            $entityManager->remove($clan);
            $entityManager->flush();

            $this->addFlash('success', 'Le clan a bien été supprimé !');
        }

        return $this->redirectToRoute('admin_clan');
    }
}
