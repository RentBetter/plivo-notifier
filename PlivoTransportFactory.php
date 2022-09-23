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

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class PlivoTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): PlivoTransport
    {
        $scheme = $dsn->getScheme();

        if ('plivo' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'plivo', $this->getSupportedSchemes());
        }

        $authId = $this->getUser($dsn);
        $authToken = $this->getPassword($dsn);
        $from = $dsn->getRequiredOption('from');
        $statusUrl = $dsn->getOption('statusUrl');
        $statusUrlMethod = $dsn->getOption('statusUrlMethod');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new PlivoTransport($authId, $authToken, $from, $statusUrl, $statusUrlMethod, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return ['plivo'];
    }
}
