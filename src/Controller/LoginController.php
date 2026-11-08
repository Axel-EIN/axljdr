<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\Throttle;
use App\Form\AccountPasswordType;
use App\Form\ResetPassType;
use Symfony\Component\Mime\Email;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class LoginController extends AbstractController
{
    private const MAX_RESETS_PER_HOUR = 3;
    private const RESET_LIFETIME = '1 hour';

    /**
     * @Route("/login", name="app_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {

        $error = $authenticationUtils->getLastAuthenticationError();
        $unverified = $error instanceof AccountStatusException && $error->getUser() instanceof Utilisateur;

        if ($error !== null) {
            $this->addFlash('danger', $this->loginErrorMessage($error));
        }

        if ($unverified) {
            $request->getSession()->set(RegistrationController::RESEND_SESSION_KEY, $error->getUser()->getId());
        }

        return $this->render('security/login.html.twig', ['resend_verification' => $unverified]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/forgot-password", name="app_forgotten_password")
     */
    public function forgotPassword(
                                    Request $request,
                                    UtilisateurRepository $userRepository,
                                    MailerInterface $mailer,
                                    TokenGeneratorInterface $tokenGenerator,
                                    Throttle $throttle): Response
    {
        $form = $this->createForm(ResetPassType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$throttle->allow('forgot_password', $request, self::MAX_RESETS_PER_HOUR)) {
                $this->addFlash('danger', 'Trop de demandes envoyées, réessayez plus tard.');

                return $this->redirectToRoute('app_forgotten_password');
            }

            $user = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);

            if ($user !== null) {
                $token = $tokenGenerator->generateToken();
                $user->setResetToken(hash('sha256', $token));
                $this->getDoctrine()->getManager()->flush();

                try {
                    $mailer->send($this->resetEmail($user, $token));
                } catch (TransportExceptionInterface $e) {
                    $this->addFlash('danger', 'L\'e-mail n\'a pas pu être envoyé. Réessayez plus tard.');

                    return $this->redirectToRoute('app_forgotten_password');
                }
            }

            $this->addFlash('success', 'Si un compte utilise cette adresse, un e-mail de réinitialisation vient de lui être envoyé.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgotten_password.html.twig',['emailForm' => $form->createView()]);
    }

    /**
     * @Route("/reset-password/{token}", name="app_reset_password")
     */
    public function resetPassword(Request $request, string $token, UtilisateurRepository $userRepository, UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $user = $userRepository->findOneBy(['reset_token' => hash('sha256', $token)]);

        if ($user === null || $user->getResetRequestedAt() < new \DateTimeImmutable('-' . self::RESET_LIFETIME)) {
            $this->addFlash('danger', 'Ce lien de réinitialisation est invalide ou a expiré. Faites une nouvelle demande.');

            return $this->redirectToRoute('app_forgotten_password');
        }

        $form = $this->createForm(AccountPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasherInterface->hashPassword($user, $form->get('plainPassword')->getData()));
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Mot de passe mis à jour');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('account/edit-password.html.twig',['form' => $form->createView()]);
    }

    private function loginErrorMessage(AuthenticationException $error): string
    {
        return match (true) {
            $error instanceof CustomUserMessageAuthenticationException,
            $error instanceof CustomUserMessageAccountStatusException => $error->getMessageKey(),
            $error instanceof BadCredentialsException => 'Pseudo ou mot de passe incorrect.',
            $error instanceof InvalidCsrfTokenException => 'Votre session a expiré. Réessayez.',
            default => 'La connexion a échoué. Réessayez.',
        };
    }

    private function resetEmail(Utilisateur $user, string $token): Email
    {
        $url = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        return (new Email())
            ->from($this->getParameter('mailer_sender'))
            ->to($user->getEmail())
            ->subject('Mot de passe oublié')
            ->text(
                'Bonjour,'
                . PHP_EOL . PHP_EOL
                . 'Pour choisir un nouveau mot de passe, ouvrez ce lien : ' . $url
                . PHP_EOL . PHP_EOL
                . 'Ce lien est valable une heure et ne sert qu\'une fois. Si vous n\'avez rien demandé, ignorez cet e-mail.'
            );
    }
}