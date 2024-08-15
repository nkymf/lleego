<?php

namespace App\Interface\Commands;

use App\Application\UseCases\RequestTravelData\RequestTravelDataCommand;
use App\Application\UseCases\RequestTravelData\RequestTravelDataHandler;
use App\Application\UseCases\RequestTravelData\RequestTravelDataValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'lleego:avail')]
class TravelCommand extends Command {
  
    public function __construct(
            private RequestTravelDataHandler $handler,
            private RequestTravelDataValidator $validator
        ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Requests travel information.')
            ->addArgument('origin', InputArgument::REQUIRED, 'Origin of the travel')
            ->addArgument('destination', InputArgument::REQUIRED, 'Destination of the travel')
            ->addArgument('date', InputArgument::REQUIRED, 'Date of the travel (YYYY-MM-DD)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $origin = $input->getArgument('origin');
        $destination = $input->getArgument('destination');
        $date = $input->getArgument('date');
        
        $errors = $this->validator->validate($origin, $destination, $date);

        if (!empty($errors)) {
            return $this->returnFailure($errors, $output);
        }
        

        $response = $this->handler->handle(new RequestTravelDataCommand(
            origin: $origin, 
            destination: $destination,
            date: \DateTime::createFromFormat('Y-m-d', $date) 
        ));

        if (!$response['status']){
            $output->writeln('<error>API communication errror.:</error>');
            return Command::FAILURE;
        }

        $table = new Table($output);
        $table->setHeaders(
            ['Origin Code', 'Origin Name','Destination Code', 'Destination Name', 'Start', 'End', 'Transport Number', 'Company Code', 'Company Name']
        );


        foreach($response['data'] as $segment) {
            $table->addRow([
                $segment->getOriginCode(),
                $segment->getOriginName(),
                $segment->getDestinationCode(),
                $segment->getDestinationName(),
                $segment->getStart()->format('Y-m-d H:i'),
                $segment->getEnd()->format('Y-m-d H:i'),
                $segment->getTransportNumber(),
                $segment->getCompanyName(),
                $segment->getCompanyCode()
            ]);
        }

        $table->render();
        
        return Command::SUCCESS;
    }

    private function returnFailure($errors, $output) {
        $output->writeln('<error>Validation failed:</error>');

        foreach ($errors as $error) {
            $output->writeln('<comment>' . $error . '</comment>');
        }

        return Command::FAILURE;
    }
}