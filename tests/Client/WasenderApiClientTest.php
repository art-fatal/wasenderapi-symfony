<?php

namespace WasenderApi\SymfonyBundle\Tests\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WasenderApi\SymfonyBundle\Client\WasenderApiClient;
use WasenderApi\SymfonyBundle\DTO\RetryConfig;
use WasenderApi\SymfonyBundle\DTO\SendAudioMessageData;
use WasenderApi\SymfonyBundle\DTO\SendContactMessageData;
use WasenderApi\SymfonyBundle\DTO\SendDocumentMessageData;
use WasenderApi\SymfonyBundle\DTO\SendImageMessageData;
use WasenderApi\SymfonyBundle\DTO\SendLocationMessageData;
use WasenderApi\SymfonyBundle\DTO\SendStickerMessageData;
use WasenderApi\SymfonyBundle\DTO\SendTextMessageData;
use WasenderApi\SymfonyBundle\DTO\SendVideoMessageData;
use WasenderApi\SymfonyBundle\Exception\WasenderApiException;

class WasenderApiClientTest extends TestCase
{
    private function createClient(array $responses): WasenderApiClient
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        $http = new Client(['handler' => $handler]);

        return new WasenderApiClient('testkey', 'https://www.wasenderapi.com/api', 'testpat', $http);
    }

    private function jsonResponse(array $data, $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($data));
    }

    // ---------------------------------------------------------------
    // Messages
    // ---------------------------------------------------------------

    public function testSendTextReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Message sent']),
        ]);

        $result = $client->sendText('123', 'hello');

        $this->assertTrue($result['success']);
        $this->assertSame('Message sent', $result['message']);
    }

    public function testSendTextWithDtoReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Message sent']),
        ]);

        $result = $client->sendText(new SendTextMessageData('123', 'hello'));

        $this->assertTrue($result['success']);
    }

    public function testSendTextThrowsOnError(): void
    {
        $this->expectException(WasenderApiException::class);

        $client = $this->createClient([
            $this->jsonResponse(['error' => 'fail'], 400),
        ]);

        $client->sendText('123', 'fail');
    }

    public function testSendImageReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Image sent']),
        ]);

        $result = $client->sendImage(new SendImageMessageData('123', 'https://img', 'caption'));

        $this->assertTrue($result['success']);
        $this->assertSame('Image sent', $result['message']);
    }

    public function testSendVideoReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Video sent']),
        ]);

        $result = $client->sendVideo(new SendVideoMessageData('123', 'https://vid', 'caption'));

        $this->assertTrue($result['success']);
    }

    public function testSendDocumentReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Document sent']),
        ]);

        $result = $client->sendDocument(new SendDocumentMessageData('123', 'https://doc', 'file.pdf', 'caption'));

        $this->assertTrue($result['success']);
    }

    public function testSendAudioReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Audio sent']),
        ]);

        $result = $client->sendAudio(new SendAudioMessageData('123', 'https://audio'));

        $this->assertTrue($result['success']);
    }

    public function testSendStickerReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Sticker sent']),
        ]);

        $result = $client->sendSticker(new SendStickerMessageData('123', 'https://sticker'));

        $this->assertTrue($result['success']);
    }

    public function testSendContactReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Contact sent']),
        ]);

        $result = $client->sendContact(new SendContactMessageData('123', 'John Doe', '+123456789'));

        $this->assertTrue($result['success']);
    }

    public function testSendLocationReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Location sent']),
        ]);

        $result = $client->sendLocation(new SendLocationMessageData('123', 1.23, 4.56, 'Place', 'Address'));

        $this->assertTrue($result['success']);
    }

    public function testSendMessageWithMentionsReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Mention sent']),
        ]);

        $result = $client->sendMessageWithMentions('123@g.us', 'Hello @user', ['123@s.whatsapp.net']);

        $this->assertTrue($result['success']);
    }

    public function testSendQuotedMessageReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Quoted sent']),
        ]);

        $result = $client->sendQuotedMessage('123', 42, 'Hello');

        $this->assertTrue($result['success']);
    }

    public function testSendQuotedMessageThrowsOnInvalidReplyId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createClient([]);
        $client->sendQuotedMessage('123', 0, 'Hello');
    }

    public function testEditMessageReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Edited']),
        ]);

        $result = $client->editMessage(123, 'Updated text');

        $this->assertTrue($result['success']);
    }

    public function testEditMessageThrowsOnEmptyText(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createClient([]);
        $client->editMessage(123, '');
    }

    public function testDeleteMessageReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Deleted']),
        ]);

        $result = $client->deleteMessage(123);

        $this->assertTrue($result['success']);
    }

    public function testDeleteMessageThrowsOnInvalidId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createClient([]);
        $client->deleteMessage(0);
    }

    public function testGetMessageInfoReturnsInfo(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'info' => []]),
        ]);

        $result = $client->getMessageInfo(123);

        $this->assertTrue($result['success']);
    }

    public function testGetMessageInfoThrowsOnInvalidId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createClient([]);
        $client->getMessageInfo(0);
    }

    // ---------------------------------------------------------------
    // Contacts
    // ---------------------------------------------------------------

    public function testGetContactsReturnsContacts(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'contacts' => []]),
        ]);

        $result = $client->getContacts();

        $this->assertTrue($result['success']);
        $this->assertIsArray($result['contacts']);
    }

    public function testGetContactInfoReturnsInfo(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'info' => []]),
        ]);

        $result = $client->getContactInfo('123');

        $this->assertTrue($result['success']);
    }

    public function testGetContactProfilePictureReturnsUrl(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'url' => 'https://img']),
        ]);

        $result = $client->getContactProfilePicture('123');

        $this->assertSame('https://img', $result['url']);
    }

    public function testBlockContactReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Blocked']),
        ]);

        $result = $client->blockContact('123');

        $this->assertSame('Blocked', $result['message']);
    }

    public function testUnblockContactReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Unblocked']),
        ]);

        $result = $client->unblockContact('123');

        $this->assertSame('Unblocked', $result['message']);
    }

    public function testCheckIfOnWhatsappReturnsStatus(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'exists' => true]),
        ]);

        $result = $client->checkIfOnWhatsapp('+1234567890');

        $this->assertTrue($result['exists']);
    }

    // ---------------------------------------------------------------
    // Groups
    // ---------------------------------------------------------------

    public function testCreateGroupReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'id' => 'abc@g.us']),
        ]);

        $result = $client->createGroup('My Group', ['u1@s.whatsapp.net']);

        $this->assertSame('abc@g.us', $result['id']);
    }

    public function testCreateGroupThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createClient([]);
        $client->createGroup('');
    }

    public function testGetGroupsReturnsGroups(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'groups' => []]),
        ]);

        $result = $client->getGroups();

        $this->assertIsArray($result['groups']);
    }

    public function testGetGroupMetadataReturnsMetadata(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'metadata' => []]),
        ]);

        $result = $client->getGroupMetadata('abc');

        $this->assertIsArray($result['metadata']);
    }

    public function testGetGroupParticipantsReturnsParticipants(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'participants' => []]),
        ]);

        $result = $client->getGroupParticipants('abc');

        $this->assertIsArray($result['participants']);
    }

    public function testAddGroupParticipantsReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Added']),
        ]);

        $result = $client->addGroupParticipants('abc', ['u1', 'u2']);

        $this->assertSame('Added', $result['message']);
    }

    public function testRemoveGroupParticipantsReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Removed']),
        ]);

        $result = $client->removeGroupParticipants('abc', ['u1', 'u2']);

        $this->assertSame('Removed', $result['message']);
    }

    public function testUpdateGroupParticipantsReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Updated']),
        ]);

        $result = $client->updateGroupParticipants('abc', 'promote', ['u1']);

        $this->assertSame('Updated', $result['message']);
    }

    public function testUpdateGroupParticipantsThrowsOnInvalidAction(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createClient([]);
        $client->updateGroupParticipants('abc', 'invalid', ['u1']);
    }

    public function testUpdateGroupSettingsReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Updated']),
        ]);

        $result = $client->updateGroupSettings('abc', ['setting' => 'value']);

        $this->assertSame('Updated', $result['message']);
    }

    public function testGetGroupInviteInfoReturnsInfo(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'info' => []]),
        ]);

        $result = $client->getGroupInviteInfo('ABC123');

        $this->assertIsArray($result['info']);
    }

    public function testLeaveGroupReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Left']),
        ]);

        $result = $client->leaveGroup('abc');

        $this->assertSame('Left', $result['message']);
    }

    public function testAcceptGroupInviteReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Accepted']),
        ]);

        $result = $client->acceptGroupInvite('CODE123');

        $this->assertSame('Accepted', $result['message']);
    }

    public function testGetGroupInviteLinkReturnsLink(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'link' => 'https://chat.whatsapp.com/xyz']),
        ]);

        $result = $client->getGroupInviteLink('abc');

        $this->assertSame('https://chat.whatsapp.com/xyz', $result['link']);
    }

    public function testGetGroupProfilePictureReturnsUrl(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'url' => 'https://img']),
        ]);

        $result = $client->getGroupProfilePicture('abc');

        $this->assertSame('https://img', $result['url']);
    }

    // ---------------------------------------------------------------
    // Media
    // ---------------------------------------------------------------

    public function testUploadMediaFileFromBase64ReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'id' => 'media123']),
        ]);

        $result = $client->uploadMediaFile('data:image/png;base64,AAA', 'image/png');

        $this->assertSame('media123', $result['id']);
    }

    public function testDecryptMediaFileReturnsPayload(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'data' => ['foo' => 'bar']]),
        ]);

        $result = $client->decryptMediaFile(['message' => ['imageMessage' => []]]);

        $this->assertSame(['foo' => 'bar'], $result['data']);
    }

    public function testDecryptMediaFileThrowsOnEmptyPayload(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createClient([]);
        $client->decryptMediaFile([]);
    }

    // ---------------------------------------------------------------
    // Presence
    // ---------------------------------------------------------------

    public function testSendPresenceUpdateReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Presence sent']),
        ]);

        $result = $client->sendPresenceUpdate('123@s.whatsapp.net', 'composing', 1000);

        $this->assertSame('Presence sent', $result['message']);
    }

    public function testSendPresenceUpdateThrowsOnInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createClient([]);
        $client->sendPresenceUpdate('123@s.whatsapp.net', 'invalid');
    }

    // ---------------------------------------------------------------
    // Sessions
    // ---------------------------------------------------------------

    public function testGetAllWhatsAppSessionsReturnsSessions(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'sessions' => []]),
        ]);

        $result = $client->getAllWhatsAppSessions();

        $this->assertIsArray($result['sessions']);
    }

    public function testCreateWhatsAppSessionReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Created']),
        ]);

        $result = $client->createWhatsAppSession(['name' => 'test']);

        $this->assertSame('Created', $result['message']);
    }

    public function testGetWhatsAppSessionDetailsReturnsDetails(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'details' => []]),
        ]);

        $result = $client->getWhatsAppSessionDetails(1);

        $this->assertIsArray($result['details']);
    }

    public function testUpdateWhatsAppSessionReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Updated']),
        ]);

        $result = $client->updateWhatsAppSession(1, ['name' => 'new']);

        $this->assertSame('Updated', $result['message']);
    }

    public function testDeleteWhatsAppSessionReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Deleted']),
        ]);

        $result = $client->deleteWhatsAppSession(1);

        $this->assertSame('Deleted', $result['message']);
    }

    public function testConnectWhatsAppSessionReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Connected']),
        ]);

        $result = $client->connectWhatsAppSession(1);

        $this->assertSame('Connected', $result['message']);
    }

    public function testGetWhatsAppSessionQrCodeReturnsQr(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'qr' => 'qrcode']),
        ]);

        $result = $client->getWhatsAppSessionQrCode(1);

        $this->assertSame('qrcode', $result['qr']);
    }

    public function testDisconnectWhatsAppSessionReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Disconnected']),
        ]);

        $result = $client->disconnectWhatsAppSession(1);

        $this->assertSame('Disconnected', $result['message']);
    }

    public function testRegenerateApiKeyReturnsSuccess(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'message' => 'Regenerated']),
        ]);

        $result = $client->regenerateApiKey(1);

        $this->assertSame('Regenerated', $result['message']);
    }

    public function testGetSessionStatusReturnsStatus(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'status' => 'CONNECTED']),
        ]);

        $result = $client->getSessionStatus('1');

        $this->assertSame('CONNECTED', $result['status']);
    }

    public function testGetSessionUserInfoReturnsInfo(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['success' => true, 'user' => ['id' => 1]]),
        ]);

        $result = $client->getSessionUserInfo();

        $this->assertSame(['id' => 1], $result['user']);
    }

    // ---------------------------------------------------------------
    // Retry
    // ---------------------------------------------------------------

    public function testRetryOnRateLimitSucceeds(): void
    {
        $client = $this->createClient([
            $this->jsonResponse(['error' => 'rate limited', 'retry_after' => 0], 429),
            $this->jsonResponse(['success' => true, 'message' => 'Sent']),
        ]);

        $retry = new RetryConfig(true, 1);
        $result = $client->sendText('123', 'hello', [], $retry);

        $this->assertTrue($result['success']);
    }

    public function testPersonalAccessTokenRequired(): void
    {
        $this->expectException(WasenderApiException::class);
        $this->expectExceptionMessage('personal access token');

        $mock = new MockHandler([]);
        $handler = HandlerStack::create($mock);
        $http = new Client(['handler' => $handler]);

        $client = new WasenderApiClient('testkey', 'https://www.wasenderapi.com/api', '', $http);
        $client->getAllWhatsAppSessions();
    }
}
