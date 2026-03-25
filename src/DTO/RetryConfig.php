<?php

namespace WasenderApi\SymfonyBundle\DTO;

class RetryConfig
{
    /** @var bool */
    public $enabled;

    /** @var int */
    public $maxRetries;

    /**
     * @param bool $enabled
     * @param int  $maxRetries
     */
    public function __construct($enabled = false, $maxRetries = 0)
    {
        $this->enabled = $enabled;
        $this->maxRetries = $maxRetries;
    }
}
