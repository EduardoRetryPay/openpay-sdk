<?php

namespace Hotelpay\OpenpaySdk\Src;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Hotelpay\OpenpaySdk\Src\Exceptions\OpenpayConnectionException;

final class OpenpayClient
{
    private $merchantId;
    private $privateKey;
    private $baseUrl;

    public function __construct($merchantId, $privateKey, $sandboxMode)
    {
        $this->merchantId = $merchantId;
        $this->privateKey = $privateKey;
        $this->baseUrl  = $this->getUrl($sandboxMode);
    }

    /**
     * @throws GuzzleException
     * @throws OpenpayConnectionException
     */
    public function cardCharge(
        $amount,
        $description,
        $sourceId,
        $deviceSessionId,
        $currency,
        $orderId,
        $msi,
        $cusName,
        $cusLastname,
        $cusPhone,
        $cusEmail
    )
    {
        $url = "{$this->baseUrl}/charges";
        $client = new Client();

        $headers = $this->getHeaders();

        $payload = [
            'method' => 'card',
            'source_id' => $sourceId,
            'amount' => $amount,
            'description' => $description,
            'currency' => $currency,
            'order_id' => $orderId,
            'device_session_id' => $deviceSessionId,
            'customer' => [
                'name' => $cusName,
                'last_name' => $cusLastname,
                'phone_number' => $cusPhone,
                'email' => $cusEmail
            ]
        ];

        if (is_int($msi)) {
            $payload['payments'] = $msi;
        }
        try {
            $response = $client->post(
                $url,
                [
                'headers' => $headers,
                'json' => $payload,
                ]
            );

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new OpenpayConnectionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws GuzzleException
     * @throws OpenpayConnectionException
     */
    public function storeCharge(
        $amount,
        $description,
        $ttlCash,
        $orderId,
        $cusName,
        $cusLastname,
        $cusPhone,
        $cusEmail
    ){
        $url = "{$this->baseUrl}/charges";
        $client = new Client();

        $headers = $this->getHeaders();

        $dueDate = date('Y-m-d', strtotime(date('Y-m-d') . " + $ttlCash days"));

        $payload = [
            'method' => 'store',
            'amount' => $amount,
            'description' => $description,
            'order_id' => $orderId,
            'due_date' => $dueDate,
            'customer' => [
                'name' => $cusName,
                'last_name' => $cusLastname,
                'phone_number' => $cusPhone,
                'email' => $cusEmail
            ]
        ];

        try {
            $response = $client->post(
                $url,
                [
                'headers' => $headers,
                'json' => $payload,
                ]
            );

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new OpenpayConnectionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getHeaders()
    {
        return [
            'Authorization' => 'Basic ' . base64_encode("{$this->merchantId}:{$this->privateKey}"),
            'Content-Type' => 'application/json',
        ];
    }

    public function getUrl($sandbox = true)
    {
        return $sandbox ? 'https://sandbox-api.openpay.mx/v1' : 'https://api.openpay.mx/v1';
    }
}
