<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        // If the user is already logged in, redirect them to the home page
        if ($this->getUser()) {
            return $this->redirectToRoute('app_forum_index');
        }

        return $this->render('security/login.html.twig');
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        // The logout logic is handled by Symfony, so this method can be empty
    }


}
