<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    /**
     * @Route("/terms", name="terms")
     */
    public function terms(): Response
    {
        return $this->render('terms/index.html.twig');
    }

    /**
     * @Route("/legal-notice", name="legal_notice")
     */
    public function legalNotice(): Response
    {
        return $this->render('legal_notice/index.html.twig');
    }

    /**
     * @Route("/privacy-policy", name="privacy_policy")
     */
    public function privacyPolicy(): Response
    {
        return $this->render('privacy_policy/index.html.twig');
    }
}