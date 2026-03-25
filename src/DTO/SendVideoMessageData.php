<?php

namespace WasenderApi\SymfonyBundle\DTO;

class SendVideoMessageData
{
    /** @var string */
    public $to;

    /** @var string */
    public $videoUrl;

    /** @var string|null */
    public $text;

    /**
     * @param string      $to
     * @param string      $videoUrl
     * @param string|null $text
     */
    public function __construct($to, $videoUrl, $text = null)
    {
        $this->to = $to;
        $this->videoUrl = $videoUrl;
        $this->text = $text;
    }
}
