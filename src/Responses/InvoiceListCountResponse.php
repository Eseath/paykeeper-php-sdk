<?php

declare(strict_types=1);

namespace Eseath\PayKeeper\Responses;

use Eseath\PayKeeper\Entities\InvoiceStatusCounter;

class InvoiceListCountResponse
{
    /**
     * @param InvoiceStatusCounter[] $statuses
     */
    public function __construct(
        public readonly array $statuses,
        public readonly int $total,
    ) {}
}
