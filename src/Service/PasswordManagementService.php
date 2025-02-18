<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordManagementService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator
    ) {}

    /**
     * Validates and changes user password
     *
     * @param User $user Current user entity
     * @param string $currentPassword Current password for verification
     * @param string $newPassword New password to set
     * @param string $confirmPassword Password confirmation
     * @return array{success: bool, errors?: array<string, string>} Validation result
     */
    public function changePassword(
        User $user,
        string $currentPassword,
        string $newPassword,
        string $confirmPassword
    ): array {
        // Verify current password
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            return [
                'success' => false,
                'errors' => ['current_password' => 'Current password is incorrect']
            ];
        }

        // Validate new password
        $violations = $this->validator->validate([
            'new_password' => $newPassword,
            'confirm_password' => $confirmPassword
        ], new Assert\Collection([
            'new_password' => [
                new Assert\NotBlank(['message' => 'New password is required']),
                new Assert\Length([
                    'min' => 8,
                    'minMessage' => 'Password must be at least 8 characters long'
                ]),
                new Assert\Regex([
                    'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                    'message' => 'Password must contain at least one letter, one number, and one special character'
                ])
            ],
            'confirm_password' => [
                new Assert\NotBlank(['message' => 'Password confirmation is required']),
                new Assert\EqualTo([
                    'propertyPath' => 'new_password',
                    'message' => 'Passwords do not match'
                ])
            ]
        ]));

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return ['success' => false, 'errors' => $errors];
        }

        // Update password
        try {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $this->entityManager->flush();

            return ['success' => true];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['system' => 'Failed to update password. Please try again.']
            ];
        }
    }
}