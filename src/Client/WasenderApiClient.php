<?php

namespace WasenderApi\SymfonyBundle\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
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

class WasenderApiClient
{
    /** @var string */
    private $apiKey;

    /** @var string */
    private $baseUrl;

    /** @var string */
    private $personalAccessToken;

    /** @var Client */
    private $httpClient;

    /**
     * @param string      $apiKey
     * @param string      $baseUrl
     * @param string      $personalAccessToken
     * @param Client|null $httpClient
     */
    public function __construct(
        $apiKey = '',
        $baseUrl = 'https://www.wasenderapi.com/api',
        $personalAccessToken = '',
        $httpClient = null
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->personalAccessToken = $personalAccessToken;
        $this->httpClient = $httpClient ?: new Client();
    }

    // ---------------------------------------------------------------
    // Messages
    // ---------------------------------------------------------------

    /**
     * @param string|SendTextMessageData $to
     * @param string|null                $text
     * @param array                      $options
     * @param RetryConfig|null           $retryConfig
     * @return array
     */
    public function sendText($to, $text = null, array $options = [], $retryConfig = null): array
    {
        if ($to instanceof SendTextMessageData) {
            $payload = ['to' => $to->to, 'text' => $to->text];
        } else {
            $payload = ['to' => $to, 'text' => $text];
        }

        return $this->postWithRetry('/send-message', array_merge($options, $payload), false, $retryConfig);
    }

    /**
     * @param string           $to
     * @param string           $text
     * @param array            $mentions
     * @param array            $options
     * @param RetryConfig|null $retryConfig
     * @return array
     */
    public function sendMessageWithMentions($to, $text, array $mentions, array $options = [], $retryConfig = null): array
    {
        $payload = array_merge($options, [
            'to' => $to,
            'text' => $text,
            'mentions' => $mentions,
        ]);

        return $this->postWithRetry('/send-message', $payload, false, $retryConfig);
    }

    /**
     * @param string           $to
     * @param int              $replyTo
     * @param string|null      $text
     * @param array            $options
     * @param RetryConfig|null $retryConfig
     * @return array
     */
    public function sendQuotedMessage($to, $replyTo, $text = null, array $options = [], $retryConfig = null): array
    {
        if ($replyTo <= 0) {
            throw new InvalidArgumentException('replyTo must be a positive message identifier.');
        }

        $payload = array_merge($options, ['to' => $to, 'replyTo' => $replyTo]);

        if ($text !== null) {
            $payload['text'] = $text;
        }

        return $this->postWithRetry('/send-message', $payload, false, $retryConfig);
    }

    /**
     * @param string|SendImageMessageData $to
     * @param string|null                 $url
     * @param string|null                 $caption
     * @param array                       $options
     * @param RetryConfig|null            $retryConfig
     * @return array
     */
    public function sendImage($to, $url = null, $caption = null, array $options = [], $retryConfig = null): array
    {
        if ($to instanceof SendImageMessageData) {
            $payload = ['to' => $to->to, 'imageUrl' => $to->imageUrl];
            if ($to->text) {
                $payload['text'] = $to->text;
            }
        } else {
            $payload = ['to' => $to, 'imageUrl' => $url];
            if ($caption) {
                $payload['text'] = $caption;
            }
        }

        return $this->postWithRetry('/send-message', array_merge($options, $payload), false, $retryConfig);
    }

    /**
     * @param string|SendVideoMessageData $to
     * @param string|null                 $url
     * @param string|null                 $caption
     * @param array                       $options
     * @param RetryConfig|null            $retryConfig
     * @return array
     */
    public function sendVideo($to, $url = null, $caption = null, array $options = [], $retryConfig = null): array
    {
        if ($to instanceof SendVideoMessageData) {
            $payload = ['to' => $to->to, 'videoUrl' => $to->videoUrl];
            if ($to->text) {
                $payload['text'] = $to->text;
            }
        } else {
            $payload = ['to' => $to, 'videoUrl' => $url];
            if ($caption) {
                $payload['text'] = $caption;
            }
        }

        return $this->postWithRetry('/send-message', array_merge($options, $payload), false, $retryConfig);
    }

