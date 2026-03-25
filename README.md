# WasenderAPI Symfony Bundle

Symfony bundle for [WasenderAPI](https://www.wasenderapi.com) – send WhatsApp messages, manage contacts, groups, sessions, and handle webhooks with Symfony best practices.

**Requires PHP >= 7.3** | Compatible with Symfony 4.4, 5.x, 6.x, 7.x

---

## Features

- Send text, image, video, document, audio, sticker, contact and location messages
- Quoted messages, mentions, and presence updates
- Edit and delete messages
- Contact management (info, profile picture, block/unblock, WhatsApp check)
- Group management (create, metadata, participants, settings, invites)
- Session management (CRUD, connect, disconnect, QR code, API key regeneration)
- Media upload (file or base64) and decryption
- Webhook handling with automatic event dispatching
- Rate-limit retry support (HTTP 429)
- Full DTO support for type-safe payloads

---

## Installation

```bash
composer require wasenderapi/wasenderapi-symfony
```

### Register the bundle

If your application does not use Symfony Flex, add the bundle to `config/bundles.php`:

```php
return [
    // ...
    WasenderApi\SymfonyBundle\WasenderApiBundle::class => ['all' => true],
];
```

---

## Configuration

Create `config/packages/wasenderapi.yaml`:

```yaml
wasenderapi:
    api_key: '%env(WASENDERAPI_API_KEY)%'
    personal_access_token: '%env(WASENDERAPI_PERSONAL_ACCESS_TOKEN)%'
    base_url: 'https://www.wasenderapi.com/api'
    webhook:
        secret: '%env(WASENDERAPI_WEBHOOK_SECRET)%'
        path: '/wasender/webhook'
        signature_header: 'x-webhook-signature'
```

Add to your `.env` file:

```dotenv
WASENDERAPI_API_KEY=your_api_key_here
WASENDERAPI_PERSONAL_ACCESS_TOKEN=your_personal_token_here
WASENDERAPI_WEBHOOK_SECRET=your_webhook_secret_here
```

### Import webhook route

Add to `config/routes.yaml`:

```yaml
wasenderapi_webhook:
    resource: '@WasenderApiBundle/Resources/config/routing.xml'
```

---

## Usage

### Dependency injection (recommended)

```php
use WasenderApi\SymfonyBundle\Client\WasenderApiClient;

class WhatsAppController
{
    private $wasender;

    public function __construct(WasenderApiClient $wasender)
    {
        $this->wasender = $wasender;
    }

    public function send()
    {
        $this->wasender->sendText('1234567890', 'Hello from Symfony!');
    }
}
```

### Direct instantiation

Useful for managing multiple sessions with different API keys:

```php
use WasenderApi\SymfonyBundle\Client\WasenderApiClient;

$client = new WasenderApiClient('your_api_key_here');
$client->sendText('1234567890', 'Hello!');
```

---

## Sending Messages

All message methods support both direct parameters and DTOs. All return an associative array.

### Text

```php
use WasenderApi\SymfonyBundle\DTO\SendTextMessageData;

$client->sendText('123', 'Hello!');
// or with DTO
$client->sendText(new SendTextMessageData('123', 'Hello!'));
```

### Image

```php
use WasenderApi\SymfonyBundle\DTO\SendImageMessageData;

$client->sendImage('123', 'https://example.com/image.png', 'Caption');
// or
$client->sendImage(new SendImageMessageData('123', 'https://example.com/image.png', 'Caption'));
```

### Video

```php
$client->sendVideo('123', 'https://example.com/video.mp4', 'Watch this');
```

### Document

```php
$client->sendDocument('123', 'https://example.com/file.pdf', 'My caption', 'file.pdf');
```

### Audio

```php
$client->sendAudio('123', 'https://example.com/audio.mp3');
```

### Sticker

```php
$client->sendSticker('123', 'https://example.com/sticker.webp');
```

### Contact

```php
$client->sendContact('123', 'John Doe', '+1234567890');
```

### Location

```php
$client->sendLocation('123', 48.8566, 2.3522, 'Paris', '1 Rue de Rivoli');
```

### Quoted message

```php
$client->sendQuotedMessage('123', 42, 'Replying to your message');
```

### Mentions

```php
$client->sendMessageWithMentions('group@g.us', 'Hello @user', ['user@s.whatsapp.net']);
```

### Edit / Delete / Info

```php
$client->editMessage(123, 'Updated text');
$client->deleteMessage(123);
$client->getMessageInfo(123);
```

---

## Retry on rate limit

Use `RetryConfig` on send-message methods to automatically retry on HTTP 429:

```php
use WasenderApi\SymfonyBundle\DTO\RetryConfig;

$retry = new RetryConfig(true, 3);
$client->sendText('123', 'Hello!', [], $retry);
```

---

## Contact Management

```php
$client->getContacts();
$client->getContactInfo('1234567890');
$client->getContactProfilePicture('1234567890');
$client->blockContact('1234567890');
$client->unblockContact('1234567890');
$client->checkIfOnWhatsapp('+1234567890');
```

---

## Group Management

```php
$client->createGroup('My Group', ['user1@s.whatsapp.net']);
$client->getGroups();
$client->getGroupMetadata('group-jid');
$client->getGroupParticipants('group-jid');
$client->addGroupParticipants('group-jid', ['user@s.whatsapp.net']);
$client->removeGroupParticipants('group-jid', ['user@s.whatsapp.net']);
$client->updateGroupParticipants('group-jid', 'promote', ['user@s.whatsapp.net']);
$client->updateGroupSettings('group-jid', ['setting' => 'value']);
$client->getGroupInviteInfo('invite-code');
$client->leaveGroup('group-jid');
$client->acceptGroupInvite('invite-code');
$client->getGroupInviteLink('group-jid');
$client->getGroupProfilePicture('group-jid');
```

---

## Session Management

These endpoints require a **personal access token**.

```php
$client->getAllWhatsAppSessions();
$client->createWhatsAppSession(['name' => 'My Session']);
$client->getWhatsAppSessionDetails(1);
$client->updateWhatsAppSession(1, ['name' => 'Renamed']);
$client->deleteWhatsAppSession(1);
$client->connectWhatsAppSession(1);
$client->getWhatsAppSessionQrCode(1);
$client->disconnectWhatsAppSession(1);
$client->regenerateApiKey(1);
$client->getSessionStatus('1');
$client->getSessionUserInfo();
```

---

## Media

```php
// Upload from base64
$client->uploadMediaFile('data:image/png;base64,AAA...', 'image/png');

// Upload from file path
$client->uploadMediaFile('/path/to/image.png');

// Decrypt media
$client->decryptMediaFile($encryptedPayload);
```

---

## Presence

```php
$client->sendPresenceUpdate('user@s.whatsapp.net', 'composing');
$client->sendPresenceUpdate('user@s.whatsapp.net', 'recording', 2000);
```

---

## Webhook Handling

The bundle automatically registers a webhook endpoint. When WasenderAPI sends a webhook, the controller verifies the signature and dispatches a Symfony event.

### Listening for events

Create an event subscriber:

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WasenderApi\SymfonyBundle\Event\MessagesUpserted;
use WasenderApi\SymfonyBundle\Event\MessageReceived;
use WasenderApi\SymfonyBundle\Event\CallReceived;

class WasenderEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            MessagesUpserted::class => 'onMessagesUpserted',
            MessageReceived::class => 'onMessageReceived',
            CallReceived::class => 'onCallReceived',
        ];
    }

    public function onMessagesUpserted(MessagesUpserted $event): void
    {
        $payload = $event->getPayload();
        // Handle upserted messages
    }

    public function onMessageReceived(MessageReceived $event): void
    {
        $payload = $event->getPayload();
        // Handle received message
    }

    public function onCallReceived(CallReceived $event): void
    {
        $payload = $event->getPayload();
        // Handle incoming call
    }
}
```

### Available events

| Webhook type | Event class |
|---|---|
| `chats.upsert` | `ChatsUpserted` |
| `chats.update` | `ChatsUpdated` |
| `chats.delete` | `ChatsDeleted` |
| `contacts.upsert` | `ContactsUpserted` |
| `contacts.update` | `ContactsUpdated` |
| `groups.upsert` | `GroupsUpserted` |
| `groups.update` | `GroupsUpdated` |
| `group-participants.update` | `GroupParticipantsUpdated` |
| `messages.upsert` | `MessagesUpserted` |
| `messages.update` | `MessagesUpdated` |
| `messages.delete` | `MessagesDeleted` |
| `messages.reaction` | `MessagesReaction` |
| `message-receipt.update` | `MessageReceiptUpdated` |
| `message.sent` | `MessageSent` |
| `messages.received` | `MessageReceived` |
| `messages-personal.received` | `PersonalMessageReceived` |
| `messages-group.received` | `GroupMessageReceived` |
| `messages-newsletter.received` | `NewsletterMessageReceived` |
| `session.status` | `SessionStatus` |
| `qrcode.updated` | `QrCodeUpdated` |
| `call.received` | `CallReceived` |
| `poll.results` | `PollResults` |

Unmapped event types dispatch `WasenderWebhookEvent` with `getEventName()` and `getPayload()`.

---

## Error Handling

All API errors throw `WasenderApiException`:

```php
use WasenderApi\SymfonyBundle\Exception\WasenderApiException;

try {
    $client->sendText('123', 'Hello');
} catch (WasenderApiException $e) {
    $e->getMessage();   // Error description
    $e->getCode();      // HTTP status code
    $e->getResponse();  // Parsed JSON response (array or null)
}
```

---

## Testing

```bash
composer install
vendor/bin/phpunit
```

---

## License

MIT
