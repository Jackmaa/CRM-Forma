<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as T;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class DevisLineType extends AbstractType {
    public function buildForm(FormBuilderInterface $b, array $o): void {
        $b->add('description', T\TextType::class, [
            'label'       => 'Description',
            'constraints' => [new Assert\NotBlank()],
            'attr'        => ['class' => 'input input-bordered w-full'],
        ])->add('qty', T\NumberType::class, [
            'label'       => 'Qté',
            'html5'       => true,
            'constraints' => [new Assert\NotBlank(), new Assert\Positive()],
            'attr'        => ['step' => '0.01', 'class' => 'input input-bordered w-full max-w-32'],
        ])->add('unit_ht', T\NumberType::class, [
            'label' => 'PU HT',
            'html5' => true,
            'scale' => 2,
            'attr'  => ['step' => '0.01', 'class' => 'input input-bordered w-full max-w-40'],
        ])->add('tva_rate', T\NumberType::class, [
            'label'      => 'TVA %',
            'html5'      => true,
            'scale'      => 2,
            'attr'       => ['step' => '0.01', 'class' => 'input input-bordered w-full max-w-32'],
            'empty_data' => '20',
        ]);
    }
}
