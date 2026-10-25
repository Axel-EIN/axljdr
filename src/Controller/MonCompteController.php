<?php

namespace App\Controller;

use App\Entity\Personnage;
use App\Entity\Utilisateur;
use App\Service\CurrentPlayer;
use App\Service\FileHandler;
use App\Form\MonCompteMdpType;
use App\Form\MonCompteAvatarType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_USER")
 */
class MonCompteController extends AbstractController
{
    /**
     * @Route("/mon_compte", name="mon_compte")
     */
    public function afficherMonCompte(CurrentPlayer $currentPlayer): Response
    {
        return $this->render('mon_compte/index.html.twig', [
            'principal' => $currentPlayer->mainCharacter(),
        ]);
    }

    /**
     * @Route("/mon_compte/password/edit", name="mon_compte_mdp")
     */
    public function modifierMotDePasse(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(MonCompteMdpType::class, $user);

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
            return $this->redirectToRoute('mon_compte');
        }

        return $this->render('mon_compte/modifier_mdp.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mon_compte/main-character", name="mon_compte_main_character", methods={"POST"})
     */
    public function choisirPersonnagePrincipal(Request $request): Response
    {
        $utilisateur = $this->getUser();

        if ($this->isCsrfTokenValid('main-character', $request->request->get('_csrf_token'))) {

            $choisi = $utilisateur->getPersonnages()->filter(
                fn (Personnage $personnage) => $personnage->getId() === $request->request->getInt('personnage')
            )->first();

            $utilisateur->setMainCharacter($choisi ?: null);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Votre personnage principal a bien été modifié !');
        }

        return $this->redirectToRoute('mon_compte');
    }

    /**
     * @Route("/mon_compte/navigation-mode", name="mon_compte_navigation_mode", methods={"POST"})
     */
    public function choisirModeNavigation(Request $request): Response
    {
        $utilisateur = $this->getUser();

        if ($utilisateur->isGameMaster() && $this->isCsrfTokenValid('navigation-mode', $request->request->get('_csrf_token'))) {

            $utilisateur->setPlayerMode($request->request->get('mode') === Utilisateur::MODE_PLAYER);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $utilisateur->isPlayerMode()
                ? 'Vous naviguez en mode Joueur.'
                : 'Vous naviguez en mode MJ.');
        }

        return $this->redirectToRoute('mon_compte');
    }

    /**
     * @Route("/mon_compte/avatar/edit", name="mon_compte_avatar")
     */
    public function modifierAvatar(Request $request, FileHandler $fileHandler): Response
    {
        $utilisateur = $this->getUser();
        $form = $this->createForm(MonCompteAvatarType::class, $utilisateur);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $nouveauAvatar = $form->get('avatar')->getData();

            if (!empty($nouveauAvatar)) {
                $utilisateur->setAvatar($fileHandler->handle(
                    $nouveauAvatar,
                    $utilisateur->getAvatar(),
                    'avatar-' . $utilisateur->getPseudo(),
                    'avatars',
                    'square320'
                ));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($utilisateur);
            $em->flush();

            $this->addFlash('success', 'Votre avatar a bien été modifié !');
            return $this->redirectToRoute('mon_compte');
        }

        return $this->render('mon_compte/modifier_avatar.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}