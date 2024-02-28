<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\JustCall\Tests;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Notifier\Bridge\JustCall\JustCallTransport;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Test\TransportTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class JustCallTransportTest extends TransportTestCase
{
    public static function createTransport(HttpClientInterface $client = null): JustCallTransport
    {
        return new JustCallTransport('apiKey', 'apiSecret', $client ?? (new JustCallTransportTest)->createMock(HttpClientInterface::class));
    }

    public static function toStringProvider(): iterable
    {
        yield ['justCall://api.justcall.io', self::createTransport()];
    }

    public static function supportedMessagesProvider(): iterable
    {
        yield [new SmsMessage('0611223344', 'Hello!')];
    }

    public static function unsupportedMessagesProvider(): iterable
    {
        yield [new ChatMessage('Hello!')];
        yield [(new JustCallTransportTest)->createMock(MessageInterface::class)];
    }

    /**
     * @dataProvider validFromProvider
     */
    public function testNoInvalidArgumentExceptionIsThrownIfFromIsValid(string $from)
    {
        $message = new SmsMessage('+33612345678', 'Hey! this is body.', '+61400000000');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(200)
        ;

        // example response from https://developer.justcall.io/reference/texts_new
        $jsonResponse = <<<JSON
{
  "id": 15112,
  "contact_number": "13801136XXX",
  "contact_name": "Justcall New Contact.",
  "contact_email": "john@suits.co",
  "justcall_number": "127077XXXXX",
  "justcall_line_name": "Sales Team",
  "agent_id": 1308,
  "agent_name": "John Smith",
  "agent_email": "john@abc.com",
  "sms_date": "2017-12-18",
  "sms_user_date": "2017-12-17",
  "sms_time": "13:55:43",
  "sms_user_time": "8:25:43",
  "direction": "Outbound",
  "cost_incurred": 0.005,
  "sms_info": {
    "body": "Hey! this is body.",
    "is_mms": "Yes",
    "mms": [
      {
        "media_url": "https://www.filepicker.io/api/file/NZsXXXXXXXXXXXXXXX",
        "content_type": "image/png"
      }
    ]
  },
  "delivery_status": "Delivered",
  "is_deleted": "FALSE",
  "medium": "Zoho Automation"
}
JSON;
        $response->expects($this->once())
            ->method('getContent')
            ->willReturn($jsonResponse)
        ;

        $client = new MockHttpClient(function (string $method, string $url, array $options = []) use ($response): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame('https://api.justcall.io/v2/texts/new', $url);

            return $response;
        });

        $transport = $this->createTransport($client, $from);

        $sentMessage = $transport->send($message);

        $this->assertSame('15112', $sentMessage->getMessageId());
    }

    public function validFromProvider(): iterable
    {
        // phone numbers
        yield ['+11'];
        yield ['+112'];
        yield ['+1123'];
        yield ['+11234'];
        yield ['+112345'];
        yield ['+1123456'];
        yield ['+11234567'];
        yield ['+112345678'];
        yield ['+1123456789'];
        yield ['+11234567891'];
        yield ['+112345678912'];
        yield ['+1123456789123'];
        yield ['+11234567891234'];
        yield ['+112345678912345'];
    }
}