    /**
     * @param string|SendDocumentMessageData $to
     * @param string|null                    $url
     * @param string|null                    $caption
     * @param string|null                    $fileName
     * @param array                          $options
     * @param RetryConfig|null               $retryConfig
     * @return array
     */
    public function sendDocument($to, $url = null, $caption = null, $fileName = null, array $options = [], $retryConfig = null): array
    {
        if ($to instanceof SendDocumentMessageData) {
            $payload = [
                'to' => $to->to,
                'documentUrl' => $to->documentUrl,
                'fileName' => $to->fileName,
            ];
            if ($to->text) {
                $payload['text'] = $to->text;
            }
        } else {
            $payload = [
                'to' => $to,
                'documentUrl' => $url,
                'fileName' => $fileName,
            ];
            if ($caption) {
                $payload['text'] = $caption;
            }
        }

        return $this->postWithRetry('/send-message', array_merge($options, $payload), false, $retryConfig);
    }

    /**
     * @param string|SendAudioMessageData $to
     * @param string|null                 $url
     * @param array                       $options
     * @param RetryConfig|null            $retryConfig
     * @return array
     */
    public function sendAudio($to, $url = null, array $options = [], $retryConfig = null): array
    {
        if ($to instanceof SendAudioMessageData) {
            $payload = ['to' => $to->to, 'audioUrl' => $to->audioUrl];
        } else {
            $payload = ['to' => $to, 'audioUrl' => $url];
        }

        return $this->postWithRetry('/send-message', array_merge($options, $payload), false, $retryConfig);
    }

    /**
     * @param string|SendStickerMessageData $to
     * @param string|null                   $url
     * @param array                         $options
     * @param RetryConfig|null              $retryConfig
     * @return array
     */
    public function sendSticker($to, $url = null, array $options = [], $retryConfig = null): array
    {
        if ($to instanceof SendStickerMessageData) {
            $payload = ['to' => $to->to, 'stickerUrl' => $to->stickerUrl];
        } else {
            $payload = ['to' => $to, 'stickerUrl' => $url];
        }

        return $this->postWithRetry('/send-message', array_merge($options, $payload), false, $retryConfig);
    }

    /**
     * @param string|SendContactMessageData $to
     * @param string|null                   $contactName
     * @param string|null                   $contactPhone
     * @param array                         $options
     * @param RetryConfig|null              $retryConfig
     * @return array
     */
    public function sendContact($to, $contactName = null, $contactPhone = null, array $options = [], $retryConfig = null): array
    {
        if ($to instanceof SendContactMessageData) {
            $payload = [
                'to' => $to->to,
                'contact' => ['name' => $to->contactName, 'phone' => $to->contactPhone],
            ];
        } else {
            $payload = [
                'to' => $to,
                'contact' => ['name' => $contactName, 'phone' => $contactPhone],
            ];
        }

        return $this->postWithRetry('/send-message', array_merge($options, $payload), false, $retryConfig);
    }

    /**
     * @param string|SendLocationMessageData $to
     * @param float|null                     $latitude
     * @param float|null                     $longitude
     * @param string|null                    $name
     * @param string|null                    $address
     * @param array                          $options
     * @param RetryConfig|null               $retryConfig
     * @return array
     */
    public function sendLocation($to, $latitude = null, $longitude = null, $name = null, $address = null, array $options = [], $retryConfig = null): array
    {
        if ($to instanceof SendLocationMessageData) {
            $location = ['latitude' => $to->latitude, 'longitude' => $to->longitude];
            if ($to->name) {
                $location['name'] = $to->name;
            }
            if ($to->address) {
                $location['address'] = $to->address;
            }
            $payload = ['to' => $to->to, 'messageType' => 'location', 'location' => $location];
        } else {
            $location = ['latitude' => $latitude, 'longitude' => $longitude];
            if ($name) {
                $location['name'] = $name;
            }
            if ($address) {
                $location['address'] = $address;
            }
            $payload = ['to' => $to, 'messageType' => 'location', 'location' => $location];
        }

        return $this->postWithRetry('/send-message', array_merge($options, $payload), false, $retryConfig);
    }

    /**
     * @param int    $messageId
     * @param string $text
     * @return array
     */
    public function editMessage($messageId, $text): array
    {
        if ($messageId <= 0) {
            throw new InvalidArgumentException('Message id must be positive.');
        }
        if ($text === '') {
            throw new InvalidArgumentException('Message text cannot be empty.');
        }

        return $this->put("/messages/{$messageId}", ['text' => $text]);
    }

