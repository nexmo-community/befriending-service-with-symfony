<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phoneNumber', TelType::class, [
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ]
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3]),
                ],
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
            ])
            ->add('town', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3]),
                ],
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
            ])
            ->add('county', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3]),
                ],
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
            ])
            ->add('countryCode', ChoiceType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control form-control-lg'
                ],
                'choices' => [
                    "United Kingdom" => "GB",
                    "United States" => "US"
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Sign up',
                'attr' => [
                    'class' => 'btn btn-info btn-lg btn-block'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
