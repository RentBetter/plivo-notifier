<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Plivo;

use Symfony\Component\Notifier\Exception\InvalidArgumentException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class PlivoTransport extends AbstractTransport
{
    protected const HOST = 'api.plivo.com';

    public function __construct(
        private readonly string $authId,
        private readonly string $authToken,
        private readonly string $from,
        private readonly ?string $statusUrl = null,
        private readonly ?string $statusUrlMethod = null,
        HttpClientInterface $client = null,
        EventDispatcherInterface $dispatcher = null,
    )
    {
        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return sprintf('plivo://%s?from=%s', $this->getEndpoint(), $this->from);
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof SmsMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, SmsMessage::class, $message);
        }

        if (!preg_match('/^[a-zA-Z0-9\s]{2,11}$/', $this->from) && !preg_match('/^\+[1-9]\d{1,14}$/', $this->from)) {
            throw new InvalidArgumentException(sprintf('The "From" number "%s" is not a valid phone number, shortcode, or alphanumeric sender ID.', $this->from));
        }

        $endpoint = sprintf('https://%s/v1/Account/%s/Message/', $this->getEndpoint(), $this->authId);
        $response = $this->client->request('POST', $endpoint, [
            'auth_basic' => $this->authId.':'.$this->authToken,
            'body' => [
                'src' => $this->from,
                'dst' => $message->getPhone(),
                'text' => $message->getSubject(),
                'url' => $this->statusUrl,
                'method' => $this->statusUrlMethod,
            ],
        ]);

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Could not reach the remote Plivo server.', $response, 0, $e);
        }

        if (202 !== $statusCode) {
            $error = $response->toArray(false);

            throw new TransportException('Unable to send the SMS: '.$error['error'].sprintf(' (request %s).', $error['api_id']), $response);
        }

        $success = $response->toArray(false);

        $sentMessage = new SentMessage($message, (string) $this);
        $sentMessage->setMessageId($success['message_uuid'][0]);

        return $sentMessage;
    }
}
