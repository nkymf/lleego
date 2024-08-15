<?php

namespace App\Interface\Connectors;

use App\Domain\Connectors\HttpClientInterface;

class SoapConnector implements HttpClientInterface {
    function request(string $url, array $data, string|null $method = 'GET'): array|null {
        
        // Probably this would make more sense if the API was SOAP instead of REST. somehow the data returned has the SOAP data pattern.
        // Ask why the service is REST but returns SOAP data structure.

        return null;
    }

}
