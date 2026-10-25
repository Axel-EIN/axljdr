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
use App\Service\Unlocker;

class AdminClanController extends AbstractController
{
    /**
     * @Route("/admin/clan", name="admin_clan")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminClans(ClanRepository $clanRepository): Response
    {
        $clans = $clanRepository->findBy(array(), array('id' => 'DESC'));

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $clans,
            'element' => 'clan',
            'label' => 'Clan',
            'labels' => 'Clans',
            'genre' => 'M',
            'determinant' => 'un',
            'table_cols' => [
                'mon:Mon:symbol:NA_MON',
                'nom:Nom::bold',
                'image:Image:image:NA_CLAN',
                'chef.prenom:Chef',
                'couleur:Couleur:color',
                'description:Description:bool',
                'citation:Citation:bool',
                'longDescription:LongText:bool',
                'type:Type:faction',
                'territoireDesc:CarteText:bool',
                'video:Video:bool',
                'access:Accès:access',
                'publishedAt:Publié le:date',
            ],
        ]);
    }

    /**
     * @Route("/admin/clan/create", name="admin_clan_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addClan(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, Unlocker $unlocker) {

        $clan = new Clan;
        $form = $this->createForm(AdminClanType::class, $clan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newMon = $form->get('mon')->getData();
            if (!empty($newMon)) {
                $prefix = 'clan-' . $clan->getNom() . '-mon';
                $clan->setMon($fileHandler->handle($newMon, null, $prefix, 'clans', 'square320'));
            }

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = 'clan-' . $clan->getNom() . '-image';
                $clan->setImage($fileHandler->handle($newImage, null, $prefix, 'clans', 'landscape720'));
            }

            $newVideo = $form->get('video')->getData();
            if (!empty($newVideo)) {
                $prefix = 'clan-' . $clan->getNom() . '-video';
                $clan->setVideo($fileHandler->handle($newVideo, null, $prefix, 'video'));
            }

            $em->persist($clan);
            $em->flush();

            $unlocker->sync($clan, $form->get('unlockedBy')->getData());
            $em->flush();
            $this->addFlash('success', 'Le clan a bien été ajouté.');

            return $this->redirectToRoute('admin_clan');
        }
        

        return $this->render('back_office/create.html.twig', [
            'type' => 'Créer',
            'entity' => 'clan',
            'label' => 'Clan',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/clan/{id}/edit", name="admin_clan_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editClan(Request $request, Clan $clan, FileHandler $fileHandler, Unlocker $unlocker): Response {

        $form = $this->createForm(AdminClanType::class, $clan);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($clan));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $newMon = $form->get('mon')->getData();

            if (!empty($newMon)) {
                $prefix = 'clan-' . $clan->getNom() . '-mon';
                $clan->setMon($fileHandler->handle($newMon, $clan->getMon(), $prefix, 'clans', 'square320'));
            } elseif ($request->request->get('remove_mon') === '1' && $clan->getMon()) {
                $fileHandler->handle(null, $clan->getMon(), null, 'clans');
                $clan->setMon(null);
            }

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = 'clan-' . $clan->getNom() . '-image';
                $clan->setImage($fileHandler->handle($newImage, $clan->getImage(), $prefix, 'clans', 'landscape720'));
            } elseif ($request->request->get('remove_image') === '1' && $clan->getImage()) {
                $fileHandler->handle(null, $clan->getImage(), null, 'clans');
                $clan->setImage(null);
            }

            $newVideo = $form->get('video')->getData();
            if (!empty($newVideo)) {
                $prefix = 'clan-' . $clan->getNom() . '-video';
                $clan->setVideo($fileHandler->handle($newVideo, $clan->getVideo(), $prefix, 'video'));
            }

            $unlocker->sync($clan, $form->get('unlockedBy')->getData());

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le clan a bien été modifié.');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'clan' )
                return $this->redirectToRoute('world_clan', ['id' => $clan->getId()]);
            
            return $this->redirectToRoute('admin_clan');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'Modifier',
            'clan' => $clan,
            'entity' => 'clan',
            'label' => 'Clan',
            'genre' => 'M',
            'determinant' => 'un',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/clan/{id}/delete", name="admin_clan_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteClan(Request $request, Clan $clan, FileHandler $fileHandler, Unlocker $unlocker): Response {

        if ($this->isCsrfTokenValid('delete' . $clan->getId(), $request->request->get('_csrf_token'))) {

            if (!$clan->getEcoles()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les écoles enfants au prélable !');
                return $this->redirectToRoute('admin_clan');
            }

            $entityManager = $this->getDoctrine()->getManager();

            $fileHandler->handle(null, $clan->getMon(), null, 'clans');
            $fileHandler->handle(null, $clan->getImage(), null, 'clans');
            $fileHandler->handle(null, $clan->getVideo(), null, 'video');

            $unlocker->forget($clan);
            $entityManager->remove($clan);
            $entityManager->flush();
            $this->addFlash('success', 'La faction a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_clan');
    }
}