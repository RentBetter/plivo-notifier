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

use Symfony\Component\Notifier\Bridge\JustCall\JustCallTransportFactory;
use Symfony\Component\Notifier\Test\TransportFactoryTestCase;

final class JustCallTransportFactoryTest extends TransportFactoryTestCase
{
    public function createFactory(): JustCallTransportFactory
    {
        return new JustCallTransportFactory();
    }

    public static function createProvider(): iterable
    {
        yield [
            'justCall://host.test',
            'justCall://apiKey:apiSecret@host.test',
        ];
    }

    public static function supportsProvider(): iterable
    {
        yield [true, 'justCall://apiKey:apiSecret@default'];
        yield [false, 'somethingElse://authId:authToken@default'];
    }

    public static function unsupportedSchemeProvider(): iterable
    {
        yield ['somethingElse://apiKey:apiSecret@default'];
    }
}
