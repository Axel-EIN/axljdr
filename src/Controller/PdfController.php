<?php

namespace App\Controller;

use App\Service\EntityRegistry;
use App\Service\Visibility;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
    /**
     * @Route("/pdf/{key}/{id}", name="pdf", requirements={"key"="rule|lore|library", "id"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function show(string $key, int $id, EntityManagerInterface $em, Visibility $visibility): BinaryFileResponse
    {
        $element = $em->find(EntityRegistry::className($key), $id);

        if ($element === null || empty($element->getPdf()) || !$visibility->isReadable($element)) {
            throw $this->createNotFoundException();
        }

        $root = realpath($this->getParameter('pdf_directory'));
        $path = $root === false ? false : realpath($root . '/' . $element->getPdf());

        if ($path === false || strpos($path, $root . DIRECTORY_SEPARATOR) !== 0) {
            throw $this->createNotFoundException();
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            basename($path),
            preg_replace('/[^A-Za-z0-9._-]/', '_', basename($path))
        );

        return $response;
    }
}
