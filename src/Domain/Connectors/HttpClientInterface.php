<?php

namespace App\Domain\Connectors;
interface HttpClientInterface {
    function request(string $url, array $data, ?string $method = 'GET');
}