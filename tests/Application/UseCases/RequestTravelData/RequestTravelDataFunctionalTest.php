<?php

namespace App\Tests\Application\UseCases\RequestTravelData;

use App\Application\Services\SegmentService;
use App\Application\UseCases\RequestTravelData\RequestTravelDataCommand;
use App\Application\UseCases\RequestTravelData\RequestTravelDataHandler;
use App\Application\UseCases\RequestTravelData\RequestTravelDataValidator;
use App\Domain\Entity\Segment;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RequestTravelDataFunctionalTest extends KernelTestCase {

    private RequestTravelDataValidator $requestTravelValidator;
    protected function setUp(): void {
        $this->requestTravelValidator = new RequestTravelDataValidator();
    }

    public function testTravelDataIfApiFails() {
        // We will create a partial mock for segmentService for chaning the xml method that brings data from third party.
        // we will only override that method and the rest of the service that parses the data will remain the same.
        
        $segmentServiceMock = $this->getMockBuilder(SegmentService::class)
        ->disableOriginalConstructor() 
        ->onlyMethods(['requestXMLData']) 
        ->getMock();

        $segmentServiceMock->method('requestXMLData')->willReturn(null);

        $handler = new RequestTravelDataHandler($segmentServiceMock);

        $response = $handler->handle(new RequestTravelDataCommand(
            destination: 'BIO',
            origin: 'MAD',
            date: new \DateTime('2022-06-01')
        ));

        $this->assertArrayHasKey('status', $response);
        $this->assertFalse($response['status']);
    }

    public function testTravelDataWithMockData() {
        $segmentServiceMock = $this->getMockBuilder(SegmentService::class)
                     ->disableOriginalConstructor()
                     ->onlyMethods(['requestXMLData']) 
                     ->getMock();

        $segmentServiceMock->method('requestXMLData')->willReturn(
            simplexml_load_string(
                file_get_contents('tests/resources/MAD_BIO_OW_1PAX_RS_SOAP.xml')
            )
        );

        $handler = new RequestTravelDataHandler($segmentServiceMock);
        
        $response = $handler->handle(new RequestTravelDataCommand(
            destination: 'BIO',
            origin: 'MAD',
            date: new \DateTime('2022-06-01')
        ));

        $data = $response['data'];

        $this->assertTrue($response['status']);
        $this->assertNotEmpty($data);
        $this->assertInstanceOf(Segment::class, $data[0]);
        $this->assertEquals('MAD', $data[0]->getOriginCode());
        $this->assertEquals('BIO', $data[0]->getDestinationCode());
        $this->assertEquals('IB', $data[0]->getCompanyCode());
        $this->assertEquals('Iberia', $data[0]->getCompanyName());
        $this->assertEquals('0426', $data[0]->getTransportNumber());
    }

    public function testValidatorOfTheRequest() {
        $errors = $this->requestTravelValidator->validate(
            origin: 'MAD',
            destination: 'BIO',
            date: '2022-06-01'
        );

        $this->assertEmpty($errors);

        $errors = $this->requestTravelValidator->validate(
            origin: 'TEST',
            destination: 'BIO',
            date: '2022-06-01'
        );

        $this->assertArrayHasKey(0, $errors, 'The key 0 is missing in the errors array.');

        $errors = $this->requestTravelValidator->validate(
            origin: 'MAD',
            destination: 'BIO',
            date: '2022-31-31'
        );

        $this->assertArrayHasKey(0, $errors, 'This value is not a valid date.');
    }
}