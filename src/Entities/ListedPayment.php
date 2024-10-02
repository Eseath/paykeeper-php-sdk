<?php

declare(strict_types=1);

namespace Eseath\PayKeeper\Entities;

use DateTime;
use Eseath\PayKeeper\DTO;
use Eseath\PayKeeper\Enums\PaymentStatuses;

class ListedPayment extends DTO
{
    public function __construct(
        public readonly int $id,
        public readonly float $pay_amount,
        public readonly ?float $refund_amount,
        public readonly ?string $clientid,
        public readonly ?string $orderid,
        public readonly int $payment_system_id,
        public readonly ?string $unique_id,
        public readonly PaymentStatuses $status,
        public readonly int $repeat_counter,
        public readonly DateTime $pending_datetime,
        public readonly ?DateTime $obtain_datetime,
        public readonly ?DateTime $success_datetime,
    ) {}
}
