Plivo Notifier
===============

Provides [Plivo](https://www.plivo.com) integration for Symfony Notifier.


Installation
------------

1. Install package using composer

```bash
composer require rentbetter/plivo-notifier@^6.1
```

2. Add your Plivo DSN to your environment variables, e.g. in `.env`

```
PLIVO_DSN=plivo://AUTH_ID:AUTH_TOKEN@default?from=FROM
```

3. Register the `PlivoTransportFactory` in your `services.yaml`

```yaml
notifier.transport_factory.plivo:
   class: Symfony\Component\Notifier\Bridge\Plivo\PlivoTransportFactory
   parent: notifier.transport_factory.abstract
   tags: ['texter.transport_factory']
```

4. Enable the Plivo transport in your `config/packages/notifier.yaml` configuration

```yaml
framework:
  notifier:
    texter_transports:
      plivo: '%env(PLIVO_DSN)%'
```

5. Start sending SMS in your application, see [the symfony docs](https://symfony.com/doc/current/notifier.html#creating-sending-notifications)


DSN example
-----------

```
PLIVO_DSN=plivo://AUTH_ID:AUTH_TOKEN@default?from=FROM&statusUrl=URL&statusUrlMethod=METHOD
```

where:
 - `AUTH_ID` is your Plivo auth ID
 - `AUTH_TOKEN` is your Plivo auth token
 - `FROM` is your sender
 - `URL` (optional) is the URL to which Plivo should send delivery updates
 - `METHOD` (optional) is the HTTP method (GET, POST) with which Plivo should call `URL`


Resources
---------

 * [Contributing](https://symfony.com/doc/current/contributing/index.html)
 * [Report issues](https://github.com/symfony/symfony/issues) and
   [send Pull Requests](https://github.com/symfony/symfony/pulls)
   in the [main Symfony repository](https://github.com/symfony/symfony)
