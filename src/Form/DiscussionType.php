<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiscussionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('field_name')
        ;
    }

    #[Route('/theme/{id}/add', name: 'app_add_discussion', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addDiscussion(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $content = $request->request->get('content');

        if (!$content) {
            $this->addFlash('error', 'Content cannot be empty');
            return $this->redirectToRoute('app_theme', ['id' => $id]);
        }

        $theme = $entityManager->getRepository(Theme::class)->find($id);
        if (!$theme) {
            throw $this->createNotFoundException('Theme not found');
        }

        $discussion = new Discussion();
        $discussion->setTheme($theme);
        $discussion->setAuthor($this->getUser());
        $discussion->setContent($content);
        $discussion->setCreatedAt(new \DateTime());

        $entityManager->persist($discussion);
        $entityManager->flush();

        return $this->redirectToRoute('app_theme', ['id' => $id]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
