<?php

namespace WasenderApi\SymfonyBundle\DTO;

class SendImageMessageData
{
    /** @var string */
    public $to;

    /** @var string */
    public $imageUrl;

    /** @var string|null */
    public $text;

    /**
     * @param string      $to
     * @param string      $imageUrl
     * @param string|null $text
     */
    public function __construct($to, $imageUrl, $text = null)
    {
        $this->to = $to;
        $this->imageUrl = $imageUrl;
        $this->text = $text;
    }
}
