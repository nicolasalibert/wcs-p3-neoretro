<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;

class GameSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
            'required'  => false,
            'label'     => false,
            'attr'      => [
                'placeholder' => 'Search',
            ],
        ])
            ->add('categories', EntityType::class, [
                'label' => 'Category',
                'required' => false,
                'class' => Category::class,
                'choice_label' => 'label',
                'multiple' => true,
                'expanded' => true,
                'constraints' => [
                ]
            ])
            ->add('sort_by', HiddenType::class, [
                'constraints' => [
                    new Choice(['choices' => ['title', 'score', 'time']]),
                ]
            ])
            ->add('sort_order', HiddenType::class, [
                'constraints' => [
                    new Choice(['choices' => ['ASC', 'DESC']]),
                ]
            ])
            ->add('isVisible', HiddenType::class, [
                'data' => '1',
                'constraints' => [
                    new Choice(['choices' => ['1', '0']]),
                ]
            ])
        ;
    }
}
