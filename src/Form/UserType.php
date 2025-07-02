<?php
// src/Form/UserType.php
namespace App\Form;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email (identifiant)',
            ])
            ->add('role', ChoiceType::class, [
                'label'    => 'Rôle',
                'choices'  => UserRole::getChoices(),
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('isActive', CheckboxType::class, [
                'label'    => 'Compte actif',
                'required' => false,
            ])
            ->add('forcePasswordReset', CheckboxType::class, [
                'label'    => 'Forcer changement de mot de passe',
                'required' => false,
            ]);
        // === on transforme string ↔ UserRole ===
        $builder->get('role')
            ->addModelTransformer(new CallbackTransformer(
                // objet → string pour l’affichage
                fn(?UserRole $roleEnum) => $roleEnum?->value,
                // string → objet pour setRole()
                fn(string $roleValue)   => UserRole::from($roleValue)
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
