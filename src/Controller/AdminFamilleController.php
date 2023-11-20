<?php

namespace App\Controller;

use App\Entity\Famille;
use App\Service\FileHandler;
use App\Form\AdminFamilleType;
use App\Repository\ClanRepository;
use App\Repository\FamilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AdminFamilleController extends AbstractController
{
    /**
     * @Route("/admin/famille", name="admin_famille")
     */
    public function afficherAdminFamilles(FamilleRepository $familleRepository): Response {
        $familles = $familleRepository->findBy(array(), array('id' => 'DESC'));

        return $this->render('admin_famille/index.html.twig', [
            'familles' => $familles
        ]);
    }

    /**
     * @Route("/admin/famille/create", name="admin_famille_create")
     * @IsGranted("ROLE_MJ")
     */
    public function ajouterFamille(Request $request, EntityManagerInterface $em, ClanRepository $clanRepository, FileHandler $fileHandler) {

        $famille = new Famille;

        // PRE-REMPLISSAGE DU CHAMP CLAN PAR LE LIEN URL
        // -------------------------------------
        if ( !empty($request->query->get('clanID')) && $request->query->get('clanID') > 0 )
        {
            $clan = $clanRepository->find($request->query->get('clanID'));
            if ($clan !== null)
                $famille->setClan($clan);
        }

        $form = $this->createForm(AdminFamilleType::class, $famille);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nouveauMon = $form->get('mon')->getData();
            if (!empty($nouveauMon)) {
                $prefix = 'famille-' . $famille->getNom() . '-mon';
                $famille->setMon($fileHandler->handle($nouveauMon, null, $prefix, 'familles'));
            } else { $famille->setMon('assets/img/placeholders/na_mon.png'); }

            $em->persist($famille);
            $em->flush();

            $this->addFlash('success', 'Le Famille a bien été ajoutée.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'famille')
                return $this->redirectToRoute('empire_clan', ['id' => $famille->getClan()->getId()]);
            return $this->redirectToRoute('admin_famille');
        } else {
            return $this->render('admin_famille/create.html.twig', [
                'type' => 'Créer',
                'form' => $form->createView()
            ]);
        }
    }

    /**
     * @Route("/admin/famille/{id}/edit", name="admin_famille_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editerFamille(Request $request, Famille $famille, FileHandler $fileHandler): Response {

        $form = $this->createForm(AdminFamilleType::class, $famille);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $nouveauMon = $form->get('mon')->getData();
            if (!empty($nouveauMon)) {
                $prefix = 'famille-' . $famille->getNom() . '-image';
                $famille->setMon($fileHandler->handle($nouveauMon, $famille->getMon(), $prefix, 'familles'));
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La Famille a bien été modifiée.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'clan')
                return $this->redirectToRoute('empire_clan', ['id' => $famille->getClan()->getId()]);
            return $this->redirectToRoute('admin_famille');
        }

        return $this->renderForm('admin_famille/edit.html.twig', [
            'famille' => $famille,
            'form' => $form,
            'type' => 'Modifier',
        ]);
        
    }

    /**
     * @Route("/admin/famille/{id}/delete", name="admin_famille_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function supprimerFamille(Request $request, Famille $famille, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $famille->getId(), $request->query->get('csrf'))) {

            $entityManager = $this->getDoctrine()->getManager();

            $fileHandler->handle(null, $famille->getMon(), null, 'familles');

            $entityManager->remove($famille);
            $entityManager->flush();

            $this->addFlash('success', 'La Famille a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_famille');
    }
}
