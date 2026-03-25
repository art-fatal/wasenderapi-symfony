<?php

namespace WasenderApi\SymfonyBundle\Controller;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WasenderApi\SymfonyBundle\Event\CallReceived;
use WasenderApi\SymfonyBundle\Event\ChatsDeleted;
use WasenderApi\SymfonyBundle\Event\ChatsUpdated;
use WasenderApi\SymfonyBundle\Event\ChatsUpserted;
use WasenderApi\SymfonyBundle\Event\ContactsUpdated;
use WasenderApi\SymfonyBundle\Event\ContactsUpserted;
use WasenderApi\SymfonyBundle\Event\GroupMessageReceived;
use WasenderApi\SymfonyBundle\Event\GroupParticipantsUpdated;
use WasenderApi\SymfonyBundle\Event\GroupsUpdated;
use WasenderApi\SymfonyBundle\Event\GroupsUpserted;
use WasenderApi\SymfonyBundle\Event\MessageReceiptUpdated;
use WasenderApi\SymfonyBundle\Event\MessageReceived;
use WasenderApi\SymfonyBundle\Event\MessageSent;
use WasenderApi\SymfonyBundle\Event\MessagesDeleted;
use WasenderApi\SymfonyBundle\Event\MessagesReaction;
use WasenderApi\SymfonyBundle\Event\MessagesUpdated;
use WasenderApi\SymfonyBundle\Event\MessagesUpserted;
use WasenderApi\SymfonyBundle\Event\NewsletterMessageReceived;
use WasenderApi\SymfonyBundle\Event\PersonalMessageReceived;
use WasenderApi\SymfonyBundle\Event\PollResults;
use WasenderApi\SymfonyBundle\Event\QrCodeUpdated;
use WasenderApi\SymfonyBundle\Event\SessionStatus;
use WasenderApi\SymfonyBundle\Event\WasenderWebhookEvent;

class WebhookController
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var string */
    private $secret;

    /** @var string */
    private $signatureHeader;

    private static $eventMap = [
        'chats.upsert' => ChatsUpserted::class,
        'chats.update' => ChatsUpdated::class,
        'chats.delete' => ChatsDeleted::class,
        'groups.upsert' => GroupsUpserted::class,
        'groups.update' => GroupsUpdated::class,
        'group-participants.update' => GroupParticipantsUpdated::class,
        'contacts.upsert' => ContactsUpserted::class,
        'contacts.update' => ContactsUpdated::class,
        'messages.upsert' => MessagesUpserted::class,
        'messages.update' => MessagesUpdated::class,
        'messages.delete' => MessagesDeleted::class,
        'messages.reaction' => MessagesReaction::class,
        'message-receipt.update' => MessageReceiptUpdated::class,
        'message.sent' => MessageSent::class,
        'session.status' => SessionStatus::class,
        'qrcode.updated' => QrCodeUpdated::class,
        'call.received' => CallReceived::class,
        'messages-personal.received' => PersonalMessageReceived::class,
        'messages-newsletter.received' => NewsletterMessageReceived::class,
        'messages-group.received' => GroupMessageReceived::class,
        'messages.received' => MessageReceived::class,
        'poll.results' => PollResults::class,
    ];

    /**
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $secret
     * @param string                   $signatureHeader
     */
    public function __construct(EventDispatcherInterface $dispatcher, $secret, $signatureHeader = 'x-webhook-signature')
    {
        $this->dispatcher = $dispatcher;
        $this->secret = $secret;
        $this->signatureHeader = $signatureHeader;
    }

    public function handle(Request $request): Response
    {
        $signature = $request->headers->get($this->signatureHeader);

        if (!$signature || !$this->secret || $signature !== $this->secret) {
            return new Response('Invalid signature', 400);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload) || !isset($payload['event'])) {
            return new Response('Invalid payload', 400);
        }

        $eventType = $payload['event'];

        if (isset(self::$eventMap[$eventType])) {
            $eventClass = self::$eventMap[$eventType];
            $event = new $eventClass($payload);
        } else {
            $event = new WasenderWebhookEvent($eventType, $payload);
        }

        $this->dispatcher->dispatch($event);

        return new Response('OK', 200);
    }
}
