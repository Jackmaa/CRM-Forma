<?php
namespace App\Enum;

/**
 * Enumération des rôles utilisateurs du CRM.
 *
 * Fournit les rôles principaux : admin centre, formateur, stagiaire, assistant.
 */
enum UserRole: string {
case ADMIN_CENTRE = 'ADMIN_CENTRE';
case FORMATEUR    = 'FORMATEUR';
case STAGIAIRE    = 'STAGIAIRE';
case ASSISTANT    = 'ASSISTANT';

    /**
     * Retourne le label lisible du rôle.
     *
     * @return string Label du rôle.
     */
    public function getLabel(): string {
        return match ($this) {
            self::ADMIN_CENTRE => 'Admin',
            self::FORMATEUR => 'Formateur',
            self::STAGIAIRE => 'Stagiaire',
            self::ASSISTANT => 'Assistant',
        };
    }

    /**
     * Retourne la liste des choix pour un formulaire (label => valeur).
     *
     * @return array<string, string> Tableau des choix.
     */
    public static function getChoices(): array {
        return array_reduce(self::cases(), function ($choices, $case) {
            $choices[$case->getLabel()] = $case->value;
            return $choices;
        }, []);
    }
}