<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function profile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $errors = [];
        $form = $this->createFormBuilder()
            ->add('profile_picture', FileType::class, [
                'label' => 'Profile Picture (JPEG/PNG)',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Upload',
            ])
            ->getForm();

        $form->handleRequest($request);

        // Handle profile picture upload
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('profile_picture')->getData();

            if ($file) {
                // Generate a unique file name
                $fileName = uniqid() . '.' . $file->guessExtension();

                // Move the file to the uploads directory
                try {
                    $file->move(
                        $this->getParameter('profile_pictures_directory'),
                        $fileName
                    );

                    // Update the user's profile picture
                    $user->setProfilePicture($fileName);
                    $entityManager->flush();
                    $this->addFlash('success', 'Profile picture uploaded successfully.');
                    return $this->redirectToRoute('app_profile');
                } catch (\Exception $e) {
                    $errors[] = 'An error occurred while uploading the profile picture.';
                    $this->addFlash('error', 'An error occurred while uploading the profile picture.');
                }
            }
        }

        // Handle other profile updates (username, email, password)
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            // Update username
            if (!empty($data['username'])) {
                $existingUser = $entityManager->getRepository(User::class)->findOneBy(['username' => $data['username']]);
                if ($existingUser && $existingUser !== $user) {
                    $errors[] = 'The username is already taken.';
                    $this->addFlash('error', 'The username is already taken.');
                } else {
                    $user->setUsername($data['username']);
                }
            }

            // Update email
            if (!empty($data['email']) && $data['email'] !== $user->getEmail()) {
                $existingEmail = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
                if ($existingEmail) {
                    $errors[] = 'The email is already in use.';
                    $this->addFlash('error', 'The email is already in use.');
                } else {
                    $user->setIsVerified(false);
                    $user->setEmail($data['email']);
                }
            }

            // Update password
            if (!empty($data['password']) && !empty($data['newPassword'])) {
                if ($passwordHasher->isPasswordValid($user, $data['password'])) {
                    $user->setPassword($passwordHasher->hashPassword($user, $data['newPassword']));
                } else {
                    $errors[] = 'The current password is incorrect.';
                    $this->addFlash('error', 'The current password is incorrect.');
                }
            }

            if (empty($errors)) {
                $entityManager->flush();
                $this->addFlash('success', 'Profile updated successfully.');
                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'errors' => $errors,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/remove-picture', name: 'app_remove_profile_picture')]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function removeProfilePicture(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Remove the current profile picture
        if ($user->getProfilePicture() && $user->getProfilePicture() !== 'default-profile.png') {
            $profilePicturesDirectory = $this->getParameter('profile_pictures_directory');
            $filePath = $profilePicturesDirectory . '/' . $user->getProfilePicture();

            if (file_exists($filePath)) {
                unlink($filePath); // Delete the file from the server
            }
        }

        // Set the profile picture to the default one
        $user->setProfilePicture('default-profile.png');
        $entityManager->flush();

        $this->addFlash('success', 'Profile picture removed successfully.');
        return $this->redirectToRoute('app_profile');
    }
}
