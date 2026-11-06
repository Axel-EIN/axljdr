<?php

namespace App\Controller;

use App\Service\FileHandler;
use App\Service\DeletionGuard;
use App\Entity\Utilisateur;
use App\Form\AdminUserType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUserController extends AbstractController
{
    /**
     * @Route("/admin/user", name="admin_user")
     * @IsGranted("ROLE_ADMIN")
     */
    public function viewAdminUsers(UtilisateurRepository $userRepository): Response
    {
        $users = $userRepository->findBy(array(), array('id' => 'ASC'));

        return $this->render('admin_user/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/admin/user/create", name="admin_user_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function addUser(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, UserPasswordHasherInterface $userPasswordHasherInterface) {

        $user = new Utilisateur;
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->applyPassword($user, $form, $userPasswordHasherInterface);

            $newAvatar = $form->get('avatar')->getData();
            if (!empty($newAvatar)) {
                $prefix = 'avatar-' . $user->getPseudo();
                $user->setAvatar($fileHandler->handle($newAvatar, null, $prefix, 'avatars', 'square320'));
            }

            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'L\'utilisateur a bien été crée.');

            return $this->redirectToRoute('admin_user');
        }
        
        return $this->render('back_office/create.html.twig', [
            'entity' => 'user',
            'label' => 'Utilisateur',
            'article' => 'un',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/user/{id}/edit", name="admin_user_edit")
     * @IsGranted("ROLE_ADMIN")
     */
    public function editUser(Request $request, Utilisateur $user, FileHandler $fileHandler, UserPasswordHasherInterface $userPasswordHasherInterface): Response {

        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $this->isSelf($user) && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $form->get('roles')->addError(new FormError('Vous ne pouvez pas retirer votre propre rôle Administrateur.'));
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $this->applyPassword($user, $form, $userPasswordHasherInterface);

            $newAvatar = $form->get('avatar')->getData();
            if (!empty($newAvatar)) {
                $prefix = 'avatar-' . $user->getPseudo();
                $user->setAvatar($fileHandler->handle($newAvatar, $user->getAvatar(), $prefix, 'avatars', 'square320'));
            } elseif ($request->request->get('remove_avatar') === '1' && $user->getAvatar()) {
                $fileHandler->handle(null, $user->getAvatar(), null, 'avatars');
                $user->setAvatar(null);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'L\'utilisateur a bien été modifié.');
            return $this->redirectToRoute('admin_user');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'entity' => 'user',
            'label' => 'Utilisateur',
            'article' => 'un',
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/user/{id}/delete", name="admin_user_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteUser(Request $request, Utilisateur $user, FileHandler $fileHandler, DeletionGuard $deletionGuard): Response {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_csrf_token'))) {

            if ($this->isSelf($user)) {
                $this->addFlash('danger', 'Vous ne pouvez pas supprimer votre propre compte.');
                return $this->redirectToRoute('admin_user');
            }

            $refusal = $deletionGuard->refusal($user, $user->getPseudo());

            if ($refusal !== null) {
                $this->addFlash('danger', $refusal);
                return $this->redirectToRoute('admin_user');
            }

            $entityManager = $this->getDoctrine()->getManager();

            $fileHandler->handle(null, $user->getAvatar(), null, 'avatars');

            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'L\'utilisateur a bien été supprimé.');
        }

        return $this->redirectToRoute('admin_user');
    }

    private function isSelf(Utilisateur $user): bool
    {
        $current = $this->getUser();

        return $current instanceof Utilisateur && $current->getId() === $user->getId();
    }

    private function applyPassword(Utilisateur $user, FormInterface $form, UserPasswordHasherInterface $passwordHasher): void
    {
        $plainPassword = $form->get('plainPassword')->getData();

        if (!empty($plainPassword)) {
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        }
    }
}