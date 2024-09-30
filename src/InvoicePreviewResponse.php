<?php

declare(strict_types=1);

namespace Eseath\PayKeeper;

class InvoicePreviewResponse
{
    public function __construct(
        public readonly string $invoice_id,
        public readonly string $invoice_url,
        public readonly string $invoice,
    ) {}
}
