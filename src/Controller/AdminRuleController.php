<?php

namespace App\Controller;

use App\Entity\Rule;
use App\Form\AdminRuleType;
use App\Service\FileHandler;
use App\Service\Orderer;
use App\Repository\RuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\Unlocker;

class AdminRuleController extends AbstractController
{
    /**
     * @Route("/admin/rule", name="admin_rule")
     * @IsGranted("ROLE_MJ")
     */
    public function viewAdminRules(RuleRepository $ruleRepository): Response
    {
        $rules = $ruleRepository->findBy( [] , ['id' => 'DESC'] );

        return $this->render('back_office/list-element.html.twig', [
            'elements' => $rules,
            'element' => 'rule',
            'label' => 'Rule',
            'labels' => 'Rules',
            'gender' => 'F',
            'article' => 'une',
            'table_cols' => [
                'image:Image:image:NA_RULE',
                'nom:Nom::bold',
                'base:base:bool',
                'numero:Ordre:number',
                'pdf:PDF:bool',
                'part1:part1:bool',
                'part2:part2:bool',
                'part3:part3:bool',
                'part4:part4:bool',
                'part5:part5:bool',
                'access:Accès:access',
                'publishedAt:Publié le:date',
            ],
        ]);
    }

    /**
     * @Route("/admin/rule/create", name="admin_rule_create")
     * @IsGranted("ROLE_MJ")
     */
    public function addRule(Request $request, EntityManagerInterface $em, FileHandler $fileHandler, RuleRepository $ruleRepository, Orderer $orderer, Unlocker $unlocker): Response
    {
        $rule = new Rule;
        $form = $this->createForm(AdminRuleType::class, $rule);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            $image = $form->get('image')->getData();
            if (!empty($image)) {
                $prefix = 'rule-' . $rule->getNom() . '-image';
                $rule->setImage($fileHandler->handle($image, null, $prefix, 'rules', 'fourthird900'));
            }

            $pdf = $form->get('pdf')->getData();
            if (!empty($pdf)) {
                $prefix = 'rule-' . $rule->getNom() . '-pdf';
                $rule->setPdf($fileHandler->handle($pdf, null, $prefix, 'pdf-rules'));
            }

            $em->persist($rule);
            $em->flush();

            $unlocker->sync($rule, $form->get('unlockedBy')->getData());
            $em->flush();
            $this->addFlash('success', 'La Règle a bien été ajoutée');

            $newSiblings = $ruleRepository->findBy(['base' => $rule->getBase()]);
            $orderer->reorderNumber($rule->getId(), -1, $rule->getNumero(), [], $newSiblings);

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'rule')
                return $this->redirectToRoute('rules_rule', ['id' => $rule->getId()]);

            return $this->redirectToRoute('admin_rule');
        }

        return $this->render('back_office/create.html.twig', [
            'type' => 'create',
            'entity' => 'rule',
            'label' => 'Rule',
            'gender' => 'F',
            'article' => 'une',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/rule/{id}/edit", name="admin_rule_edit")
     * @IsGranted("ROLE_MJ")
     */
    public function editRule(Request $request, Rule $rule, FileHandler $fileHandler, RuleRepository $ruleRepository, Orderer $orderer, Unlocker $unlocker): Response
    {
        $previousNumber = $rule->getNumero();
        $previousSiblingsId = $rule->getBase();

        $form = $this->createForm(AdminRuleType::class, $rule);
        $form->get('unlockedBy')->setData($unlocker->charactersOf($rule));
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            $newImage = $form->get('image')->getData();
            if (!empty($newImage)) {
                $prefix = 'rule-' . $rule->getNom() . '-image';
                $rule->setImage($fileHandler->handle($newImage, $rule->getImage(), $prefix, 'rules', 'fourthird900'));
            } elseif ($request->request->get('remove_image') === '1' && $rule->getImage()) {
                $fileHandler->handle(null, $rule->getImage(), null, 'rules');
                $rule->setImage(null);
            }

            $newPdf = $form->get('pdf')->getData();
            if (!empty($newPdf)) {
                $prefix = 'rule-' . $rule->getNom() . '-pdf';
                $rule->setPdf($fileHandler->handle($newPdf, $rule->getPdf(), $prefix, 'pdf-rules'));
            }

            if ($previousNumber != $rule->getNumero() || $previousSiblingsId != $rule->getBase())
            {
                $previousSiblings = $ruleRepository->findBy(['base' => $previousSiblingsId]);
                $newSiblings = $ruleRepository->findBy(['base' => $rule->getBase()]);
                $orderer->reorderNumber($rule->getId(), $previousNumber, $rule->getNumero(), $previousSiblings, $newSiblings);
            }

            $unlocker->sync($rule, $form->get('unlockedBy')->getData());

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La Règle a bien été modifiée');

            if (!empty($request->query->get('redirect')) && $request->query->get('redirect') == 'rule')
                return $this->redirectToRoute('rules_rule', ['id' => $rule->getId()]);

            return $this->redirectToRoute('admin_rule');
        }

        return $this->renderForm('back_office/edit.html.twig', [
            'type' => 'edit',
            'rule' => $rule,
            'entity' => 'rule',
            'label' => 'Rule',
            'gender' => 'F',
            'article' => 'une',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/rule/{id}/delete", name="admin_rule_delete", methods={"POST"})
     * @IsGranted("ROLE_MJ")
     */
    public function deleteRule(Request $request, Rule $rule, FileHandler $fileHandler, EntityManagerInterface $em, RuleRepository $ruleRepository, Orderer $orderer, Unlocker $unlocker): Response
    {
        if ( $this->isCsrfTokenValid('delete' . $rule->getId(), $request->request->get('_csrf_token')))
        {

            $fileHandler->handle(null, $rule->getImage(), null, 'rules');
            $fileHandler->handle(null, $rule->getPdf(), null, 'pdf-rules');

            $previousSiblingsId = $rule->getBase();
            $previousSiblings = $ruleRepository->findBy(['base' => $previousSiblingsId]);
            $orderer->reorderNumber($rule->getId(), $rule->getNumero(), -1, $previousSiblings, []);

            $unlocker->forget($rule);
            $em->remove($rule);
            $em->flush();
            $this->addFlash('success', 'La Règle a bien été supprimée');    
        }

        return $this->redirectToRoute('admin_rule');
    }
}