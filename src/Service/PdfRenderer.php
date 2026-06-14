<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

final readonly class PdfRenderer
{
    public function __construct(private Environment $twig)
    {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function render(string $template, array $context): string
    {
        $html = $this->twig->render($template, $context);
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Serif');
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
