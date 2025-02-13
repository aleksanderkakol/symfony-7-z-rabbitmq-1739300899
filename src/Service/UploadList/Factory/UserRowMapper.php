<?php

declare(strict_types=1);

namespace App\Service\UploadList\Factory;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class UserRowMapper
{
    private const int ID = 0;
    private const int FULL_NAME = 1;
    private const int EMAIL = 2;
    private const int CITY = 3;

    /**
     * @throws RowValidationException
     */
    public function map(array $row): array
    {
        $mappedData = [
            'id' => (int) $row[self::ID],
            'full_name' => $row[self::FULL_NAME],
            'email' => $row[self::EMAIL],
            'city' => $row[self::CITY],
        ];

        $this->validateMappedData($mappedData);

        return $mappedData;
    }

    /**
     * @throws RowValidationException
     */
    private function validateMappedData(array $data): void
    {
        $validator = Validation::createValidator();

        $constraints = new Assert\Collection([
            'id' => [
                new Assert\Type('integer'),
                new Assert\GreaterThan(0),
            ],
            'full_name' => [
                new Assert\Type('string'),
                new Assert\Length(['min' => 3]),
            ],
            'email' => [
                new Assert\Type('string'),
                new Assert\Email(),
            ],
            'city' => [
                new Assert\Type('string'),
                new Assert\Length(['min' => 3]),
            ],
        ]);

        $errors = $validator->validate($data, $constraints);

        if (count($errors) > 0) {
            throw new RowValidationException();
        }
    }
}
