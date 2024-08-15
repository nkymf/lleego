<?php

namespace App\Application\Services;

use App\Domain\Entity\Segment;
use App\Domain\Connectors\HttpClientInterface;

class SegmentService {
    const ENDPOINT = 'https://testapi.lleego.com/prueba-tecnica/availability-price';
    const DATE_FORMAT = 'Y-m-d H:i';
    private $flightSegments = [];


    public function __construct(private HttpClientInterface $httpClientInterface) { 
    }


    public function requestXMLData($origin, $date , $destination): ?\SimpleXMLElement {  
        $response = $this->httpClientInterface->request(self::ENDPOINT, [
            'origin' => $origin,
            'date' => $date,
            'destination' => $destination
        ], 'GET');

        if (!empty($response)){
            return simplexml_load_string($response);
        }

        return null;
    }

    /**
     * @return Segment[]
     */
    public function parseData($xml): array {
        $parsedData = [];   
        $namespaces = $xml->getNamespaces(true);

        $xml->registerXPathNamespace('soap', $namespaces['soap']);
        $xml->registerXPathNamespace('ns', 'http://www.iata.org/IATA/EDIST/2017.2');
        $flightSegments = $xml->xpath('//ns:AirShoppingRS/ns:DataLists/ns:FlightSegmentList/ns:FlightSegment');
        
        foreach ($flightSegments as $segment) {
            try {
                $fligtSegment = new Segment();
                //Departure info
                $fligtSegment->setOriginCode((string) $segment->Departure->AirportCode);
                $fligtSegment->setOriginName((string) $segment->Departure->AirportName);
                $startDate = \DateTime::createFromFormat(self::DATE_FORMAT, (string) $segment->Departure->Date . ' '. (string) $segment->Departure->Time);
                $fligtSegment->setStart($startDate);

                //Arrival info
                $fligtSegment->setDestinationCode((string) $segment->Arrival->AirportCode);
                $fligtSegment->setDestinationName((string) $segment->Arrival->AirportName);
                $endDate = \DateTime::createFromFormat(self::DATE_FORMAT, (string) $segment->Arrival->Date . ' '. (string) $segment->Arrival->Time);
                $fligtSegment->setEnd($endDate);

                //Marketing info
                $fligtSegment->setTransportNumber((string) $segment->MarketingCarrier->FlightNumber);
                $fligtSegment->setCompanyName((string) $segment->MarketingCarrier->Name);
                $fligtSegment->setCompanyCode((string) $segment->MarketingCarrier->AirlineID);
                
                $parsedData[] = $fligtSegment;
            }

            catch (\Throwable $error) {
                // Sentry or logging implmentation for faulty XML info.
            }
        }
        return $parsedData;
    }
}
