<?php

declare(strict_types=1);

namespace App\MessageBus\Query\GetFileErrorsQuery;

readonly class GetFileErrorsQuery
{
    public function __construct(
        public string $fileName,
    ) {
    }
}
