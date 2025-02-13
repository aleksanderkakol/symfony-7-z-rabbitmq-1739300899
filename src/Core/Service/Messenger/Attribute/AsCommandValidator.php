<?php

declare(strict_types=1);

namespace App\Core\Service\Messenger\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class AsCommandValidator
{
    public function __construct(
        public ?string $method = null,
    ) {
    }
}
