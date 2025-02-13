<?php

declare(strict_types=1);

namespace App\Service\UploadList\Factory;

class RowValidationException extends \Exception
{
    public function __construct()
    {
        parent::__construct();
    }
}
