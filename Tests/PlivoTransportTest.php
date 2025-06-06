<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Plivo\Tests;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Notifier\Bridge\Plivo\PlivoTransport;
use Symfony\Component\Notifier\Exception\InvalidArgumentException;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Test\TransportTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class PlivoTransportTest extends TransportTestCase
{
    public static function createTransport(HttpClientInterface $client = null, string $from = 'from'): PlivoTransport
    {
        return new PlivoTransport('authId', 'authToken', $from, 'https://localhost/status', null, $client ?? (new self())->createMock(HttpClientInterface::class));
    }

    public static function toStringProvider(): iterable
    {
        yield ['plivo://api.plivo.com?from=from', self::createTransport()];
    }

    public static function supportedMessagesProvider(): iterable
    {
        yield [new SmsMessage('0611223344', 'Hello!')];
    }

    public static function unsupportedMessagesProvider(): iterable
    {
        yield [new ChatMessage('Hello!')];
        yield [(new self())->createMock(MessageInterface::class)];
    }

    /**
     * @dataProvider invalidFromProvider
     */
    public function testInvalidArgumentExceptionIsThrownIfFromIsInvalid(string $from)
    {
        $transport = $this->createTransport(null, $from);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('The "From" number "%s" is not a valid phone number, shortcode, or alphanumeric sender ID.', $from));

        $transport->send(new SmsMessage('+33612345678', 'Hello!'));
    }

    public function invalidFromProvider(): iterable
    {
        // alphanumeric sender ids
        yield 'too short' => ['a'];
        yield 'too long' => ['abcdefghijkl'];

        // phone numbers
        yield 'no zero at start if phone number' => ['+0'];
        yield 'phone number to short' => ['+1'];
    }

    /**
     * @dataProvider validFromProvider
     */
    public function testNoInvalidArgumentExceptionIsThrownIfFromIsValid(string $from)
    {
        $message = new SmsMessage('+33612345678', 'Hello!');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(202);
        $response->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode([
                'message_uuid' => ['db3ce55a-7f1d-11e1-8ea7-1231380bc196'],
                'message' => 'message(s) queued',
                'api_id' => 'db342550-7f1d-11e1-8ea7-1231380bc196',
            ]));

        $client = new MockHttpClient(function (string $method, string $url, array $options = []) use ($response): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame('https://api.plivo.com/v1/Account/authId/Message/', $url);

            return $response;
        });

        $transport = $this->createTransport($client, $from);

        $sentMessage = $transport->send($message);

        $this->assertSame('db3ce55a-7f1d-11e1-8ea7-1231380bc196', $sentMessage->getMessageId());
    }

    public function validFromProvider(): iterable
    {
        // alphanumeric sender ids
        yield ['ab'];
        yield ['abc'];
        yield ['abcd'];
        yield ['abcde'];
        yield ['abcdef'];
        yield ['abcdefg'];
        yield ['abcdefgh'];
        yield ['abcdefghi'];
        yield ['abcdefghij'];
        yield ['abcdefghijk'];
        yield ['abcdef ghij'];
        yield [' abcdefghij'];
        yield ['abcdefghij '];

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
