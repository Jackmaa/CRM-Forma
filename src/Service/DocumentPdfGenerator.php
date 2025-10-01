<?php
// src/Service/DocumentPdfGenerator.php
namespace App\Service;

use Dompdf\Dompdf;
use Twig\Environment as Twig;

final class DocumentPdfGenerator {
    public function __construct(private Twig $twig, private string $basePath) {}

    public function renderToPdf(string $template, array $vars, string $targetRelPath): string {
        $html = $this->twig->render($template, $vars);
        $abs  = rtrim($this->basePath, '/') . '/' . $targetRelPath;
        @mkdir(\dirname($abs), 0775, true);

        $pdf = new Dompdf(['isRemoteEnabled' => true]);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        file_put_contents($abs, $pdf->output());
        return $abs;
    }
}
