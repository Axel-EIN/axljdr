<?php

namespace App\Controller;

use App\Service\FileHandler;
use App\Entity\Utilisateur;
use App\Form\AdminUtilisateurType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUtilisateurController extends AbstractController
{
    /**
     * @Route("/admin/utilisateur", name="admin_utilisateur")
     * @IsGranted("ROLE_ADMIN")
     */
    public function afficherUtilisateurs(UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateurs = $utilisateurRepository->findBy(array(), array('id' => 'ASC'));

        return $this->render('admin_utilisateur/index.html.twig', [
            'controller_name' => 'AdminUtilisateurController',
            'utilisateurs' => $utilisateurs
        ]);
    }

    /**
     * @Route("/admin/utilisateur/create", name="admin_utilisateur_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function ajouterUtilisateur(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, UserPasswordHasherInterface $userPasswordHasherInterface) {

        $utilisateur = new Utilisateur;
        $form = $this->createForm(AdminUtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $utilisateur->setPassword(
                $userPasswordHasherInterface->hashPassword(
                    $utilisateur,
                    $form->get('password')->getData()
                )
            );

            $nouvelleAvatar = $form->get('avatar')->getData();
            if (!empty($nouvelleAvatar)) {
                $prefix = 'avatar-' . $utilisateur->getPseudo();
                $utilisateur->setAvatar($fileHandler->handle($nouvelleAvatar, null, $prefix, 'avatars'));
            } else { $utilisateur->setAvatar('assets/img/placeholders/na_mon.png'); }

            $em->persist($utilisateur);
            $em->flush();

            $this->addFlash('success', 'L\'utilisateur a bien été crée.');

            return $this->redirectToRoute('admin_utilisateur');
        } else {
            return $this->render('admin_utilisateur/create.html.twig', [
                'type' => 'Créer',
                'form' => $form->createView()
            ]);
        }
    }

    /**
     * @Route("/admin/utilisateur/{id}/edit", name="admin_utilisateur_edit")
     * @IsGranted("ROLE_ADMIN")
     */
    public function editerUtilisateur(Request $request, Utilisateur $utilisateur, FileHandler $fileHandler, UserPasswordHasherInterface $userPasswordHasherInterface): Response {

        $ancien_mdp = $utilisateur->getPassword();

        $utilisateur->setPassword('******');

        $form = $this->createForm(AdminUtilisateurType::class, $utilisateur);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            if ($form->get('password')->getData() != '******') {
                $utilisateur->setPassword(
                    $userPasswordHasherInterface->hashPassword(
                        $utilisateur,
                        $form->get('password')->getData()
                    )
                );
            } else {
                $utilisateur->setPassword($ancien_mdp);
            }

            $nouvelleAvatar = $form->get('avatar')->getData();
            if (!empty($nouvelleAvatar)) {
                $prefix = 'avatar-' . $utilisateur->getPseudo();
                $utilisateur->setAvatar($fileHandler->handle($nouvelleAvatar, $utilisateur->getAvatar(), $prefix, 'avatars'));
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'L\'utilisateur a bien été modifié.');

            return $this->redirectToRoute('admin_utilisateur');
        }

        return $this->renderForm('admin_utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
            'type' => 'Modifier',
        ]); 
    }

    /**
     * @Route("/admin/utilisateur/{id}/delete", name="admin_utilisateur_delete", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function supprimerUtilisateur(Request $request, Utilisateur $utilisateur, FileHandler $fileHandler): Response {
        if ($this->isCsrfTokenValid('delete' . $utilisateur->getId(), $request->query->get('csrf'))) {

            $entityManager = $this->getDoctrine()->getManager();

            $fileHandler->handle(null, $utilisateur->getAvatar(), null, 'avatars');

            $entityManager->remove($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a bien été supprimé !');
        }

        return $this->redirectToRoute('admin_utilisateur');
        // return $this->redirectToRoute('admin_utilisateur', [], Response::HTTP_SEE_OTHER);
    }
}
