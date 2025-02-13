<?php

declare(strict_types=1);

namespace App\MessageBus\Query\GetFileProgressQuery;

use Predis\ClientInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
readonly class GetFileProgressHandler
{
    public function __construct(
        private ClientInterface $client
    ) {
    }

    public function __invoke(GetFileProgressQuery $query): string
    {
        $progress = $this->client->get($query->fileName);

        if (!$progress) {
            $progress = '0';
        }

        return $progress;
    }
}
