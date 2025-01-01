<?php
// src/Service/PasswordResetService.php

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordResetService
{
    private $userRepository;
    private $passwordEncoder;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function isValidToken(string $token): bool
    {
        // Vérifiez ici si le token est valide (par exemple, en vérifiant sa base de données ou sa durée de vie)
        return true; // Retourne vrai si valide, sinon faux
    }

    public function resetPassword(string $token, string $newPassword): void
    {
        // Récupérer l'utilisateur par son token
        $user = $this->userRepository->findUserByResetToken($token);
        if ($user) {
            // Encoder le nouveau mot de passe
            $encodedPassword = $this->passwordEncoder->encodePassword($user, $newPassword);
            $user->setPassword($encodedPassword);
            $this->userRepository->save($user);
        }
    }
}
