<?php

namespace DigitalMarketingFramework\Distributor\Pdf\Service;

use Exception;
use FPDM;

class PdfService
{
    /**
     * @param array<mixed> $settings
     *
     * @return array<mixed>|bool
     */
    public function generatePdf($settings)
    {
        if (!is_array($settings['pdfFormFields']) || $settings['pdfOutputDir'] == '' || $settings['pdfOutputName'] == '' || $settings['pdfTemplatePath'] == '' || !file_exists($settings['pdfTemplatePath'])) {
            return false;
        }

        $processedFields = $settings['pdfFormFields'];

        $tempDir = $this->createUniqueTempDirectory($settings['pdfOutputDir']);
        if (!$tempDir) {
            return false;
        }

        $generatedPdf = $tempDir . '/' . $settings['pdfOutputName'];

        try {
            $pdf = new FPDM($settings['pdfTemplatePath']); // @phpstan-ignore-line because the FPDM constructor gets its arguments via func_get_args()
            if ($settings['useCheckboxParser']) {
                $pdf->useCheckboxParser = true;
            }

            $pdf->Load($processedFields, true);
            $pdf->Merge();
            $pdf->Output('F', $generatedPdf);

            return ['fileName' => $settings['pdfOutputName'], 'publicUrl' => $generatedPdf, 'relativePath' => $generatedPdf, 'mimeType' => mime_content_type($generatedPdf)];
        } catch (Exception) {
            // TODO: REALLY catch errors like "FPDF-Merge Error: field companyname not found"
            return false;
        }
    }

    /**
     * Create unique temp Directory
     *
     * @return string|bool
     */
    private function createUniqueTempDirectory(string $dir, int $maxTries = 500)
    {
        if (!file_exists($dir)) {
            mkdir($dir);
        }

        $dir .= uniqid('pdf_') . '_';
        $tries = 0;
        while ($tries < $maxTries && file_exists($dir . $tries)) {
            ++$tries;
        }

        $dir .= $tries;
        if (!file_exists($dir) && mkdir($dir)) {
            return $dir;
        }

        return false;
    }
}
