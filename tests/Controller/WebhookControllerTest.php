<?php

namespace WasenderApi\SymfonyBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use WasenderApi\SymfonyBundle\Controller\WebhookController;
use WasenderApi\SymfonyBundle\Event\CallReceived;
use WasenderApi\SymfonyBundle\Event\MessagesUpserted;
use WasenderApi\SymfonyBundle\Event\WasenderWebhookEvent;

class WebhookControllerTest extends TestCase
{
    /** @var EventDispatcher */
    private $dispatcher;

    /** @var WebhookController */
    private $controller;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
        $this->controller = new WebhookController($this->dispatcher, 'testsecret');
    }

    private function createRequest(array $payload, $signature = 'testsecret'): Request
    {
        $request = Request::create('/wasender/webhook', 'POST', [], [], [], [], json_encode($payload));
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('x-webhook-signature', $signature);

        return $request;
    }

    public function testWebhookDispatchesMessagesUpsertedEvent(): void
    {
        $dispatched = null;
        $this->dispatcher->addListener(MessagesUpserted::class, function (MessagesUpserted $event) use (&$dispatched) {
            $dispatched = $event;
        });

        $payload = ['event' => 'messages.upsert', 'data' => ['foo' => 'bar']];
        $response = $this->controller->handle($this->createRequest($payload));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($dispatched);
        $this->assertSame($payload, $dispatched->getPayload());
    }

    public function testWebhookDispatchesCallReceivedEvent(): void
    {
        $dispatched = null;
        $this->dispatcher->addListener(CallReceived::class, function (CallReceived $event) use (&$dispatched) {
            $dispatched = $event;
        });

        $payload = ['event' => 'call.received', 'data' => ['caller' => '123']];
        $response = $this->controller->handle($this->createRequest($payload));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($dispatched);
    }

    public function testWebhookDispatchesGenericEventForUnmappedType(): void
    {
        $dispatched = null;
        $this->dispatcher->addListener(WasenderWebhookEvent::class, function (WasenderWebhookEvent $event) use (&$dispatched) {
            $dispatched = $event;
        });

        $payload = ['event' => 'unknown.event', 'data' => ['foo' => 'bar']];
        $response = $this->controller->handle($this->createRequest($payload));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($dispatched);
        $this->assertSame('unknown.event', $dispatched->getEventName());
    }

    public function testWebhookRejectsInvalidSignature(): void
    {
        $payload = ['event' => 'messages.upsert', 'data' => []];
        $response = $this->controller->handle($this->createRequest($payload, 'wrongsecret'));

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid signature', $response->getContent());
    }

    public function testWebhookRejectsMissingSignature(): void
    {
        $request = Request::create('/wasender/webhook', 'POST', [], [], [], [], json_encode(['event' => 'test']));
        $request->headers->set('Content-Type', 'application/json');

        $response = $this->controller->handle($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testWebhookRejectsInvalidPayload(): void
    {
        $response = $this->controller->handle($this->createRequest(['data' => 'no event key']));

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid payload', $response->getContent());
    }
}
