<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        $notice = '';

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $email = new Email;
            $email
                ->from($data['email'])
                ->to('axel.ein@gmail.com')
                ->subject($data['objet'])
                ->text(
                    'On vous a envoyé un message. Voici ce qu\'il disait :'
                        . PHP_EOL . PHP_EOL .
                        $data['message']
                        . PHP_EOL . PHP_EOL .
                        'Cet email vous a été envoyé par une boîte automatique.'
                        . PHP_EOL . PHP_EOL .
                        'Si vous souhaitez répondre à cette personne, veuillez lui écrire à l\'adresse ' . $data['email'] . ' ou encore utiliser le bouton "Répondre".'
                );
            $mailer->send($email);

            $notice = 'Votre message a bien été envoyé.';
        }

        return $this->render('contact/index.html.twig', [
            'notice' => $notice,
            'form' => $form->createView(),
        ]);
    }
}