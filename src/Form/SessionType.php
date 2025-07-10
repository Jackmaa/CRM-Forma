<?php
namespace App\Form;

use App\Entity\Formation;
use App\Entity\Session;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
        // Association à la formation
            ->add('formation', EntityType::class, [
                'class'        => Formation::class,
                'choice_label' => 'titre',
                'label'        => 'Formation',
            ])
            // Responsable : un seul formateur
            ->add('formateur_responsable', EntityType::class, [
                'class'         => User::class,
                'choice_label'  => fn(User $u)  => $u->getFullName(),
                'query_builder' => fn($repo) => $repo->createQueryBuilder('u')
                    ->where('u.role = :r')
                    ->setParameter('r', 'FORMATEUR'),
                'label'         => 'Formateur responsable',
            ])
            //Stagiaires
            ->add('participants', EntityType::class, [
                'class'         => User::class,
                'choice_label'  => fn(User $u)  => $u->getFullName(),
                'query_builder' => fn($repo) => $repo->createQueryBuilder('u')
                    ->where('u.role = :r')->setParameter('r', 'STAGIAIRE'),
                'expanded'      => true, // cases à cocher
                'multiple'      => true,
                'choice_attr'   => fn(User $u)   => [
                    'class' => 'form-checkbox h-4 w-4 text-blue-600',
                ],
                'label'         => 'Stagiaires',
            ])
            // Dates
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'label'  => 'Date & heure de début',
            ])
            ->add('dateFin', DateTimeType::class, [
                'widget' => 'single_text',
                'label'  => 'Date & heure de fin',
            ])
            // Modalité et lieu
            ->add('modalite', TextType::class, [
                'label' => 'Modalité (présentiel/distanciel/hybride)',
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu',
            ])
            // Remarques (facultatif)
            ->add('remarques', TextareaType::class, [
                'required' => false,
                'label'    => 'Remarques',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class'         => Session::class,
            'csrf_protection'    => false,
            'allow_extra_fields' => true,
        ]);
    }
}
