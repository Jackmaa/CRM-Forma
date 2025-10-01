<?php
namespace App\Service;

final class ConventionSnapshotBuilder {
    public function build(array $data): array {
        $of        = $data['of'];                              // centre
        $session   = $data['session'];                         // titre, code, dates...
        $stagiaire = $data['stagiaire'] + ['address' => null]; // fullname, email...
        $pricing   = $data['pricing'] ?? ['ht' => 0, 'tva_rate' => 0, 'tva' => 0, 'ttc' => 0];

        $legal = [
            'cancel' => "En cas d’annulation, se référer aux CGV de l’organisme.",
            'rgpd'   => "Les données sont traitées conformément au RGPD.",
        ];

        // issued_at / place
        $meta = [
            'issued_at' => (new \DateTimeImmutable())->format('Y-m-d'),
            'place'     => $session['location'] ?? '',
        ];

        return compact('of', 'session', 'stagiaire', 'pricing') + ['legal' => $legal, 'meta' => $meta];
    }
}
