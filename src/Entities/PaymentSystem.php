<?php

declare(strict_types=1);

namespace Eseath\PayKeeper\Entities;

class PaymentSystem
{
    public function __construct(
        public readonly string $id,
        public readonly string $system_description,
        public readonly string $site_description,
    ) {}
}
