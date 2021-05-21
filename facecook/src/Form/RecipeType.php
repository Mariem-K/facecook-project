<?php

namespace App\Form;

use App\Entity\Recipe;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('description', TextType::class, [
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'Privé' => 1,
                    'Public' => 2,
                    'Custom' => 3,
                ],
            ])
            ->add('instructions', TextareaType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('ingredients', TextareaType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('category', null, [
                'expanded' => true,
            ])
            ->add('visibleBy')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
