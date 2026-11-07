<?php

namespace App\Controller;

use App\Service\Throttle;
use App\Form\ContactType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ContactController extends AbstractController
{
    private const SHOWN_AT = 'contact_shown_at';
    private const MIN_DELAY = 3;
    private const MAX_PER_HOUR = 3;

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request, MailerInterface $mailer, Throttle $throttle): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        $session = $request->getSession();
        $shownAt = $session->get(self::SHOWN_AT);

        if (!$form->isSubmitted()) {
            $session->set(self::SHOWN_AT, time());
        }

        if ($form->isSubmitted() && $form->isValid()) {

            if (!empty($form->get('website')->getData()) || $shownAt === null || time() - $shownAt < self::MIN_DELAY) {
                return $this->sent($request);
            }

            if (!$throttle->allow('contact', $request, self::MAX_PER_HOUR)) {
                $this->addFlash('danger', 'Trop de messages envoyés, réessayez plus tard.');

                return $this->renderForm('contact/index.html.twig', ['form' => $form]);
            }

            try {
                $mailer->send($this->email($form));
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('danger', 'Le message n\'a pas pu être envoyé. Réessayez plus tard.');

                return $this->renderForm('contact/index.html.twig', ['form' => $form]);
            }

            return $this->sent($request);
        }

        return $this->renderForm('contact/index.html.twig', ['form' => $form]);
    }

    private function sent(Request $request): Response
    {
        $request->getSession()->remove(self::SHOWN_AT);
        $this->addFlash('success', 'Votre message a bien été envoyé.');

        return $this->redirectToRoute('contact');
    }

    private function email(FormInterface $form): Email
    {
        $data = $form->getData();

        return (new Email())
            ->from($this->getParameter('mailer_sender'))
            ->replyTo($data['email'])
            ->to($this->getParameter('contact_recipient'))
            ->subject('[AXL-JDR Contact] ' . $data['objet'])
            ->text(
                'Message envoyé depuis le formulaire de contact par ' . $data['email'] . ' :'
                . PHP_EOL . PHP_EOL
                . $data['message']
                . PHP_EOL . PHP_EOL
                . 'Pour lui répondre, utilisez le bouton « Répondre ».'
            );
    }
}
