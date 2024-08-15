<?php

namespace App\Application\UseCases\RequestTravelData;

class RequestTravelDataCommand {
    public function __construct(
            private string $origin,
            private string $destination,
            private \DateTime $date
        ) {
    }
    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }
}