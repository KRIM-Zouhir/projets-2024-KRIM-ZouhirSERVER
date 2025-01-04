<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username/email entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Retrieve the custom error message from the session (if available)
        $customErrorMessage = $request->getSession()->get('login_error_message', null);

        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $customErrorMessage ?? ($error ? $error->getMessageKey() : null),
        ]);
    }




    public function logout(): void
    {
        // Symfony handles the logout logic automatically
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
