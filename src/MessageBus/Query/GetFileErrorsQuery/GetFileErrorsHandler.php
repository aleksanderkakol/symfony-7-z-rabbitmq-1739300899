<?php

declare(strict_types=1);

namespace App\MessageBus\Query\GetFileErrorsQuery;

use Predis\ClientInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
readonly class GetFileErrorsHandler
{
    public function __construct(
        private ClientInterface $client
    ) {
    }

    public function __invoke(GetFileErrorsQuery $query): ?string
    {
        return $this->client->get($query->fileName.'_errors');
    }
}
