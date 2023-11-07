<?php

namespace DigitalMarketingFramework\Distributor\Pdf;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;

class FPDM extends \FPDM
{
    /** @phpstan-impure */
    function Error($msg) {
        throw new DigitalMarketingFrameworkException($msg, 1699365138);
    }
}
