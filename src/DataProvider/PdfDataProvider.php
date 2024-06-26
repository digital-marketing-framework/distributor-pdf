<?php

namespace DigitalMarketingFramework\Distributor\Pdf\DataProvider;

use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessorAwareInterface;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessorAwareTrait;
use DigitalMarketingFramework\Core\DataProcessor\DataProcessorContext;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareInterface;
use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareTrait;
use DigitalMarketingFramework\Core\Model\Data\Value\FileValue;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\ValueSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\MapSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProvider;
use DigitalMarketingFramework\Distributor\Core\Model\DataSet\SubmissionDataSetInterface;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Pdf\Service\PdfService;

class PdfDataProvider extends DataProvider implements DataProcessorAwareInterface, FileStorageAwareInterface
{
    use DataProcessorAwareTrait;
    use FileStorageAwareTrait;

    public const KEY_FIELD = 'field';

    public const DEFAULT_FIELD = 'pdf_form';

    public const KEY_PDF_TEMPLATE_PATH = 'pdfTemplatePath';

    public const DEFAULT_PDF_TEMPLATE_PATH = '';

    public const KEY_PDF_OUTPUT_DIR = 'pdfOutputDir';

    public const DEFAULT_PDF_OUTPUT_DIR = '';

    public const KEY_PDF_OUTPUT_NAME = 'pdfOutputName';

    public const DEFAULT_PDF_OUTPUT_NAME = '';

    public const KEY_PDF_FORM_FIELDS = 'pdfFormFields';

    public const KEY_USE_CHECKBOX_PARSER = 'useCheckboxParser';

    public const DEFAULT_USE_CHECKBOX_PARSER = false;

    protected const KEY_UNIQUE_DIRECTORY_IDENTIFIER = 'uniqueDirectoryIdentifier';

    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        SubmissionDataSetInterface $submission,
        protected PdfService $pdfService
    ) {
        parent::__construct($keyword, $registry, $submission);
    }

    protected function processContext(WriteableContextInterface $context): void
    {
        $uniqueDirectoryName = $this->pdfService->createUniqueDirectory($this->getConfig(static::KEY_PDF_OUTPUT_DIR));
        if ($uniqueDirectoryName) {
            $context[self::KEY_UNIQUE_DIRECTORY_IDENTIFIER] = $uniqueDirectoryName;
        }
    }

    protected function process(): void
    {
        $pdfDirectoryName = $this->context[self::KEY_UNIQUE_DIRECTORY_IDENTIFIER] ?? null;
        if (!$pdfDirectoryName) {
            throw new DigitalMarketingFrameworkException(self::KEY_UNIQUE_DIRECTORY_IDENTIFIER . ' is missing in the context.', 1699453570);
        }

        $dataProcessorContext = new DataProcessorContext($this->submission->getData(), $this->submission->getConfiguration());
        $pdfFormFields = [];
        $pdfFormFieldsMap = $this->getMapConfig(static::KEY_PDF_FORM_FIELDS);
        foreach ($pdfFormFieldsMap as $pdfFieldName => $pdfFieldConfig) {
            $pdfFieldValue = $this->dataProcessor->processValue($pdfFieldConfig, $dataProcessorContext);
            if ($pdfFieldValue !== null) {
                $pdfFormFields[$pdfFieldName] = $pdfFieldValue;
            }
        }

        $settings = [
            'pdfTemplatePath' => $this->getConfig(static::KEY_PDF_TEMPLATE_PATH),
            'pdfOutputDir' => $pdfDirectoryName,
            'pdfOutputName' => $this->getConfig(static::KEY_PDF_OUTPUT_NAME),
            'pdfFormFields' => $pdfFormFields,
            'useCheckboxParser' => $this->getConfig(static::KEY_USE_CHECKBOX_PARSER),
        ];
        $pdfFileIdentifier = $this->pdfService->generatePdf($settings);
        if (!$pdfFileIdentifier) {
            throw new DigitalMarketingFrameworkException('Failed to create PDF, no reason given.', 1699453575);
        }

        $pdfField = new FileValue();
        $pdfField->setFileName($this->getConfig(static::KEY_PDF_OUTPUT_NAME));
        $pdfField->setRelativePath($pdfFileIdentifier);
        $pdfField->setMimeType('application/pdf');
        $pdfField->setPublicUrl($this->fileStorage->getPublicUrl($pdfFileIdentifier));
        $this->setField($this->getConfig(static::KEY_FIELD), $pdfField);
    }

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema */
        $schema = parent::getSchema();
        $schema->addProperty(static::KEY_FIELD, new StringSchema(static::DEFAULT_FIELD));
        $schema->addProperty(static::KEY_PDF_TEMPLATE_PATH, new StringSchema(static::DEFAULT_PDF_TEMPLATE_PATH));
        $schema->addProperty(static::KEY_PDF_OUTPUT_DIR, new StringSchema(static::DEFAULT_PDF_OUTPUT_DIR));
        $schema->addProperty(static::KEY_PDF_OUTPUT_NAME, new StringSchema(static::DEFAULT_PDF_OUTPUT_NAME));
        $schema->addProperty(static::KEY_PDF_FORM_FIELDS, new MapSchema(new CustomSchema(ValueSchema::TYPE)));
        $schema->addProperty(static::KEY_USE_CHECKBOX_PARSER, new BooleanSchema(static::DEFAULT_USE_CHECKBOX_PARSER));

        return $schema;
    }
}
