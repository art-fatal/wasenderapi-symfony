<?php

namespace WasenderApi\SymfonyBundle\DTO;

class SendContactMessageData
{
    /** @var string */
    public $to;

    /** @var string */
    public $contactName;

    /** @var string */
    public $contactPhone;

    /**
     * @param string $to
     * @param string $contactName
     * @param string $contactPhone
     */
    public function __construct($to, $contactName, $contactPhone)
    {
        $this->to = $to;
        $this->contactName = $contactName;
        $this->contactPhone = $contactPhone;
    }
}
