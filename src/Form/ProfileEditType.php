<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'First Name',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your first name']),
                    new Length([
                        'min' => 2,
                        'max' => 25,
                        'minMessage' => 'Your first name must be at least {{ limit }} characters long',
                        'maxMessage' => 'Your first name cannot be longer than {{ limit }} characters'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your first name'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Last Name',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your last name']),
                    new Length([
                        'min' => 2,
                        'max' => 25,
                        'minMessage' => 'Your last name must be at least {{ limit }} characters long',
                        'maxMessage' => 'Your last name cannot be longer than {{ limit }} characters'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your last name'
                ]
            ])
            ->add('username', TextType::class, [
                'label' => 'Username',
                'constraints' => [
                    new NotBlank(['message' => 'Please choose a username']),
                    new Length([
                        'min' => 3,
                        'max' => 25,
                        'minMessage' => 'Your username must be at least {{ limit }} characters long',
                        'maxMessage' => 'Your username cannot be longer than {{ limit }} characters'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Choose a unique username'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your email']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your email address'
                ]
            ])
            ->add('birthdate', DateType::class, [
                'label' => 'Birthdate',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'max' => (new \DateTime('-13 years'))->format('Y-m-d') // Minimum age 13
                ]
            ])
            ->add('profile_picture', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, GIF, or WebP)',
                        'maxSizeMessage' => 'The file is too large ({{ size }} {{ suffix }}). Maximum allowed size is {{ limit }} {{ suffix }}.'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control-file',
                    'accept' => 'image/jpeg,image/png,image/gif,image/webp'
                ]
            ]);
            // ->add('submit', SubmitType::class, [
            //     'label' => 'Update Profile',
            //     'attr' => [
            //         'class' => 'btn btn-primary mt-3'
            //     ]
            // ])
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'profile_edit'
        ]);
    }
}