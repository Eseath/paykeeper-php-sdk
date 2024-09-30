<?php

declare(strict_types=1);

namespace Eseath\PayKeeper\Enums;

enum InvoiceStatuses
{
    case created;
    case sent;
    case paid;
    case expired;
}
