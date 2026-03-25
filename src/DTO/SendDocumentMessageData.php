<?php

namespace WasenderApi\SymfonyBundle\DTO;

class SendDocumentMessageData
{
    /** @var string */
    public $to;

    /** @var string */
    public $documentUrl;

    /** @var string */
    public $fileName;

    /** @var string|null */
    public $text;

    /**
     * @param string      $to
     * @param string      $documentUrl
     * @param string      $fileName
     * @param string|null $text
     */
    public function __construct($to, $documentUrl, $fileName, $text = null)
    {
        $this->to = $to;
        $this->documentUrl = $documentUrl;
        $this->fileName = $fileName;
        $this->text = $text;
    }
}
