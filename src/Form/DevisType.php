<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as T;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class DevisType extends AbstractType {
    public function buildForm(FormBuilderInterface $b, array $o): void {
        // Destinataire (V1 : saisi libre)
        $b->add('dest_company', T\TextType::class, [
            'label'       => 'Entreprise / Financeur',
            'constraints' => [new Assert\NotBlank()],
            'attr'        => ['class' => 'input input-bordered w-full'],
        ])->add('dest_contact_fullname', T\TextType::class, [
            'label'       => 'Contact',
            'constraints' => [new Assert\NotBlank()],
            'attr'        => ['class' => 'input input-bordered w-full'],
        ])->add('dest_contact_email', T\EmailType::class, [
            'label'       => 'Email',
            'constraints' => [new Assert\NotBlank(), new Assert\Email()],
            'attr'        => ['class' => 'input input-bordered w-full'],
        ])->add('dest_address', T\TextareaType::class, [
            'label'    => 'Adresse (optionnel)',
            'required' => false,
            'attr'     => ['rows' => 2, 'class' => 'textarea textarea-bordered w-full'],
        ]);

        // Meta formation (optionnels V1)
        $b->add('formation_title', T\TextType::class, [
            'label'    => 'Intitulé formation (optionnel)',
            'required' => false,
            'attr'     => ['class' => 'input input-bordered w-full'],
        ])->add('session_code', T\TextType::class, [
            'label'    => 'Code session (optionnel)',
            'required' => false,
            'attr'     => ['class' => 'input input-bordered w-full'],
        ])->add('hours_total', T\NumberType::class, [
            'label'    => 'Durée (h) (optionnel)',
            'required' => false,
            'html5'    => true,
            'attr'     => ['step' => '0.5', 'class' => 'input input-bordered w-full max-w-40'],
        ])->add('modality', T\ChoiceType::class, [
            'label'       => 'Modalité (optionnel)',
            'required'    => false,
            'choices'     => [
                'Présentiel' => 'presentiel',
                'Distanciel' => 'distanciel',
                'Hybride'    => 'hybride',
            ],
            'placeholder' => '—',
            'attr'        => ['class' => 'select select-bordered w-full'],
        ]);

        // TVA / Validité
        $b->add('tva_exempt', T\CheckboxType::class, [
            'label'    => false,
            'required' => false,
            'attr'     => ['class' => 'checkbox'],
        ]);
        $b->add('validity_days', T\IntegerType::class, [
            'label'      => 'Validité (jours)',
            'empty_data' => '30',
            'attr'       => ['class' => 'input input-bordered w-full max-w-40'],
        ]);

        // Lignes
        $b->add('lines', T\CollectionType::class, [
            'label'        => 'Lignes du devis',
            'entry_type'   => DevisLineType::class,
            'allow_add'    => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype'    => true,
            'constraints'  => [
                new Assert\Count([
                    'min'        => 1,
                    'minMessage' => 'Ajoute au moins une ligne au devis.',
                ]),
            ],
        ]);
    }
    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults(['csrf_protection' => true]);
    }
}
