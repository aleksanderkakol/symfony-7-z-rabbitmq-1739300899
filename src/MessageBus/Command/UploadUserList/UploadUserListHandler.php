<?php

declare(strict_types=1);

namespace App\MessageBus\Command\UploadUserList;

use App\Service\Database\UserBatchInserter;
use App\Service\UploadList\Factory\RowValidationException;
use App\Service\UploadList\Factory\UserRowMapper;
use Predis\ClientInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
class UploadUserListHandler
{
    private const int FLUSH_BULK_SIZE = 100;

    private int $totalLines;

    private string $fileName;

    public function __construct(
        private UserRowMapper $userRowMapper,
        private UserBatchInserter $batchInserter,
        private ClientInterface $redisClient
    ) {
    }

    public function __invoke(UploadUserListCommand $command): void
    {
        $this->fileName = $command->fileName;
        $filePath = $command->path.$this->fileName;
        $this->totalLines = $this->countLines($filePath) - 1;

        $processedLines = 0;
        $numberOfErrors = 0;
        $file = fopen($filePath, 'rb');
        fgetcsv($file);
        while (($row = fgetcsv($file)) !== false) {
            ++$processedLines;
            $this->updateProgress($processedLines);
            try {
                $batchData[] = $this->userRowMapper->map($row);
            } catch (RowValidationException) {
                $numberOfErrors++;
                continue;
            }

            if(($processedLines % self::FLUSH_BULK_SIZE) === 0) {
                $this->batchInserter->insertBatch($batchData);
                $batchData = [];
            }
        }

        if (!empty($batchData)) {
            $this->batchInserter->insertBatch($batchData);
        }

        fclose($file);

        $this->storeErrors($numberOfErrors);
    }

    private function updateProgress(int $processedLines): void
    {
        $progress = ($processedLines / $this->totalLines) * 100;
        $this->redisClient->set($this->fileName, $progress, 'EX', 60);
    }

    private function storeErrors(int $errors): void
    {
        if (!empty($errors)) {
            $this->redisClient->set(
                $this->fileName . '_errors',
                $errors,
                'EX',
                3600
            );
        }
    }

    private function countLines(string $filePath): int
    {
        $lineCount = 0;
        $file = fopen($filePath, 'rb');
        while (!feof($file)) {
            fgets($file);
            $lineCount++;
        }
        fclose($file);

        return $lineCount;
    }
}