    /**
     * @param int $messageId
     * @return array
     */
    public function deleteMessage($messageId): array
    {
        if ($messageId <= 0) {
            throw new InvalidArgumentException('Message id must be positive.');
        }

        return $this->delete("/messages/{$messageId}");
    }

    /**
     * @param int $messageId
     * @return array
     */
    public function getMessageInfo($messageId): array
    {
        if ($messageId <= 0) {
            throw new InvalidArgumentException('Message id must be positive.');
        }

        return $this->get("/messages/{$messageId}/info");
    }

    // ---------------------------------------------------------------
    // Contacts
    // ---------------------------------------------------------------

    /** @return array */
    public function getContacts(): array
    {
        return $this->get('/contacts');
    }

    /**
     * @param string $phone
     * @return array
     */
    public function getContactInfo($phone): array
    {
        return $this->get("/contacts/{$phone}");
    }

    /**
     * @param string $phone
     * @return array
     */
    public function getContactProfilePicture($phone): array
    {
        return $this->get("/contacts/{$phone}/profile-picture");
    }

    /**
     * @param string $phone
     * @return array
     */
    public function blockContact($phone): array
    {
        return $this->post("/contacts/{$phone}/block");
    }

    /**
     * @param string $phone
     * @return array
     */
    public function unblockContact($phone): array
    {
        return $this->post("/contacts/{$phone}/unblock");
    }

    /**
     * @param string $phoneNumber
     * @return array
     */
    public function checkIfOnWhatsapp($phoneNumber): array
    {
        $encoded = rawurlencode($phoneNumber);
        return $this->get("/on-whatsapp/{$encoded}");
    }

    // ---------------------------------------------------------------
    // Groups
    // ---------------------------------------------------------------

    /**
     * @param string $name
     * @param array  $participants
     * @return array
     */
    public function createGroup($name, array $participants = []): array
    {
        if ($name === '') {
            throw new InvalidArgumentException('Group name cannot be empty.');
        }

        $payload = ['name' => $name];
        if (!empty($participants)) {
            $payload['participants'] = array_values($participants);
        }

        return $this->post('/groups', $payload);
    }

    /** @return array */
    public function getGroups(): array
    {
        return $this->get('/groups');
    }

    /**
     * @param string $jid
     * @return array
     */
    public function getGroupMetadata($jid): array
    {
        return $this->get("/groups/{$jid}/metadata");
    }

    /**
     * @param string $jid
     * @return array
     */
    public function getGroupParticipants($jid): array
    {
        return $this->get("/groups/{$jid}/participants");
    }

    /**
     * @param string $jid
     * @param array  $participants
     * @return array
     */
    public function addGroupParticipants($jid, array $participants): array
    {
        return $this->post("/groups/{$jid}/participants/add", ['participants' => $participants]);
    }

    /**
     * @param string $jid
     * @param array  $participants
     * @return array
     */
    public function removeGroupParticipants($jid, array $participants): array
    {
        return $this->post("/groups/{$jid}/participants/remove", ['participants' => $participants]);
    }

    /**
     * @param string $jid
     * @param string $action   "promote" or "demote"
     * @param array  $participants
     * @return array
     */
    public function updateGroupParticipants($jid, $action, array $participants): array
    {
        $action = strtolower($action);
        if (!in_array($action, ['promote', 'demote'], true)) {
            throw new InvalidArgumentException('Action must be either promote or demote.');
        }

        return $this->put("/groups/{$jid}/participants/update", [
            'action' => $action,
            'participants' => $participants,
        ]);
    }

    /**
     * @param string $jid
     * @param array  $settings
     * @return array
     */
    public function updateGroupSettings($jid, array $settings): array
    {
        return $this->put("/groups/{$jid}/settings", $settings);
    }

    /**
     * @param string $inviteCode
     * @return array
     */
    public function getGroupInviteInfo($inviteCode): array
    {
        return $this->get("/groups/invite/{$inviteCode}");
    }

    /**
     * @param string $jid
     * @return array
     */
    public function leaveGroup($jid): array
    {
        return $this->post("/groups/{$jid}/leave");
    }

    /**
     * @param string $code
     * @return array
     */
    public function acceptGroupInvite($code): array
    {
        return $this->post('/groups/invite/accept', ['code' => $code]);
    }

    /**
     * @param string $jid
     * @return array
     */
    public function getGroupInviteLink($jid): array
    {
        return $this->get("/groups/{$jid}/invite-link");
    }

