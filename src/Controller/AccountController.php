<?php

namespace App\Controller;

use App\Entity\Personnage;
use App\Entity\Utilisateur;
use App\Service\CurrentPlayer;
use App\Service\FileHandler;
use App\Form\AccountPasswordType;
use App\Form\AccountAvatarType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_USER")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/account", name="account")
     */
    public function index(CurrentPlayer $currentPlayer): Response
    {
        return $this->render('account/index.html.twig', [
            'main_character' => $currentPlayer->mainCharacter(),
        ]);
    }

    /**
     * @Route("/account/password/edit", name="account_password")
     */
    public function editPassword(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(AccountPasswordType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $user->setPassword(
                $userPasswordHasherInterface->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a bien été modifié !');
            return $this->redirectToRoute('account');
        }

        return $this->render('account/edit-password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/account/main-character", name="account_main_character", methods={"POST"})
     */
    public function chooseMainCharacter(Request $request): Response
    {
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('main-character', $request->request->get('_csrf_token'))) {

            $chosen = $user->getPersonnages()->filter(
                fn (Personnage $character) => $character->getId() === $request->request->getInt('character')
            )->first();

            $user->setMainCharacter($chosen ?: null);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Votre personnage principal a bien été modifié !');
        }

        return $this->redirectToRoute('account');
    }

    /**
     * @Route("/account/navigation-mode", name="account_navigation_mode", methods={"POST"})
     */
    public function chooseNavigationMode(Request $request): Response
    {
        $user = $this->getUser();

        if ($user->isGameMaster() && $this->isCsrfTokenValid('navigation-mode', $request->request->get('_csrf_token'))) {

            $user->setPlayerMode($request->request->get('mode') === Utilisateur::MODE_PLAYER);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $user->isPlayerMode()
                ? 'Vous naviguez en mode Joueur.'
                : 'Vous naviguez en mode MJ.');
        }

        return $this->redirectToRoute('account');
    }

    /**
     * @Route("/account/avatar/edit", name="account_avatar")
     */
    public function editAvatar(Request $request, FileHandler $fileHandler): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(AccountAvatarType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $newAvatar = $form->get('avatar')->getData();

            if (!empty($newAvatar)) {
                $user->setAvatar($fileHandler->handle(
                    $newAvatar,
                    $user->getAvatar(),
                    'avatar-' . $user->getPseudo(),
                    'avatars',
                    'square320'
                ));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre avatar a bien été modifié !');
            return $this->redirectToRoute('account');
        }

        return $this->render('account/edit-avatar.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
