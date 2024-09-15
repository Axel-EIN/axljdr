<?php

namespace App\Controller;

use App\Entity\Famille;
use App\Service\Uploader;
use App\Form\AdminFamilleType;
use App\Repository\FamilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
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
        $familles = $familleRepository->findAll();
        return $this->render('admin_famille/index.html.twig', [
            'familles' => $familles
        ]);
    }

    /**
     * @Route("/admin/famille/create", name="admin_famille_create")
     * @IsGranted("ROLE_MJ")
     */
    public function ajouterFamille(Request $request, EntityManagerInterface $em, Uploader $uploadeur) {

        $famille = new Famille;
        $form = $this->createForm(AdminFamilleType::class, $famille);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nouveauMon = $form->get('mon')->getData();

            if (!empty($nouveauMon)) {
                $nouveauMonNomFichier = $uploadeur->upload($nouveauMon, 'famille-' . $famille->getNom() . '-mon', 'familles');
                $nouveauCheminRelatif = 'assets/img/familles/' . $nouveauMonNomFichier;
                $famille->setMon($nouveauCheminRelatif);
            } else { $famille->setMon('assets/img/placeholders/na_mon.png'); }

            $em->persist($famille);
            $em->flush();

            $this->addFlash('success', 'Le Famille a bien été ajoutée !');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'famille')
                return $this->redirectToRoute('empire_famille', ['id' => $famille->getId()]);
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
    public function editerFamille(Request $request, Famille $famille, Uploader $uploadeur): Response {

        $form = $this->createForm(AdminFamilleType::class, $famille);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $nouveauMon = $form->get('mon')->getData();

            if (!empty($nouveauMon)) {
                $ancienMonNomFichier = basename($famille->getMon());

                $nouveauMonNomFichier = $uploadeur->upload($nouveauMon, 'famille-' . $famille->getNom() . '-image', 'familles');
                $nouveauCheminRelatif = 'assets/img/familles/' . $nouveauMonNomFichier;
                $famille->setMon($nouveauCheminRelatif);

                // Efface l'ancien fichier uniquement s'il a réussit à récupérer depuis l'url un nom de fichier et que ce n'est pas un dossier (protection)
                if ($ancienMonNomFichier) {
                    $ancienMonCheminComplet = $this->getParameter('image_directory') . '/familles/' . $ancienMonNomFichier;
                    if (is_dir($ancienMonCheminComplet) == false) {
                        $filesystem = new Filesystem();
                        $filesystem->remove($ancienMonCheminComplet);
                    }
                }
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La Famille a bien été modifiée !');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'famille')
                return $this->redirectToRoute('empire_famille', ['id' => $famille->getId()]);
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
    public function supprimerFamille(Request $request, Famille $famille): Response {

        if ($this->isCsrfTokenValid('delete' . $famille->getId(), $request->query->get('csrf'))) {

            $entityManager = $this->getDoctrine()->getManager();

            $nomImageASupprimer = basename($famille->getMon());
            $cheminImageASupprimer = $this->getParameter('image_directory') . '/familles/' . $nomImageASupprimer;

            if (file_exists($cheminImageASupprimer) && is_dir($cheminImageASupprimer) == false) {
                $filesystem = new Filesystem();
                $filesystem->remove($cheminImageASupprimer);
            }

            $entityManager->remove($famille);
            $entityManager->flush();

            $this->addFlash('success', 'La Famille a bien été supprimée !');
        }

        return $this->redirectToRoute('admin_famille');
    }
}
