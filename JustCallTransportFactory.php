<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\JustCall;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class JustCallTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): JustCallTransport
    {
        $scheme = $dsn->getScheme();

        if ('justCall' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'justCall', $this->getSupportedSchemes());
        }

        $authId = $this->getUser($dsn);
        $authToken = $this->getPassword($dsn);
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new JustCallTransport($authId, $authToken, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return ['justCall'];
    }
}
