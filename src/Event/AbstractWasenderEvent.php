<?php

namespace WasenderApi\SymfonyBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AbstractWasenderEvent extends Event
{
    /** @var array */
    private $payload;

    /**
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
