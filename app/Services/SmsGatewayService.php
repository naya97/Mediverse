<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SmsGatewayService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = "fbQo4VTVTD2BdIwrMu1P6a:APA91bHZkLGFCxlV8c6XGvRnHzM_FJcedshhZKlEuIBe4AWPnp-eMHs7SYpmL5P3fIIyvOky6KWFyNE_ez9zjfGi3iFLi76ZMFs-dpEONWKLza-gfUIRWIA";
        $this->apiUrl = "https://www.traccar.org/sms/";
    }

    public function sendSMS($phone, $otp, $message)
    {
        $requestBody = [
            'message' => sprintf('%s %d', $message, $otp),
            'to' => $phone,
        ];

        try {
            $response = $this->client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $requestBody,
            ]);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            throw new \Exception('Failed to send SMS: ' . $e->getMessage());
        }
    }
}
