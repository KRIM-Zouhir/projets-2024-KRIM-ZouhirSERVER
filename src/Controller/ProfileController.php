<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileEditType;
use App\Service\FileUploader;
use App\Service\PasswordManagementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileUploader $fileUploader,
        private readonly PasswordManagementService $passwordManager
    ) {}

    #[Route('', name: 'app_profile', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profile(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $profilePicture = $form->get('profile_picture')->getData();
                
                if ($profilePicture) {
                    $this->fileUploader->setTargetDirectory($this->getParameter('profile_pictures_directory'));
                    
                    // Handle profile picture upload
                    $fileName = $this->fileUploader->upload($profilePicture, $user->getUsername());

                    // Remove old profile picture if it exists
                    $oldPicture = $user->getProfilePicture();
                    if ($oldPicture && $oldPicture !== 'default-profile.png') {
                        $this->fileUploader->remove($oldPicture);
                    }

                    $user->setProfilePicture($fileName);
                }

                $this->entityManager->flush();

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Profile updated successfully',
                        'imageUrl' => $this->getProfilePictureUrl($user)
                    ]);
                }

                $this->addFlash('success', 'Profile updated successfully.');
                return $this->redirectToRoute('app_profile');

            } catch (\Exception $e) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Failed to update profile: ' . $e->getMessage()
                    ], Response::HTTP_BAD_REQUEST);
                }

                $this->addFlash('error', 'Failed to update profile: ' . $e->getMessage());
            }
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/change-password', name: 'app_profile_change_password', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function changePassword(Request $request): Response
    {
        $result = $this->passwordManager->changePassword(
            $this->getUser(),
            $request->request->get('current_password'),
            $request->request->get('new_password'),
            $request->request->get('confirm_password')
        );

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                $result['success'] 
                    ? ['message' => 'Password updated successfully'] 
                    : ['errors' => $result['errors']],
                $result['success'] ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST
            );
        }

        if ($result['success']) {
            $this->addFlash('success', 'Password updated successfully');
        } else {
            foreach ($result['errors'] as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/check-username', name: 'app_profile_check_username', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function checkUsername(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? null;

        if (!$username) {
            return new JsonResponse([
                'available' => false,
                'message' => 'Username is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        
        // Allow user to keep their current username
        if ($currentUser->getUsername() === $username) {
            return new JsonResponse(['available' => true]);
        }

        $existingUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        return new JsonResponse([
            'available' => $existingUser === null
        ]);
    }

    #[Route('/remove-picture', name: 'app_profile_remove_picture', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function removeProfilePicture(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $currentPicture = $user->getProfilePicture();
            if ($currentPicture && $currentPicture !== 'default-profile.png') {
                $this->fileUploader->setTargetDirectory($this->getParameter('profile_pictures_directory'));
                $this->fileUploader->remove($currentPicture);
                
                $user->setProfilePicture('default-profile.png');
                $this->entityManager->flush();
            }

            return new JsonResponse([
                'success' => true,
                'message' => 'Profile picture removed successfully',
                'imageUrl' => $this->getProfilePictureUrl($user)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to remove profile picture'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/delete-account', name: 'app_profile_delete_account', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deleteAccount(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();

            // Remove profile picture if it exists
            $profilePicture = $user->getProfilePicture();
            if ($profilePicture && $profilePicture !== 'default-profile.png') {
                $this->fileUploader->setTargetDirectory($this->getParameter('profile_pictures_directory'));
                $this->fileUploader->remove($profilePicture);
            }

            $this->entityManager->remove($user);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to delete account'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getProfilePictureUrl(User $user): string
    {
        $profilePicture = $user->getProfilePicture();
        return $profilePicture
            ? '/uploads/profile-pictures/' . $profilePicture
            : '/images/default-profile.png';
    }
}