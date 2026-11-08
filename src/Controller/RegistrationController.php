<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use App\Repository\UtilisateurRepository;
use App\Security\EmailVerifier;
use App\Service\Throttle;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    use CsrfTokenTrait;

    public const RESEND_SESSION_KEY = 'verification_resend';
    private const MAX_RESENDS_PER_HOUR = 3;

    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword(
            $userPasswordHasherInterface->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            try {
                $this->sendConfirmation($user);
            } catch (TransportExceptionInterface) {
                $entityManager->remove($user);
                $entityManager->flush();
                $this->addFlash('danger', 'L\'e-mail de confirmation n\'a pas pu être envoyé. Réessayez plus tard.');

                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            $this->addFlash('success', 'Compte créé : cliquez sur le lien envoyé à ' . $user->getEmail() . ' pour l\'activer.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/resend", name="app_verify_resend", methods={"POST"})
     */
    public function resendConfirmation(Request $request, UtilisateurRepository $userRepository, Throttle $throttle): Response
    {
        $session = $request->getSession();
        $user = $userRepository->find((int) $session->get(self::RESEND_SESSION_KEY));

        if (!$this->validToken($request, 'verify_resend') || $user === null || $user->isVerified()) {
            return $this->redirectToRoute('app_login');
        }

        if (!$throttle->allow('verify_resend', $request, self::MAX_RESENDS_PER_HOUR)) {
            $this->addFlash('danger', 'Trop de demandes envoyées, réessayez plus tard.');

            return $this->redirectToRoute('app_login');
        }

        try {
            $this->sendConfirmation($user);
        } catch (TransportExceptionInterface) {
            $this->addFlash('danger', 'L\'e-mail n\'a pas pu être envoyé. Réessayez plus tard.');

            return $this->redirectToRoute('app_login');
        }

        $session->remove(self::RESEND_SESSION_KEY);
        $this->addFlash('success', 'Un nouveau lien de confirmation a été envoyé à ' . $user->getEmail() . '.');

        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request, UtilisateurRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface) {
            $this->addFlash('verify_email_error', 'Ce lien de confirmation est invalide ou a expiré.');

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre adresse e-mail est confirmée : vous pouvez vous connecter.');

        return $this->redirectToRoute('app_login');
    }

    private function sendConfirmation(Utilisateur $user): void
    {
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address($this->getParameter('mailer_sender'), 'AXL-JDR'))
                ->to($user->getEmail())
                ->subject('Confirmez votre adresse e-mail')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }
}
