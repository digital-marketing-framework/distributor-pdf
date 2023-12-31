<?php

namespace DigitalMarketingFramework\Distributor\Pdf;

use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProviderInterface;
use DigitalMarketingFramework\Distributor\Pdf\DataProvider\PdfDataProvider;
use DigitalMarketingFramework\Distributor\Pdf\Service\PdfService;

class DistributorPdfInitialization extends Initialization
{
    public const PLUGINS = [
        RegistryDomain::DISTRIBUTOR => [
            DataProviderInterface::class => [
                PdfDataProvider::class,
            ],
        ],
    ];

    protected const SCHEMA_MIGRATIONS = [];

    public function __construct()
    {
        parent::__construct('distributor-pdf', '1.0.0');
    }

    protected function getAdditionalPluginArguments(string $interface, string $pluginClass, RegistryInterface $registry): array
    {
        if ($pluginClass === PdfDataProvider::class) {
            return [$registry->createObject(PdfService::class)];
        }

        return parent::getAdditionalPluginArguments($interface, $pluginClass, $registry);
    }
}
