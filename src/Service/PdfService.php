<?php

namespace DigitalMarketingFramework\Distributor\Pdf\Service;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareInterface;
use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareTrait;
use DigitalMarketingFramework\Distributor\Pdf\FPDM;
use Exception;

class PdfService implements FileStorageAwareInterface
{
    use FileStorageAwareTrait;

    /**
     * @param array<mixed> $settings
     *
     * @return string|false the file identifier
     */
    public function generatePdf(array $settings): string|false
    {
        if (!is_array($settings['pdfFormFields']) || $settings['pdfOutputDir'] == '' || $settings['pdfOutputName'] == '' || $settings['pdfTemplatePath'] == '') {
            return false;
        }
        $processedFields = $settings['pdfFormFields'];
        $templateContents = $this->fileStorage->getFileContents($settings['pdfTemplatePath']);
        $tempFile = $this->fileStorage->writeTempFile('', $templateContents, '.pdf');
        $outputDir = $settings['pdfOutputDir'];
        $generatedPdfIdentifier = $outputDir . '/' . $settings['pdfOutputName'];
        if (!$this->fileStorage->fileExists($generatedPdfIdentifier)) {
            try {
                $pdf = new FPDM($tempFile); // @phpstan-ignore-line because the FPDM constructor gets its arguments via func_get_args()
                if ($settings['useCheckboxParser']) {
                    $pdf->useCheckboxParser = true;
                }
                $pdf->Load($processedFields, true);
                $pdf->Merge();
                $pdf->Output('F', $tempFile);
                $mergedContent = file_get_contents($tempFile);
                $this->fileStorage->putFileContents($generatedPdfIdentifier, $mergedContent);
            } catch (Exception $e) {
                throw new DigitalMarketingFrameworkException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $generatedPdfIdentifier;
    }

    /**
     * Create unique temp Directory
     *
     * @return string|bool
     */
    public function createUniqueDirectory(string $dir, int $maxTries = 500)
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
