<?php
namespace App\Enum;

enum UserRole: string {
case ADMIN_CENTRE = 'ADMIN_CENTRE';
case FORMATEUR    = 'FORMATEUR';
case STAGIAIRE    = 'STAGIAIRE';
case ASSISTANT    = 'ASSISTANT';

    public function getLabel(): string {
        return match ($this) {
            self::ADMIN_CENTRE => 'Administrateur Centre',
            self::FORMATEUR => 'Formateur',
            self::STAGIAIRE => 'Stagiaire',
            self::ASSISTANT => 'Assistant',
        };
    }

    public static function getChoices(): array {
        return array_reduce(self::cases(), function ($choices, $case) {
            $choices[$case->getLabel()] = $case->value;
            return $choices;
        }, []);
    }
}