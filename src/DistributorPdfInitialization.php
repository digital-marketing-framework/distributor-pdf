<?php

namespace DigitalMarketingFramework\Distributor\Pdf;

use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Distributor\Core\DataProvider\DataProviderInterface;
use DigitalMarketingFramework\Distributor\Pdf\DataProvider\PdfDataProvider;

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
}