    /**
     * @param string $jid
     * @return array
     */
    public function getGroupProfilePicture($jid): array
    {
        return $this->get("/groups/{$jid}/picture");
    }

    // ---------------------------------------------------------------
    // Media
    // ---------------------------------------------------------------

    /**
     * @param string      $fileOrBase64
     * @param string|null $mimeType
     * @param array       $options
     * @return array
     */
    public function uploadMediaFile($fileOrBase64, $mimeType = null, array $options = []): array
    {
        $url = $this->baseUrl . '/upload';
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'User-Agent' => 'wasenderapi-symfony-sdk',
        ];

        try {
            if (is_file($fileOrBase64)) {
                $filename = isset($options['filename']) ? $options['filename'] : basename($fileOrBase64);
                $multipart = [
                    [
                        'name' => 'file',
                        'contents' => fopen($fileOrBase64, 'rb'),
                        'filename' => $filename,
                    ],
                ];

                foreach ($options as $key => $value) {
                    if ($key === 'filename') {
                        continue;
                    }
                    $multipart[] = ['name' => $key, 'contents' => (string) $value];
                }

                $response = $this->httpClient->request('POST', $url, [
                    'headers' => $headers,
                    'multipart' => $multipart,
                ]);
            } else {
                $payload = array_merge($options, ['base64' => $fileOrBase64]);
                if ($mimeType) {
                    $payload['mimetype'] = $mimeType;
                }

                $headers['Accept'] = 'application/json';

                $response = $this->httpClient->request('POST', $url, [
                    'headers' => $headers,
                    'json' => $payload,
                ]);
            }
        } catch (RequestException $e) {
            $body = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();
            $code = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            throw new WasenderApiException('Wasender API error: ' . $body, $code, $this->decodeBody($body));
        }

