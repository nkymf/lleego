<?php

namespace App\Application\UseCases\RequestTravelData;

use App\Application\Services\SegmentService;
use App\Domain\ValueObject\BasicResponse;

class RequestTravelDataHandler {
    public function __construct(private SegmentService $segmentService) {
        
    }
    public function handle(RequestTravelDataCommand $command) {
        $xml = $this->segmentService->requestXMLData(
            origin:  $command->getOrigin(),
            date: $command->getDate()->format('Y-m-d'),
            destination: $command->getDestination()
        );

        if (!isset($xml)) {
            return [
                'status' => false,
                'data' => [],
                'message' => 'Error communication with API'
            ];
        }

        return [
            'status' => true,
            'data' => $this->segmentService->parseData($xml)
        ];
    }
}