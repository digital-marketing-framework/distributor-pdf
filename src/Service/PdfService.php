<?php

namespace DigitalMarketingFramework\Distributor\Pdf\Service;

use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareInterface;
use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareTrait;
use DigitalMarketingFramework\Typo3\Core\Utility\VendorAssetUtility;
use Exception;
use FPDM;

class PdfService implements FileStorageAwareInterface
{

    use FileStorageAwareTrait;

    /**
     * @param array<mixed> $settings
     *
     * @return array<mixed>|bool
     */
    public function generatePdf(array $settings): array|bool
    {
        if (!is_array($settings['pdfFormFields']) || $settings['pdfOutputDir'] == '' || $settings['pdfOutputName'] == '' || $settings['pdfTemplatePath'] == '' || $settings['pdfTemplatePath'] == '') {
            return false;
        }

        $processedFields = $settings['pdfFormFields'];

        $templateContents = $this->fileStorage->getFileContents($settings['pdfTemplatePath']);
        $tempFile = $this->fileStorage->writeTempFile('', $templateContents, '.pdf');

        try {
            $pdf = new FPDM($tempFile); // @phpstan-ignore-line because the FPDM constructor gets its arguments via func_get_args()
            if ($settings['useCheckboxParser']) {
                $pdf->useCheckboxParser = true;
            }

            $pdf->Load($processedFields, true);
            $pdf->Merge();
            $pdf->Output('F', $tempFile);
            $mergedContent = file_get_contents($tempFile);
            $uniqueOutputDir = $this->createUniqueDirectory($settings['pdfOutputDir']);
            if (!$uniqueOutputDir) {
                return false;
            }
            $generatedPdf = $uniqueOutputDir . '/' . $settings['pdfOutputName'];
            $this->fileStorage->putFileContents($generatedPdf, $mergedContent);
            unlink($tempFile);
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
    private function createUniqueDirectory(string $dir, int $maxTries = 500)
    {
        if (!$this->fileStorage->folderExists($dir)) {
            $this->fileStorage->createFolder($dir);
        }

        $dir .= uniqid('pdf_') . '_';
        $tries = 0;
        while ($tries < $maxTries && $this->fileStorage->folderExists($dir . $tries)) {
            ++$tries;
        }

        $dir .= $tries;
        $this->fileStorage->createFolder($dir);
        if ($this->fileStorage->folderExists($dir)) {
            return $dir;
        }
        return false;
    }
}
