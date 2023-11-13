<?php

namespace DigitalMarketingFramework\Distributor\Pdf;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;

class FPDM extends \FPDM
{
    /** @phpstan-impure */
    public function Error($msg): never
    {
        throw new DigitalMarketingFrameworkException($msg, 1699365138);
    }
}
