<?php

namespace DigitalMarketingFramework\Distributor\Pdf\Service;

class PdfService
{
    /**
     *
     * @param array<mixed> $settings
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
        $generatedPdf =  $tempDir . '/' . $settings['pdfOutputName'];

        try {
            $pdf = new \FPDM($settings['pdfTemplatePath']);
            if ($settings['useCheckboxParser']) {
                $pdf->useCheckboxParser = true;
            }
            $pdf->Load($processedFields, true);
            $pdf->Merge();
            $pdf->Output('F', $generatedPdf);
            return array(
                'fileName' => $settings['pdfOutputName'],
                'publicUrl' => $generatedPdf,
                'relativePath' => $generatedPdf,
                'mimeType' => mime_content_type($generatedPdf)
            );
        } catch (\Exception $e) {
            // TODO: REALLY catch errors like "FPDF-Merge Error: field companyname not found"
            return false;
        }
    }
    /**
     * Create unique temp Directory
     *
     * @param string $dir
     * @param integer $maxTries
     * @return string|bool
     */
    private function createUniqueTempDirectory(string $dir, int $maxTries = 500)
    {
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        $dir .= uniqid('pdf_') . '_';
        $tries = 0;
        for ($tries = 0; $tries < $maxTries && file_exists($dir . $tries); $tries++) {
        }
        $dir .= $tries;
        if (!file_exists($dir) && mkdir($dir)) {
            return $dir;
        }
        return false;
    }
}
