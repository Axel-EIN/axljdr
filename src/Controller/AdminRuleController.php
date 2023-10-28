<?php

namespace App\Controller;

use App\Entity\Rule;
use App\Form\AdminRuleType;
use App\Service\FileHandler;
use App\Service\Numeroteur;
use App\Repository\RuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminRuleController extends AbstractController
{
    /**
     * @Route("/admin/rule", name="admin_rule")
     */
    public function afficherAdminRules(RuleRepository $ruleRepository): Response
    {
        $rules = $ruleRepository->findAll();

        return $this->render('admin_rule/index.html.twig', [
            'rules' => $rules
        ]);
    }

    /**
     * @Route("/admin/rule/create", name="admin_rule_create")
     * @IsGranted("ROLE_MJ")
     */
    public function ajouterRule(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, RuleRepository $ruleRepository, Numeroteur $numeroteur): Response
    {
        $rule = new Rule;
        $form = $this->createForm(AdminRuleType::class, $rule);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            // IMAGE
            $image = $form->get('image')->getData();
            if (!empty($image)) {
                $prefix = 'rule-' . $rule->getNom() . '-image';
                $rule->setImage($fileHandler->handle($image, null, $prefix, 'rules'));
            }

            // PDF
            $pdf = $form->get('pdf')->getData();
            if (!empty($pdf)) {
                $prefix = 'rule-' . $rule->getNom() . '-pdf';
                $rule->setPdf($fileHandler->handle($pdf, null, $prefix, 'rules-pdfs'));
            }

            $em->persist($rule);
            $em->flush();

            $this->addFlash('success', 'La Règle a bien été ajoutée.');

            // NUMEROTEUR
            // ----------
            $fratrieArrivee = $ruleRepository->findBy(['base' => $rule->getBase()]);
            $numeroteur->reordonnerNumero($rule->getId(), -1, $rule->getNumero(), [], $fratrieArrivee);

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'rule')
                return $this->redirectToRoute('regles_rule', ['id' => $rule->getId()]);

            return $this->redirectToRoute('admin_rule');
        }

        return $this->render('admin_rule/create.html.twig', [
            'type' => 'Créer',
            'form' => $form->createView()

        ]);
    }

    /**
     * @Route("/admin/rule/{id}/edit", name="admin_rule_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function modifierRule(Request $request, Rule $rule, FileHandler $fileHandler, RuleRepository $ruleRepository, Numeroteur $numeroteur): Response
    {
        // Stockage du numéro et de l'ID de l'episode avant édition
        $numeroDepart = $rule->getNumero();
        $fratrieDepartId = $rule->getBase();

        $form = $this->createForm(AdminRuleType::class, $rule);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            // IMAGE
            $nouvelleImage = $form->get('image')->getData();
            if (!empty($nouvelleImage)) {
                $prefix = 'rule-' . $rule->getNom() . '-image';
                $rule->setImage($fileHandler->handle($nouvelleImage, $rule->getImage(), $prefix, 'rules'));
            }

            // PDF
            $nouveauPDF = $form->get('pdf')->getData();
            if (!empty($nouveauPDF)) {
                $prefix = 'rule-' . $rule->getNom() . '-pdf';
                $rule->setPdf($fileHandler->handle($nouveauPDF, $rule->getPdf(), $prefix, 'rules-pdfs'));
            }

            // NUMEROTEUR
            // ----------
            // Si numero change ou parent change
            if ($numeroDepart != $rule->getNumero() || $fratrieDepartId != $rule->getBase())
            {
                $fratrieDepart = $ruleRepository->findBy(['base' => $fratrieDepartId]);
                $fratrieArrivee = $ruleRepository->findBy(['base' => $rule->getBase()]);
                $numeroteur->reordonnerNumero($rule->getId(), $numeroDepart, $rule->getNumero(), $fratrieDepart, $fratrieArrivee);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La Règle a bien été modifiée.');

            // REDIRECTION
            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'rule')
                return $this->redirectToRoute('regles_rule', ['id' => $rule->getId()]);

            return $this->redirectToRoute('admin_rule');
        }

        return $this->renderForm('admin_rule/edit.html.twig', [
            'type' => 'Modifier',
            'rule' => $rule,
            'form' => $form
        ]);
    }

    /**
     * @Route("/admin/rule/{id}/delete", name="admin_rule_delete")
     * @IsGranted("ROLE_MJ")
     */
    public function supprimerRule(Request $request, Rule $rule, FileHandler $fileHandler, EntityManagerInterface $em, RuleRepository $ruleRepository, Numeroteur $numeroteur): Response
    {
        if ( $this->isCsrfTokenValid('delete' . $rule->getId(), $request->query->get('csrf')))
        {
            $fileHandler->handle(null, $rule->getImage(), null, 'rules');
            $fileHandler->handle(null, $rule->getPdf(), null, 'rules-pdfs');

            // NUMEROTEUR
            // ----------
            $fratrieDepartId = $rule->getBase();
            $fratrieDepart = $ruleRepository->findBy(['base' => $fratrieDepartId]);
            $numeroteur->reordonnerNumero($rule->getId(), $rule->getNumero(), -1, $fratrieDepart, []);

            $em->remove($rule);
            $em->flush();

            $this->addFlash('success', 'La Règle a bien été supprimée.');    
        }

        return $this->redirectToRoute('admin_rule');
    }
}