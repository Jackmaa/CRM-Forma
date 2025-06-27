<?php

// src/Form/CentreType.php
namespace App\Form;

use App\Entity\Centre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CentreType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('nom', TextType::class)
            ->add('siret', TextType::class)
            ->add('adresse', TextareaType::class)
            ->add('email', TextType::class)
            ->add('telephone', TextType::class)
            ->add('logoFile', FileType::class, [
                'mapped'   => false,
                'required' => false,
                'label'    => 'Logo (PNG/JPG)', //VichUploaderBundle
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => Centre::class,
        ]);
    }
}
