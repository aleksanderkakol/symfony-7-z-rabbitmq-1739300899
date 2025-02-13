<?php

declare(strict_types=1);

namespace App\Service\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class UserBatchInserter
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * @param array<int, array{id: string, full_name: string, email: string, city: string}> $batchData
     * @throws Exception
     */
    public function insertBatch(array $batchData): void
    {
        if (empty($batchData)) {
            return;
        }

        $sql = 'INSERT INTO user (id, full_name, email, city) VALUES ';
        $values = [];
        $parameters = [];

        foreach ($batchData as $data) {
            $values[] = '(?, ?, ?, ?)';
            $parameters[] = $data['id'];
            $parameters[] = $data['full_name'];
            $parameters[] = $data['email'];
            $parameters[] = $data['city'];
        }

        $sql .= implode(', ', $values);

        $con = $this->connection->getNativeConnection();
        $stmt = $con->prepare($sql);
        $stmt->execute($parameters);
    }
}
