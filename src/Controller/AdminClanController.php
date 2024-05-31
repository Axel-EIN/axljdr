<?php

namespace App\Controller;

use App\Entity\Clan;
use App\Service\FileHandler;
use App\Form\AdminClanType;
use App\Repository\ClanRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        $clans = $clanRepository->findAll();

        return $this->render('admin_clan/index.html.twig', [
            'controller_name' => 'AdminClanController',
            'clans' => $clans
        ]);
    }

    /**
     * @Route("/admin/clan/create", name="admin_clan_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addClan(Request $request, EntityManagerInterface $em, FileHandler $fileHandler) {

        // // TIPS Première manière de faire : DENYACCESS redirige vers page erreur 500 automatiquement
        // $this->denyAccessUnlessGranted('ROLE_MJ'); // Test si Admin sinon affiche 500
        // $this->denyAccessUnlessGranted('ROLE_JOUEUR'); // Puis test si Joueur sinon affiche 500

        // // Deuxième manière de faire : ISGRANTED renvoi true, false donc on peut faire de la logique comme on veut
        // if( !$this->isGranted('ROLE_MJ') || !$this->isGranted('ROLE_JOUEUR') ) // Ici on test si l'un ou l'autre renvoi faux on redirige vers une autre page de notre choix
        //     return $this->redirectToRoute('admin_clan');

        $clan = new Clan;
        $form = $this->createForm(AdminClanType::class, $clan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Symbol Image Handling
            $nouveauMon = $form->get('mon')->getData();
            if (!empty($nouveauMon)) {
                $prefix = 'clan-' . $clan->getNom() . '-mon';
                $clan->setMon($fileHandler->handle($nouveauMon, null, $prefix, 'clans'));
            } else { $clan->setMon('assets/img/placeholders/na_mon.png'); }

            // Banner Image Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'clan-' . $clan->getNom() . '-image';
                $clan->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'clans'));
            } else { $clan->setImage('assets/img/placeholders/na_clan.jpg'); }

            // Map Image Handling
            $nouvelleCarte = $form->get('territoireCarte')->getData();
            if (!empty($nouvelleCarte)) {
                $prefix = 'clan-' . $clan->getNom() . '-territoire';
                $clan->setTerritoireCarte($fileHandler->handle($nouvelleCarte, null, $prefix, 'clans'));
            } else { $clan->setTerritoireCarte('assets/img/placeholders/na_map.jpg'); }

            // Video Handling
            $nouvelleVideo = $form->get('video')->getData();
            if (!empty($nouvelleVideo)) {
                $prefix = 'clan-' . $clan->getNom() . '-video';
                $clan->setVideo($fileHandler->handle($nouvelleVideo, null, $prefix, '../video'));
            } else { $clan->setVideo(null); }

            $em->persist($clan);
            $em->flush();
            $this->addFlash('success', 'Le clan a bien été ajouté.');

            return $this->redirectToRoute('admin_clan');
        }
        
        return $this->render('admin_clan/create.html.twig', [
            'type' => 'Créer',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/clan/{id}/edit", name="admin_clan_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editClan(Request $request, Clan $clan, FileHandler $fileHandler): Response {

        $form = $this->createForm(AdminClanType::class, $clan);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // Icon Image Handling
            $nouveauMon = $form->get('mon')->getData();
            dump($nouveauMon);
            if (!empty($nouveauMon)) {
                $prefix = 'clan-' . $clan->getNom() . '-mon';
                $clan->setMon($fileHandler->handle($nouveauMon, $clan->getMon(), $prefix, 'clans'));
            }

            // Image Handling
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'clan-' . $clan->getNom() . '-image';
                $clan->setImage($fileHandler->handle($nouvelleImage, $clan->getImage(), $prefix, 'clans'));
            }

            // Map Image Handling
            $nouvelleCarte = $form->get('territoireCarte')->getData();
            if (!empty($nouvelleCarte)) {
                $prefix = 'clan-' . $clan->getNom() . '-territoire';
                $clan->setTerritoireCarte($fileHandler->handle($nouvelleCarte, $clan->getTerritoireCarte(), $prefix, 'clans'));
            }

            // Video Handling
            $nouvelleVideo = $form->get('video')->getData();
            if (!empty($nouvelleVideo)) {
                $prefix = 'clan-' . $clan->getNom() . '-video';
                $clan->setVideo($fileHandler->handle($nouvelleVideo, $clan->getVideo(), $prefix, '../video'));
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le clan a bien été modifié.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && ($request->query->get('redirect') == 'clan' || $request->query->get('redirect') == 'empire'))
                return $this->redirectToRoute('empire_clan', ['id' => $clan->getId()]);
            
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
    public function deleteClan(Request $request, Clan $clan, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $clan->getId(), $request->query->get('csrf'))) {

            if (!$clan->getEcoles()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les écoles enfants au prélable !');
                return $this->redirectToRoute('admin_clan');
            }

            $entityManager = $this->getDoctrine()->getManager();

            // Files Handling
            $fileHandler->handle(null, $clan->getMon(), null, 'clans');
            $fileHandler->handle(null, $clan->getImage(), null, 'clans');
            $fileHandler->handle(null, $clan->getTerritoireCarte(), null, 'clans');
            $fileHandler->handle(null, $clan->getVideo(), null, '../video');

            $entityManager->remove($clan);
            $entityManager->flush();
            $this->addFlash('success', 'La faction a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_clan');
    }
}