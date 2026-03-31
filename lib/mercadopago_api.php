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

    private function apiRequest($url, array $params = [], $type = "POST", array $extraHeaders = [])
    {
        // Send request
	$ch = curl_init();

	$headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->access_token
	];

	$headers = array_merge($headers, $extraHeaders);

	$options = [
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => true,
            CURLOPT_HTTPHEADER => $headers,
	];

        // Build request based on HTTP method
	if ($type == "GET") {
            $options[CURLOPT_CUSTOMREQUEST] = "GET";
            if (!empty($params)) {
		$url .= "?" . http_build_query($params);
            }
	} elseif ($type == "POST") {
            $options[CURLOPT_CUSTOMREQUEST] = "POST";
            $options[CURLOPT_POST] = true;
            if (!empty($params)) {
		$options[CURLOPT_POSTFIELDS] = json_encode($params);
            }
	}

        // Execute request
	$options[CURLOPT_URL] = $url;
	curl_setopt_array($ch, $options);

	$response = curl_exec($ch);

	if (curl_errno($ch)) {
            $data = new stdClass();
            $data->message = curl_error($ch);
	} else {
            $data = json_decode($response);
            if ($data === null) {
		$data = new stdClass();
            }
	}

        curl_close($ch);

        return new MercadopagoResponse($data);
    }

    /**
     * Build the payment request.
     *
     * @param  array $params An array containing information to generate the payment link
     * @return MercadopagoResponse An object containing the api response
     */
    public function buildPayment($params,$idempotencyKey)
    {
	$extraHeaders = ['X-Idempotency-Key: ' . $idempotencyKey];
        return $this->apiRequest(
            "https://api.mercadopago.com/checkout/preferences",
            $params,
            "POST",
	    $extraHeaders
        );
    }

    /**
     * Validate this payment.
     *
     * @param  string $reference The unique reference code for this payment
     * @return MercadopagoResponse An object containing the api response
     */
    public function checkPayment($reference)
    {
        return $this->apiRequest(
            "https://api.mercadopago.com/v1/payments/" . $reference,
            [],
            "GET"
        );
    }

    /**
     * Refund this payment.
     *
     * @param  string $reference The unique reference code for this payment
     * @return MercadopagoResponse An object containing the api response
     */
    public function refundPayment($reference, $params, $idempotencyKey)
    {
	$extraHeaders = ['X-Idempotency-Key: ' . $idempotencyKey];
        return $this->apiRequest(
            "https://api.mercadopago.com/v1/payments/.$reference./refunds",
            $params,
            "POST",
	    $extraHeaders
        );
    }
}
