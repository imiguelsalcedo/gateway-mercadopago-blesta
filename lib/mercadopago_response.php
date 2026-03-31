<?php

class MercadopagoResponse
{
    private $data;
    private $message;
    private $error;
    private $status;

    /**
     * Mercadopago Response constructor.
     *
     * @param stdClass $apiResponse
     */
    public function __construct(stdClass $apiResponse)
    {
	$this->data = $apiResponse;
	$status  = $apiResponse->status ?? false;
        $message = $apiResponse->message ?? '';

	if (is_numeric($status) && $status >= 400) {
            $this->error   = $message;
            $this->message = '';
        } else {
            $this->message = $message;
            $this->error   = '';
        }

        $this->status = $status;
    }

    /**
     * Get the status of this response
     *
     * @return string The status of this response
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * Get the data from this response
     *
     * @return stdClass The data from this response
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Get any errors from this response
     *
     * @return string The errors from this response
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * Get the message returned with this response
     *
     * @return string The message returned with this response
     */
    public function message()
    {
        return $this->message;
    }
}
