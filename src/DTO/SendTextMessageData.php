<?php

namespace WasenderApi\SymfonyBundle\DTO;

class SendTextMessageData
{
    /** @var string */
    public $to;

    /** @var string */
    public $text;

    /**
     * @param string $to
     * @param string $text
     */
    public function __construct($to, $text)
    {
        $this->to = $to;
        $this->text = $text;
    }
}