        return $this->decodeResponse($response);
    }

    /**
     * @param array $data
     * @return array
     */
    public function decryptMediaFile(array $data): array
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Decrypt media payload cannot be empty.');
        }

        return $this->post('/decrypt-media', ['data' => $data]);
    }

    // ---------------------------------------------------------------
    // Presence
    // ---------------------------------------------------------------

    /**
     * @param string   $jid
     * @param string   $type  composing|recording|available|unavailable
     * @param int|null $delayMs
     * @param array    $options
     * @return array
     */
    public function sendPresenceUpdate($jid, $type, $delayMs = null, array $options = []): array
    {
        $allowed = ['composing', 'recording', 'available', 'unavailable'];
        $type = strtolower($type);

        if (!in_array($type, $allowed, true)) {
            throw new InvalidArgumentException('Presence type must be one of: ' . implode(', ', $allowed));
        }

        $payload = array_merge($options, ['jid' => $jid, 'type' => $type]);

        if ($delayMs !== null) {
            $payload['delayMs'] = $delayMs;
        }

        return $this->post('/send-presence-update', $payload);
    }

    // ---------------------------------------------------------------
    // Sessions
    // ---------------------------------------------------------------

    /** @return array */
    public function getSessionUserInfo(): array
    {
        return $this->get('/user', false);
    }

    /** @return array */
    public function getAllWhatsAppSessions(): array
    {
        return $this->get('/whatsapp-sessions', true);
    }

    /**
     * @param array $payload
     * @return array
     */
    public function createWhatsAppSession(array $payload): array
    {
        return $this->post('/whatsapp-sessions', $payload, true);
    }

    /**
     * @param int $sessionId
     * @return array
     */
    public function getWhatsAppSessionDetails($sessionId): array
    {
        return $this->get("/whatsapp-sessions/{$sessionId}", true);
    }

    /**
     * @param int   $sessionId
     * @param array $payload
     * @return array
     */
    public function updateWhatsAppSession($sessionId, array $payload): array
    {
        return $this->put("/whatsapp-sessions/{$sessionId}", $payload, true);
    }

    /**
     * @param int $sessionId
     * @return array
     */
    public function deleteWhatsAppSession($sessionId): array
    {
        return $this->delete("/whatsapp-sessions/{$sessionId}", true);
    }

    /**
     * @param int  $sessionId
     * @param bool $qrAsImage
     * @return array
     */
    public function connectWhatsAppSession($sessionId, $qrAsImage = false): array
    {
        $query = $qrAsImage ? '?qrAsImage=true' : '';
        return $this->post("/whatsapp-sessions/{$sessionId}/connect{$query}", null, true);
    }

    /**
     * @param int $sessionId
     * @return array
     */
    public function getWhatsAppSessionQrCode($sessionId): array
    {
        return $this->get("/whatsapp-sessions/{$sessionId}/qr-code", true);
    }

    /**
     * @param int $sessionId
     * @return array
     */
    public function disconnectWhatsAppSession($sessionId): array
    {
        return $this->post("/whatsapp-sessions/{$sessionId}/disconnect", null, true);
    }

    /**
     * @param int $sessionId
     * @return array
     */
    public function regenerateApiKey($sessionId): array
    {
        return $this->post("/whatsapp-sessions/{$sessionId}/regenerate-api-key", null, true);
    }

    /**
     * @param string $sessionId
     * @return array
     */
    public function getSessionStatus($sessionId): array
    {
        return $this->get("/sessions/{$sessionId}/status", false);
    }

    // ---------------------------------------------------------------
    // HTTP helpers
    // ---------------------------------------------------------------

    /**
     * @param string $path
     * @param bool   $usePersonalToken
     * @return array
     */
    protected function get($path, $usePersonalToken = false): array
    {
        return $this->request('GET', $path, null, $usePersonalToken);
    }

    /**
     * @param string     $path
     * @param array|null $payload
     * @param bool       $usePersonalToken
     * @return array
     */
    protected function post($path, $payload = null, $usePersonalToken = false): array
    {
        return $this->request('POST', $path, $payload, $usePersonalToken);
    }

    /**
     * @param string $path
     * @param array  $payload
     * @param bool   $usePersonalToken
     * @return array
     */
    protected function put($path, array $payload, $usePersonalToken = false): array
    {
        return $this->request('PUT', $path, $payload, $usePersonalToken);
    }

    /**
     * @param string $path
     * @param bool   $usePersonalToken
     * @return array
     */
    protected function delete($path, $usePersonalToken = false): array
    {
        return $this->request('DELETE', $path, null, $usePersonalToken);
    }

    /**
     * @param string     $method
     * @param string     $path
     * @param array|null $payload
     * @param bool       $usePersonalToken
     * @return array
     */
    protected function request($method, $path, $payload = null, $usePersonalToken = false): array
    {
        if ($usePersonalToken && empty($this->personalAccessToken)) {
            throw new WasenderApiException('This endpoint requires a personal access token.');
        }

        $url = $this->baseUrl . $path;
        $token = ($usePersonalToken && $this->personalAccessToken)
            ? $this->personalAccessToken
            : $this->apiKey;

        $requestOptions = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                'User-Agent' => 'wasenderapi-symfony-sdk',
            ],
        ];

        if ($payload !== null) {
            $requestOptions['json'] = $payload;
        }

        try {
            $response = $this->httpClient->request($method, $url, $requestOptions);
        } catch (RequestException $e) {
            $body = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();
            $code = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            throw new WasenderApiException('Wasender API error: ' . $body, $code, $this->decodeBody($body));
        }

        return $this->decodeResponse($response);
    }

    /**
     * @param string           $path
     * @param array            $payload
     * @param bool             $usePersonalToken
     * @param RetryConfig|null $retryConfig
     * @return array
     */
    protected function postWithRetry($path, array $payload, $usePersonalToken = false, $retryConfig = null): array
    {
        $enabled = ($retryConfig !== null) ? $retryConfig->enabled : false;
        $maxRetries = ($retryConfig !== null) ? $retryConfig->maxRetries : 0;
        $attempts = 0;

        do {
            $attempts++;
            try {
                return $this->post($path, $payload, $usePersonalToken);
            } catch (WasenderApiException $e) {
                if ($enabled && $e->getCode() === 429 && $attempts <= $maxRetries) {
                    $response = $e->getResponse();
                    $retryAfter = (isset($response['retry_after'])) ? (int) $response['retry_after'] : 1;
                    sleep($retryAfter);
                    continue;
                }
                throw $e;
            }
        } while ($enabled && $attempts <= $maxRetries);

        throw new WasenderApiException('Max retries exceeded', 429);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return array
     */
    private function decodeResponse($response): array
    {
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param string $body
     * @return array|null
     */
    private function decodeBody($body)
    {
        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : null;
    }
}
