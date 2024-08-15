<?php

namespace App\Application\UseCases\RequestTravelData;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;


class RequestTravelDataValidator {
    private ValidatorInterface $validator;

    public function __construct() {
        $this->validator = Validation::createValidator();
    }

    public function validate(string $origin, string $destination, string $date): array {
        $constraints = new Assert\Collection([
            'origin' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 3, 'max' => 3]),
                new Assert\Type('string'),
            ],
            'destination' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 3, 'max' => 3]),
                new Assert\Type('string'),
            ],
            'date' => [
                new Assert\NotBlank(),
                new Assert\Date(),
                new Assert\Regex('/^\d{4}-\d{2}-\d{2}$/'),
            ],
        ]);

        $input = [
            'origin' => $origin,
            'destination' => $destination,
            'date' => $date,
        ];

        $violations = $this->validator->validate($input, $constraints);

        $errors = [];
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
        }

        return $errors;
    }
}