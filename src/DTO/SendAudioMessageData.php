<?php

namespace WasenderApi\SymfonyBundle\DTO;

class SendAudioMessageData
{
    /** @var string */
    public $to;

    /** @var string */
    public $audioUrl;

    /**
     * @param string $to
     * @param string $audioUrl
     */
    public function __construct($to, $audioUrl)
    {
        $this->to = $to;
        $this->audioUrl = $audioUrl;
    }
}
