<?php

require_once dirname(__FILE__) .
    DIRECTORY_SEPARATOR .
    "mercadopago_response.php";

/**
 * MercadoPago API.
 */

class MercadopagoApi
{
    /**
     * @var string The Mercadopago API access token
     */
    private $access_token;

    /**
     * Initializes the class.
     *
     * @param string $access_token The Mercadopago API access token
     */
    public function __construct($access_token)
    {
        $this->access_token = $access_token;
    }

    private function apiRequest($url, array $params = [], $type = "POST")
    {
        // Send request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->access_token,
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Build GET request
        if ($type == "GET") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            $url = $url . "?" . http_build_query($params);
        }

        // Build POST request
        if ($type == "POST") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POST, true);

            if (!empty($params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }
        }

        // Execute request
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = new stdClass();
        if (curl_errno($ch)) {
            $data->message = curl_error($ch);
        } else {
            $data = json_decode(curl_exec($ch));
        }
        curl_close($ch);

        return new MercadopagoResponse($data);
    }

    /**
     * Build the payment request.
     *
     * @param  array $params An array containing information to generate the payment link
     * @return stdClass An object containing the api response
     */
    public function buildPayment($params)
    {
        return $this->apiRequest(
            "https://api.mercadopago.com/checkout/preferences",
            $params,
            "POST"
        );
    }

    /**
     * Validate this payment.
     *
     * @param  string $reference The unique reference code for this payment
     * @return stdClass An object containing the api response
     */
    public function checkPayment($reference)
    {
        return $this->apiRequest(
            "https://api.mercadopago.com/v1/payments/" . $reference,
            [],
            "GET"
        );
    }
}
