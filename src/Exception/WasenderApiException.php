<?php

namespace WasenderApi\SymfonyBundle\Exception;

use RuntimeException;

class WasenderApiException extends RuntimeException
{
    /** @var array|null */
    private $response;

    /**
     * @param string     $message
     * @param int        $code
     * @param array|null $response
     */
    public function __construct($message = '', $code = 0, $response = null)
    {
        parent::__construct($message, $code);
        $this->response = $response;
    }

    /**
     * @return array|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
