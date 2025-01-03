<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Bundle\SecurityBundle\Security;



class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        VerifyEmailHelperInterface $verifyEmailHelper,
        \Symfony\Component\Mailer\MailerInterface $mailer
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $user->setIsVerified(false);
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            // Generate the signed URL
            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );
    
            // Send the confirmation email
            $email = (new TemplatedEmail())
                ->from(new Address('micmjad@gmail.com', 'TalkSphere'))
                ->to($user->getEmail())
                ->subject('Please Confirm Your Email')
                ->htmlTemplate('registration/send_email.html.twig')
                ->context([
                    'signedUrl' => $signatureComponents->getSignedUrl(),
                    'expiresAt' => $signatureComponents->getExpiresAt(),
                    'user' => $user,
                ]);
    
            $mailer->send($email);
    
            return $this->redirectToRoute('app_confirmation_email');
        }
    
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /** -----------------------------------------------------------------------------------------------------**/ 


    public function confirmationEmail(): Response
    {
        return $this->render('registration/confirmation_email.html.twig');
    }

    public function confirmationSuccess(): Response
    {
        return $this->render('registration/confirmation_success.html.twig');
    }

    /** -----------------------------------------------------------------------------------------------------**/ 

    public function verifyUserEmail(
        Request $request,
        EntityManagerInterface $entityManager,
        VerifyEmailHelperInterface $verifyEmailHelper,
        Security $security
    ): Response {
        $userId = $request->query->get('id');
        if (!$userId || !($user = $entityManager->getRepository(User::class)->find($userId))) {
            return $this->redirectToRoute('app_register');
        }
    
        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
    
            $user->setIsVerified(true);
            $entityManager->flush();
    
            return $security->login($user);
            
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());
            return $this->redirectToRoute('app_register');
        }
    }
    
}
