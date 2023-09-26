<?php

namespace DigitalMarketingFramework\Distributor\Pdf\DataProvider;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\MapSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Model\Data\Value\FileValue;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProvider;
use DigitalMarketingFramework\Distributor\Pdf\Service\PdfService;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class PdfDataProvider extends DataProvider
{
    public const KEY_FIELD = 'field';
    public const DEFAULT_FIELD = 'pdf_form';

    public const KEY_PDF_TEMPLATE_PATH = 'pdfTemplatePath';
    public const DEFAULT_PDF_TEMPLATE_PATH = '';

    public const KEY_PDF_OUTPUT_DIR = 'pdfOutputDir';
    public const DEFAULT_PDF_OUTPUT_DIR = '';

    public const KEY_PDF_OUTPUT_NAME = 'pdfOutputName';
    public const DEFAULT_PDF_OUTPUT_NAME = '';

    public const KEY_PDF_FORM_FIELDS = 'pdfFormFields';
    public const DEFAULT_PDF_FORM_FIELDS = [];

    public const KEY_USE_CHECKBOX_PARSER = 'useCheckboxParser';
    public const DEFAULT_USE_CHECKBOX_PARSER = 0;

    protected function processContext(ContextInterface $context): void
    {
    }

    protected function process(): void
    {
        $settings = [
            'pdfTemplatePath' => $this->getConfig(static::KEY_PDF_TEMPLATE_PATH),
            'pdfOutputDir' => $this->getConfig(static::KEY_PDF_OUTPUT_DIR),
            'pdfOutputName' => $this->getConfig(static::KEY_PDF_OUTPUT_NAME),
            'pdfFormFields' => $this->getMapConfig(static::KEY_PDF_FORM_FIELDS),
            'useCheckboxParser' => $this->getConfig(static::KEY_USE_CHECKBOX_PARSER)
        ];
        $serviceObject = GeneralUtility::makeInstance(PdfService::class);
        $pdf = $serviceObject->generatePdf($settings);
        if (is_array($pdf)) {
            $pdfField = FileValue::unpack($pdf);
            $this->setField($this->getConfig(static::KEY_FIELD), $pdfField);
        }
    }
    
    public static function getSchema(): SchemaInterface
    {
        $schema = parent::getSchema();
        $schema->addProperty(static::KEY_FIELD, new StringSchema(static::DEFAULT_FIELD));
        $schema->addProperty(static::KEY_PDF_TEMPLATE_PATH, new StringSchema(static::DEFAULT_PDF_TEMPLATE_PATH));
        $schema->addProperty(static::KEY_PDF_OUTPUT_DIR, new StringSchema(static::DEFAULT_PDF_OUTPUT_DIR));
        $schema->addProperty(static::KEY_PDF_OUTPUT_NAME, new StringSchema(static::DEFAULT_PDF_OUTPUT_NAME));
        $schema->addProperty(static::KEY_PDF_FORM_FIELDS, new MapSchema(new StringSchema()));
        $schema->addProperty(static::KEY_USE_CHECKBOX_PARSER, new BooleanSchema(static::DEFAULT_USE_CHECKBOX_PARSER));
        return $schema;
    }
}
