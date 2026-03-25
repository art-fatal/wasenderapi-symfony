<?php

namespace WasenderApi\SymfonyBundle\Event;

class WasenderWebhookEvent extends AbstractWasenderEvent
{
    /** @var string */
    private $eventName;

    /**
     * @param string $eventName
     * @param array  $payload
     */
    public function __construct($eventName, array $payload)
    {
        parent::__construct($payload);
        $this->eventName = $eventName;
    }

    /**
     * @return string
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }
}
