<?php

namespace App\Interface\Controllers;

use App\Application\UseCases\RequestTravelData\RequestTravelDataCommand;
use App\Application\UseCases\RequestTravelData\RequestTravelDataHandler;
use App\Application\UseCases\RequestTravelData\RequestTravelDataValidator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TravelController extends AbstractController {


    public function __construct(
            private RequestTravelDataHandler $requestTravelDataHandler,
            private RequestTravelDataValidator $validator
        ) {
    }
    public function trigger(Request $request): Response
    {   
        $errors = $this->validator->validate(
            origin: $request->query->get('origin'),
            destination: $request->query->get('destination'),
            date: $request->query->get('date')
        );

        if ($errors) {
            /**  @Note maybe validion format should be more explicit than the plain json from the validator. */
            return new Response(json_encode($errors), Response::HTTP_BAD_REQUEST);
        }

        $response = $this->requestTravelDataHandler->handle(new RequestTravelDataCommand(
                origin: $request->query->get('origin'),
                destination: $request->query->get('destination'),
                date: \DateTime::createFromFormat('Y-m-d', $request->query->get('date') 
            )
        ));
        
        if ($response['status']) {
            return new Response(json_encode($response['data'], JSON_PRETTY_PRINT));
        }

        return new Response($response['message'] , Response::HTTP_BAD_REQUEST);
    }
}
