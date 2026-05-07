<?php

namespace App\Controller;

use App\Service\Uploader;
use App\Form\MonCompteMdpType;
use App\Entity\FichePersonnage;
use App\Form\MonCompteAvatarType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_USER")
 */
class MonCompteController extends AbstractController
{
    /**
     * @Route("/mon_compte", name="mon_compte")
     */
    public function afficherMonCompte(): Response
    {
        return $this->render('mon_compte/index.html.twig');
    }

    /**
     * @Route("/mon_compte/password/edit", name="mon_compte_mdp")
     */
    public function modifierMotDePasse(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(MonCompteMdpType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // encode the plain password
            $user->setPassword(
                $userPasswordHasherInterface->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a bien été modifié !');
            return $this->redirectToRoute('mon_compte');
        }

        return $this->render('mon_compte/modifier_mdp.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mon_compte/avatar/edit", name="mon_compte_avatar")
     */
    public function modifierAvatar(Request $request, Uploader $uploadeur): Response
    {
        $utilisateur = $this->getUser();
        $form = $this->createForm(MonCompteAvatarType::class, $utilisateur);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $nouveauAvatar = $form->get('avatar')->getData();

            if (!empty($nouveauAvatar)) {

                $ancienAvatarNomFichier = basename($utilisateur->getAvatar());

                $nouveauAvatarNomFichier = $uploadeur->upload($nouveauAvatar, 'avatar-' . $utilisateur->getPseudo(), 'avatars');

                if ($nouveauAvatarNomFichier !== null) {
                    $nouveauChemingRelatif = 'assets/img/avatars/' . $nouveauAvatarNomFichier;
                    $utilisateur->setAvatar($nouveauChemingRelatif);

                    $ancienneAvatarCheminComplet = $this->getParameter('image_directory') . '/avatars/' . $ancienAvatarNomFichier;
                    $filesystem = new Filesystem();
                    if ($ancienneAvatarCheminComplet != $this->getParameter('image_directory') . '/avatars/')
                        $filesystem->remove($ancienneAvatarCheminComplet);
                }

            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($utilisateur);
            $em->flush();

            $this->addFlash('success', 'Votre avatar a bien été modifié !');
            return $this->redirectToRoute('mon_compte');
        }

        return $this->render('mon_compte/modifier_avatar.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mon_compte/fiche/{id}", name="mon_compte_fiche")
     */
    public function afficherMonCompteFichePersonnage(FichePersonnage $fiche): Response
    {
        $utilisateur = $this->getUser();

        $estLeJoueur = $fiche->getPersonnage()->getJoueur() && $fiche->getPersonnage()->getJoueur()->getId() == $utilisateur->getId();

        // if (!$estLeJoueur && !$this->isGranted('ROLE_MJ'))
        //     return $this->redirectToRoute('mon_compte');

        $xp_progression = 0;
        foreach ($fiche->getPersonnage()->getParticipations() as $participation) {
            $xp_progression += $participation->getXpGagne();
        }

        $xp_creation = $fiche->getCreationExp() ?? 0;
        $xp_total = $xp_progression + $xp_creation;

        $rang = 1;
        if ($xp_total >= 360)      $rang = 5;
        elseif ($xp_total >= 240)  $rang = 4;
        elseif ($xp_total >= 140)  $rang = 3;
        elseif ($xp_total >= 60)   $rang = 2;

        return $this->render('mon_compte/fiche_personnage.html.twig', [
            'fiche'          => $fiche,
            'xp_total'       => $xp_total,
            'xp_creation'    => $xp_creation,
            'xp_progression' => $xp_progression,
            'rang'           => $rang,
            'est_le_joueur'  => $estLeJoueur,
        ]);
    }
}