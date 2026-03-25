<?php

namespace WasenderApi\SymfonyBundle\DTO;

class SendStickerMessageData
{
    /** @var string */
    public $to;

    /** @var string */
    public $stickerUrl;

    /**
     * @param string $to
     * @param string $stickerUrl
     */
    public function __construct($to, $stickerUrl)
    {
        $this->to = $to;
        $this->stickerUrl = $stickerUrl;
    }
}
