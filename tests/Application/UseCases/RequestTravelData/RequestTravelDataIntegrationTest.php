<?php

namespace App\Tests\Application\UseCases\RequestTravelData;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;
class RequestTravelDataIntegrationTest extends WebTestCase {
    private $client;

    public function setUp(): void {
        $this->client = static::createClient();
    }

    public function testTriggerEndpointSuccessfull() {
        $this->client->request('GET', 'api/avail?origin=MAD&destination=BIO&date=2022-06-01');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
    
        $this->assertEquals('MAD', $data[0]['originCode']);
        $this->assertEquals('BIO', $data[0]['destinationCode']);
        $this->assertEquals('IB', $data[0]['companyCode']);
        $this->assertEquals('Iberia', $data[0]['companyName']);
        $this->assertEquals('0426', $data[0]['transportNumber']);
    }
    public function testTriggerEndpointFailure() {
        $this->client->request('GET', 'api/avail?origin=XXXX&destination=TTTTTT&date=2022-06-01');
        $response = $this->client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey(0, $data, 'This value should have exactly 3 characters.');
    }
    public function testCommandWithSuccess() {
        self::bootKernel();

        $application = new Application();
        $application->add(self::$kernel->getContainer()->get('App\Interface\Commands\TravelCommand'));

        
        $commandTester = new CommandTester($application->find('lleego:avail'));

        $commandTester->execute([
            'origin' => 'MAD',
            'destination' => 'BIO',
            'date' => '2022-06-01'
        ]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString("Madrid Adolfo Suarez-Barajas", $output);
        $this->assertStringContainsString("MAD", $output);
        $this->assertStringContainsString("2022-06-01 15:55", $output);
        $this->assertStringContainsString("0440", $output);
    }

    public function testCommandWithError() {
        self::bootKernel();

        $application = new Application();
        $application->add(self::$kernel->getContainer()->get('App\Interface\Commands\TravelCommand'));

        
        $commandTester = new CommandTester($application->find('lleego:avail'));

        $commandTester->execute([
            'origin' => 'TEST',
            'destination' => 'TEST',
            'date' => '2022-31-31'
        ]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString("This value should have exactly 3 characters", $output);
        $this->assertStringContainsString("This value is not a valid date.", $output);
    }
}