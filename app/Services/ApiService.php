<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class ApiService
{
    public function __construct(
        private Client $client
    ) {
    }

    public function get()
    {
        try {
            $response = $this->client->request('GET');

            $responseArray = $this->handleResponse($response);

            if (!isset($responseArray['quote'])) {
                Log::critical(
                    'Unable to find the quote key in the response, perhaps the API has changed?'
                );
                throw new \Exception("Unable to find the quote key in the kanya api");
            }

            return $responseArray['quote'];
        } catch (\Exception $e) {
            Log::critical(
                "There is an issue with the kanye api",
                [
                    'message' => $e->getMessage(),
                    'status_code' => $e->getCode(),
                ]
            );

            throw $e;
        }
    }

    protected function handleResponse(ResponseInterface $response)
    {
        // perhaps replace with a proper serializer
        return json_decode($response->getBody()->getContents(), true);
    }
}
