<?php

declare(strict_types=1);

namespace App\MessageBus\Command\UploadUserList;

use App\Core\Service\Messenger\Attribute\AsCommandValidator;
use App\Exception\ValidateFail;

#[AsCommandValidator]
readonly class UploadUserListValidator
{
    public function __invoke(UploadUserListCommand $command): void
    {
        $filePath = $command->path.$command->fileName;
        if (!file_exists($filePath)) {
            throw new ValidateFail('File does not exists');
        }

        $totalLines = count(file($filePath)) - 1;
        if ($totalLines <= 0) {
            throw new ValidateFail('Empty file');
        }
    }
}
