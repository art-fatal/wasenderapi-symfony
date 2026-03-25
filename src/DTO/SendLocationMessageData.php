<?php

namespace WasenderApi\SymfonyBundle\DTO;

class SendLocationMessageData
{
    /** @var string */
    public $to;

    /** @var float */
    public $latitude;

    /** @var float */
    public $longitude;

    /** @var string|null */
    public $name;

    /** @var string|null */
    public $address;

    /**
     * @param string      $to
     * @param float       $latitude
     * @param float       $longitude
     * @param string|null $name
     * @param string|null $address
     */
    public function __construct($to, $latitude, $longitude, $name = null, $address = null)
    {
        $this->to = $to;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->name = $name;
        $this->address = $address;
    }
}
