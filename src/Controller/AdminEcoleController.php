<?php

namespace App\Controller;

use App\Entity\Ecole;
use App\Service\Uploader;
use App\Form\AdminEcoleType;
use App\Repository\EcoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use function PHPUnit\Framework\fileExists;

class AdminEcoleController extends AbstractController
{
    /**
     * @Route("/admin/ecole", name="admin_ecole")
     */
    public function afficherAdminEcoles(EcoleRepository $ecoleRepository): Response {

        $ecoles = $ecoleRepository->findBy(array(), array('clan' => 'ASC'));
        return $this->render('admin_ecole/index.html.twig', [
            'ecoles' => $ecoles
        ]);
    }

     /**
     * @Route("/admin/ecole/create", name="admin_ecole_create")
     * @IsGranted("ROLE_MJ")
     */
    public function ajouterEcole(Request $request, EntityManagerInterface $em, Uploader $uploadeur) {

        $ecole = new Ecole;
        $form = $this->createForm(AdminEcoleType::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nouvelleImage = $form->get('image')->getData();

            if (!empty($nouvelleImage)) {

                $nouvelleImageNomFichier = $uploadeur->upload($nouvelleImage, 'ecole-' . $ecole->getClan()->getNom() . '-' . $ecole->getNom(), 'ecoles');
                $nouveauChemingRelatif = 'assets/img/ecoles/' . $nouvelleImageNomFichier;
                $ecole->setImage($nouveauChemingRelatif);

            } else { $ecole->setImage('assets/img/placeholders/na_ecole.png'); }

            $em->persist($ecole);
            $em->flush();

            $this->addFlash('success', 'L\'école a bien été ajoutée !');

            return $this->redirectToRoute('admin_ecole');
        } else {
            return $this->render('admin_ecole/create.html.twig', [
                'type' => 'Créer',
                'form' => $form->createView()
            ]);
        }
    }

    /**
     * @Route("/admin/ecole/{id}/edit", name="admin_ecole_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editerEcole(Request $request, Ecole $ecole, Uploader $uploadeur): Response {

        $form = $this->createForm(AdminEcoleType::class, $ecole);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $nouvelleImage = $form->get('image')->getData();

            if (!empty($nouvelleImage)) {

                $ancienneImageNomFichier = basename($ecole->getImage());

                $nouvelleImageNomFichier = $uploadeur->upload($nouvelleImage, 'ecole' . '-' . $ecole->getClan()->getNom() . '-' . $ecole->getNom(), 'ecoles');
                $nouveauChemingRelatif = 'assets/img/ecoles/' . $nouvelleImageNomFichier;
                $ecole->setImage($nouveauChemingRelatif);

                $ancienneImageCheminComplet = $this->getParameter('image_directory') . '/ecoles/' . $ancienneImageNomFichier;
                if (file_exists($ancienneImageCheminComplet)) {
                    $filesystem = new Filesystem();
                    $filesystem->remove($ancienneImageCheminComplet);
                }

            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'L\'école a bien été modifiée !');

            return $this->redirectToRoute('admin_ecole');
        }

        return $this->renderForm('admin_ecole/edit.html.twig', [
            'ecole' => $ecole,
            'form' => $form,
            'type' => 'Modifier',
        ]);
        
    }

    /**
     * @Route("/admin/ecole/{id}/delete", name="admin_ecole_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function supprimerEcole(Request $request, Ecole $ecole): Response {

        if ($this->isCsrfTokenValid('delete' . $ecole->getId(), $request->query->get('csrf'))) {

            if (!$ecole->getPersonnages()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les personnages enfants au prélable !');
                return $this->redirectToRoute('admin_ecole');
            }

            $entityManager = $this->getDoctrine()->getManager();

            // Gestion de l'image
            $nomImageASupprimer = basename($ecole->getImage());
            $cheminImageASupprimer = $this->getParameter('image_directory') . '/ecoles/' . $nomImageASupprimer;

            if (file_exists($cheminImageASupprimer)) {
                $filesystem = new Filesystem();
                $filesystem->remove($cheminImageASupprimer);
            }

            $entityManager->remove($ecole);
            $entityManager->flush();

            $this->addFlash('success', 'L\'école a bien été supprimée !');
        }

        return $this->redirectToRoute('admin_ecole');
    }
}
