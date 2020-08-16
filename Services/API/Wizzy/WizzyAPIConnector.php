<?php

namespace Wizzy\Search\Services\API\Wizzy;

use GuzzleHttp\Client;
use Wizzy\Search\Helpers\API\ResponseBuilder;
use Wizzy\Search\Model\API\Response;

class WizzyAPIConnector
{

    private $responseBuilder;

    public function __construct(ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
    }

    public function send(string $endPoint, string $method, $data, $headers, $isJsonRequest = false): Response
    {
        $method = strtolower($method);
        $isMethodSupported = $this->isMethodSupported($method);
        if ($isMethodSupported !== true) {
            return $isMethodSupported;
        }
        if ($isJsonRequest) {
            $headers['Content-Type'] = 'application/json';
        }

        $client = new Client();

        $options = [
        'form_params' => $data,
        'headers' => $headers,
        ];

        if ($isJsonRequest) {
            $options['body'] = json_encode($options['form_params']);
            unset($options['form_params']);
        }

        try {
            $response = $client->$method($endPoint, $options);
            if ($response->getStatusCode() != 200) {
                return $this->responseBuilder->error('Not able to connect to Wizzy Server', [
                'statusCode' => $response->getStatusCode(),
                ]);
            }

            $content = json_decode($response->getBody()->getContents(), true);

            if (isset($content['status']) && $content['status'] == "0") {
                return $this->responseBuilder->error('Error occured while connecting to Wizzy Server.', [
                'response' => $content,
                ]);
            } else {
                return $this->responseBuilder->success('Request executed successfully.', [
                'response' => $content,
                ]);
            }
        } catch (\Exception $e) {
            return $this->responseBuilder->error('Error occured while connecting to Wizzy Server', [
            'exeception' => $e->getMessage(),
            ]);
        }
    }

    private function isMethodSupported(string $method)
    {
        if ($method != "post" && $method != "delete" && $method != "get" && $method != "put") {
            return $this->responseBuilder->error('Requested method is not supported');
        }

        return true;
    }
}
