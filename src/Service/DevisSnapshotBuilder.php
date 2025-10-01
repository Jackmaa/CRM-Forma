<?php
// src/Service/DevisSnapshotBuilder.php
namespace App\Service;

final class DevisSnapshotBuilder {
    public function __construct(
        private int $defaultValidityDays = 30,
        private int $defaultTvaRate = 20
    ) {}

    /** @param array{of:array,dest:array,lines:array,meta:array} $data */
    public function build(array $data): array {
        $totalHt  = 0.0;
        $totalTva = 0.0;
        foreach ($data['lines'] as &$l) {
            $desc = (string) ($l['description'] ?? '');
            $qty  = (float) ($l['qty'] ?? 1);
            $pu   = (float) ($l['unit_ht'] ?? 0);
            $tva  = isset($l['tva_rate']) && $l['tva_rate'] !== ''
                ? (float) $l['tva_rate']
                : (float) $this->defaultTvaRate;

            $total_ht   = round($qty * $pu, 2);
            $tva_amount = round($total_ht * $tva / 100, 2);
            $total_ttc  = round($total_ht + $tva_amount, 2);

            $l = [
                'description' => $desc,
                'qty'         => $qty,
                'unit_ht'     => $pu,
                'tva_rate'    => $tva,
                'total_ht'    => $total_ht,
                'tva_amount'  => $tva_amount,
                'total_ttc'   => $total_ttc,
            ];

            $totalHt += $total_ht;
            $totalTva += $tva_amount;
        }

        $validityDays = (int) ($data['meta']['validity_days'] ?? $this->defaultValidityDays);
        $validityEnd  = new \DateTimeImmutable("+{$validityDays} days");

        return [
            'of'     => $data['of'],
            'dest'   => $data['dest'],
            'lines'  => $data['lines'],
            'meta'   => [
                'formation_title' => $data['meta']['formation_title'] ?? null,
                'session_code'    => $data['meta']['session_code'] ?? null,
                'hours_total'     => $data['meta']['hours_total'] ?? null,
                'modality'        => $data['meta']['modality'] ?? null,
                'tva_exempt'      => (bool) ($data['meta']['tva_exempt'] ?? false),
                'validity_days'   => $validityDays,
                'validity_end'    => $validityEnd, // objet DateTimeImmutable => sûr pour Twig
            ],
            'totals' => [
                'ht'  => round($totalHt, 2),
                'tva' => round($totalTva, 2),
                'ttc' => round($totalHt + $totalTva, 2),
            ],
            'legal'  => [
                'tva_text' => 'TVA non applicable — art. 293 B du CGI',
            ],
        ];
    }
}
