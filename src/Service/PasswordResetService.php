<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;

class PasswordResetService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function isValidToken(string $token): bool
    {
        $user = $this->userRepository->findOneBy(['passwordResetToken' => $token]);
        
        if (!$user) {
            return false;
        }

        $expiresAt = $user->getPasswordResetTokenExpiresAt();
        if (!$expiresAt) {
            return false;
        }

        return $expiresAt > new \DateTime();
    }

    public function resetPassword(string $token, string $newPassword): void
    {
        $user = $this->userRepository->findOneBy(['passwordResetToken' => $token]);
        
        if (!$user) {
            throw new \InvalidArgumentException('Invalid token');
        }

        // Hash the new password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        // Clear the reset token
        $user->setPasswordResetToken(null);
        $user->setPasswordResetTokenExpiresAt(null);

        $this->entityManager->flush();
    }
}