<?php

declare(strict_types=1);

namespace Eseath\PayKeeper\Entities;

use DateTime;
use Eseath\PayKeeper\DTO;
use Eseath\PayKeeper\Enums\InvoiceStatuses;

class InvoiceStatusCounter extends DTO
{
    public function __construct(
        public readonly string $name,
        public readonly int $quantity,
    ) {}
}
