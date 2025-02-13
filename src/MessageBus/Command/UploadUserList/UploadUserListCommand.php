<?php

declare(strict_types=1);

namespace App\MessageBus\Command\UploadUserList;

class UploadUserListCommand
{
    public function __construct(
        public string $path,
        public string $fileName
    ) {
    }
}
