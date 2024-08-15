<?php

namespace App\Interface\Connectors;

use App\Domain\Connectors\HttpClientInterface;
use GuzzleHttp\Client;

class GuzzleConnector implements HttpClientInterface {
    private $client;
    public function __construct() {
        $this->client = new Client();
    }

    function request(string $url, array $data, ?string $method = 'GET') {
        $response = $method == 'GET' 
         ? $this->client->request($method, $url, ['query' => $data])
         : $this->client->request($method, $url, $data);

        $body = $response->getBody()->getContents();

        return $body;
    }
}
