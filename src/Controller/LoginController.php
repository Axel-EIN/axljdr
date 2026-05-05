<?php

namespace App\Controller;

use Exception;
use App\Entity\Utilisateur;
use App\Form\MonCompteMdpType;
use App\Form\ResetPassType;
use Symfony\Component\Mime\Email;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error !== null)
            $this->addFlash('danger', $error->getMessage());

        return $this->render('security/login.html.twig');
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/oubli-pass", name="app_forgotten_password")
     */
    public function passOublie(
                                Request $request,
                                UtilisateurRepository $utilisateurs,
                                MailerInterface $mailer,
                                TokenGeneratorInterface $tokenGenerator): Response
    {
        $form = $this->createForm(ResetPassType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $donnees = $form->getData();

            $user = $utilisateurs->findOneBy( [ 'email' => $donnees['email'] ] );

            if ($user === null) {
                $this->addFlash('danger', 'Cette adresse e-mail est inconnue');
                return $this->redirectToRoute('app_login');
            }

            $token = $tokenGenerator->generateToken();

            try{
                $user->setResetToken($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('app_login');
            }

            $url = $this->generateUrl('app_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            $message = new Email;
            $message
                ->from('no-reply@axl-design.me')
                ->to($user->getEmail())
                ->subject('Mot de passe oublié')
                ->text(
                    'Bonjour,'
                    . PHP_EOL . PHP_EOL .
                    'Suite à une demande de réinitialisation du mot de passe, nous avons généré ce lien pour que vous puissiez le changer'
                    . PHP_EOL . PHP_EOL .
                    'Merci de cliquer sur ce lien pour accéder à la page pour le changement de mot de passe : '
                    . $url
                );
            ;

            $mailer->send($message);

            $this->addFlash('success', 'E-mail de réinitialisation du mot de passe a été envoyé !');

            // On redirige vers la page de login
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgotten_password.html.twig',['emailForm' => $form->createView()]);
    }

    /**
     * @Route("/reset_pass/{token}", name="app_reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $user = $this->getDoctrine()->getRepository(Utilisateur::class)->findOneBy(['reset_token' => $token]);
        
        if ($user === null) {
            $this->addFlash('danger', 'Token Inconnu');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(MonCompteMdpType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mdp = $form->get('plainPassword')->getData();

            $user->setResetToken(null);

            $user->setPassword($userPasswordHasherInterface->hashPassword($user, $mdp));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Mot de passe mis à jour');

            // On redirige vers la page de connexion
            return $this->redirectToRoute('app_login');
        }else {
            // Si on n'a pas reçu les données, on affiche le formulaire
            return $this->render('mon_compte/modifier_mdp.html.twig',['form' => $form->createView()]);
        }
    }
}