<?php

declare(strict_types=1);

namespace Eseath\PayKeeper\Entities;

use DateTime;
use Eseath\PayKeeper\DTO;
use Eseath\PayKeeper\Enums\InvoiceStatuses;

class Invoice extends DTO
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $user_id,
        public readonly InvoiceStatuses $status,
        public readonly float $pay_amount,
        public readonly ?string $clientid,
        public readonly ?string $paymentid,
        public readonly ?string $service_name,
        public readonly ?string $client_email,
        public readonly ?string $client_phone,
        public readonly DateTime $expiry_datetime,
        public readonly DateTime $created_datetime,
        public readonly ?DateTime $paid_datetime,
        public readonly ?string $user_login,
    ) {}
}
