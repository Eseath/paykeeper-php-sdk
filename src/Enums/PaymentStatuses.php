<?php

declare(strict_types=1);

namespace Eseath\PayKeeper\Enums;

enum PaymentStatuses
{
    case pending;
    case obtained;
    case canceled;
    case success;
    case failed;
    case stuck;
    case refunded;
    case refunding;
    case partially_refunded;
}
