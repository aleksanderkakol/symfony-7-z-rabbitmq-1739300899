<?php

declare(strict_types=1);

namespace App\MessageBus\Query\GetFileProgressQuery;

readonly class GetFileProgressQuery
{
    public function __construct(
        public string $fileName,
    ) {
    }
}
