<?php

declare(strict_types=1);

namespace Eseath\PayKeeper\Attributes;

use Attribute;

#[Attribute]
final class MapFrom
{
    public function __construct(public string $sourceField) {}
}
