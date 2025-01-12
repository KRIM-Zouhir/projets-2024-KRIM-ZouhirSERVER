<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPasswordRequestFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Service\EmailService; // Si vous avez un service d'email personnalisé
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use App\Service\PasswordResetService;

class ForgotController extends AbstractController
{

    public function request(Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer,         EntityManagerInterface $entityManager  // Add this parameter
    ): Response
    {
        $form = $this->createForm(ForgotPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            // Chercher l'utilisateur par email
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Générer un token unique pour la réinitialisation
                $token = $tokenGenerator->generateToken();
                $user->setPasswordResetToken($token);
                $user->setPasswordResetTokenExpiresAt(new \DateTime('+1 hour')); // Le token expire dans 1 heure
                $entityManager->flush();

                // Envoi de l'email avec le lien de réinitialisation
                $resetPasswordUrl = $this->generateUrl('app_reset_password', ['token' => $token], 0);
                $emailMessage = (new Email())
                    ->from('micmjad@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Réinitialisation du mot de passe')
                    ->html(
                        '<p>Bonjour ' . $user->getUsername() . ',</p>' .
                        '<p>Nous avons reçu une demande de réinitialisation de votre mot de passe. Cliquez sur le lien ci-dessous pour réinitialiser votre mot de passe:</p>' .
                        '<a href="' . $resetPasswordUrl . '">Réinitialiser mon mot de passe</a>'
                    );
                $mailer->send($emailMessage);

                $this->addFlash('success', 'Un lien de réinitialisation a été envoyé à votre adresse e-mail.');

                return $this->redirectToRoute('app_login');
            }

            // Si l'email n'est pas trouvé, afficher une erreur générique (pour éviter la divulgation d'informations sur les utilisateurs)
            $this->addFlash('error', 'Si un compte avec cet e-mail existe, vous recevrez un lien de réinitialisation.');

            return $this->redirectToRoute('app_forgot_password');
        }

        return $this->render('forgot_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function reset(string $token, Request $request, PasswordResetService $passwordResetService): Response
    {
        // Verifier la validité du token
        if (!$passwordResetService->isValidToken($token)) {
            $this->addFlash('error', 'Le lien de réinitialisation est invalide ou expiré.');
            return $this->redirectToRoute('app_forgot_password');
        }

        // Créer le formulaire pour réinitialiser le mot de passe
        $form = $this->createForm(ResetPasswordFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les nouvelles informations et mettre à jour le mot de passe
            $passwordResetService->resetPassword($token, $form->get('newPassword')->getData());
            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('forgot_password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
