<?php

// src/Form/FormationType.php
namespace App\Form;

use App\Entity\Formation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la formation',
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug (URL friendly)',
            ])
            ->add('thematique', TextType::class, [
                'label' => 'Thématique',
            ])
            ->add('niveau', TextType::class, [
                'label' => 'Niveau',
            ])
            ->add('prerequis', TextareaType::class, [
                'label' => 'Prérequis',
            ])
            ->add('modalites', CollectionType::class, [
                'entry_type'    => TextType::class,
                'entry_options' => ['label' => false],
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'delete_empty'  => true,
                'prototype'     => true,
                'required'      => false,
                'label'         => 'Modalités pédagogiques',
                'help'          => 'Ex. Présentiel, Distanciel…',
            ])
            ->add('objectifs', CollectionType::class, [
                'entry_type'    => TextType::class,
                'entry_options' => ['label' => false],
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'delete_empty'  => true,
                'prototype'     => true,
                'required'      => false,
                'label'         => 'Objectifs pédagogiques',
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (heures)',
            ])
            ->add('tarif', MoneyType::class, [
                'label'    => 'Tarif (€)',
                'currency' => 'EUR',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description détaillée',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
