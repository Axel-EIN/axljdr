<?php

namespace App\Controller;

use App\Entity\Ecole;
use App\Service\FileHandler;
use App\Form\AdminEcoleType;
use App\Repository\EcoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use function PHPUnit\Framework\fileExists;

class AdminEcoleController extends AbstractController
{
    /**
     * @Route("/admin/ecole", name="admin_ecole")
     */
    public function afficherAdminEcoles(EcoleRepository $ecoleRepository): Response {

        $ecoles = $ecoleRepository->findBy(array(), array('id' => 'DESC'));
        return $this->render('admin_ecole/index.html.twig', [
            'ecoles' => $ecoles
        ]);
    }

     /**
     * @Route("/admin/ecole/create", name="admin_ecole_create")
     * @IsGranted("ROLE_MJ")
     */
    public function ajouterEcole(Request $request, EntityManagerInterface $em, FileHandler $fileHandler) {

        $ecole = new Ecole;
        $form = $this->createForm(AdminEcoleType::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'ecole-' . $ecole->getClan()->getNom() . '-' . $ecole->getNom();
                $ecole->setImage($fileHandler->handle($nouvelleImage, null, $prefix, 'ecoles'));
            } else {
                switch ($ecole->getClasse()->getNom()) {
                    case 'Bushi':
                        $ecole->setImage('assets/img/placeholders/na-ecole-bushi.jpg');
                        break;
                    case 'Shugenja':
                        $ecole->setImage('assets/img/placeholders/na-ecole-shugenja.jpg');
                        break;
                    case 'Moine':
                        $ecole->setImage('assets/img/placeholders/na-ecole-moine.jpg');
                        break;
                    case 'Courtisan':
                        $ecole->setImage('assets/img/placeholders/na-ecole-courtisan.jpg');
                        break;
                    case 'Artisan':
                        $ecole->setImage('assets/img/placeholders/na-ecole-artisan.jpg');
                        break;
                }
            }

            $em->persist($ecole);
            $em->flush();

            $this->addFlash('success', 'L\'école a bien été ajoutée.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'regles')
                return $this->redirectToRoute('regles');
            return $this->redirectToRoute('admin_ecole');
            
        } else {
            return $this->render('admin_ecole/create.html.twig', [
                'type' => 'Créer',
                'form' => $form->createView()
            ]);
        }
    }

    /**
     * @Route("/admin/ecole/{id}/edit", name="admin_ecole_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editerEcole(Request $request, Ecole $ecole, FileHandler $fileHandler): Response {

        $form = $this->createForm(AdminEcoleType::class, $ecole);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'ecole' . '-' . $ecole->getClan()->getNom() . '-' . $ecole->getNom();
                $ecole->setImage($fileHandler->handle($nouvelleImage, $ecole->getImage(), $prefix, 'ecoles'));
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'L\'école a bien été modifiée.');

            // REDIRECTION
            // -----------
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'ecole')
                return $this->redirectToRoute('regles_ecole', ['id' => $ecole->getId()]);
            return $this->redirectToRoute('admin_ecole');

            return $this->redirectToRoute('admin_ecole');
        }

        return $this->renderForm('admin_ecole/edit.html.twig', [
            'ecole' => $ecole,
            'form' => $form,
            'type' => 'Modifier',
        ]);
        
    }

    /**
     * @Route("/admin/ecole/{id}/delete", name="admin_ecole_delete", methods={"GET"})
     * @IsGranted("ROLE_MJ")
     */
    public function supprimerEcole(Request $request, Ecole $ecole, FileHandler $fileHandler): Response {

        if ($this->isCsrfTokenValid('delete' . $ecole->getId(), $request->query->get('csrf'))) {

            if (!$ecole->getPersonnages()->isEmpty()) {
                $this->addFlash('warning', 'Veuillez supprimer les personnages enfants au prélable !');
                return $this->redirectToRoute('admin_ecole');
            }

            $entityManager = $this->getDoctrine()->getManager();

            $fileHandler->handle(null, $ecole->getImage(), null, 'ecoles');

            $entityManager->remove($ecole);
            $entityManager->flush();

            $this->addFlash('success', 'L\'école a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_ecole');
    }
}
