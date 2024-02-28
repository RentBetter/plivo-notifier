JustCall Notifier
===============

Provides [JustCall](https://justcall.io/) integration for Symfony Notifier.


Installation
------------

1. Install package using composer

```bash
composer require rentbetter/just-call-notifier@^6.1
```

2. Add your JustCall DSN to your environment variables, e.g. in `.env`

```
JUST_CALL_DSN=justCall://API_KEY:API_SECRET@default
```

3. Register the `JustCallTransportFactory` in your `services.yaml`

```yaml
notifier.transport_factory.justCall:
   class: Symfony\Component\Notifier\Bridge\JustCall\JustCallTransportFactory
   parent: notifier.transport_factory.abstract
   tags: ['texter.transport_factory']
```

4. Enable the JustCall transport in your `config/packages/notifier.yaml` configuration

```yaml
framework:
  notifier:
    texter_transports:
      justCall: '%env(JUST_CALL_DSN)%'
```

5. Start sending SMS in your application, see [the symfony docs](https://symfony.com/doc/current/notifier.html#creating-sending-notifications)


DSN example
-----------

```
JUST_CALL_DSN=justCall://API_KEY:API_SECRET@default
```

where:
 - `API_KEY` is your JustCall api key
 - `API_SECRET` is your JustCall api secret


Resources
---------

 * [Contributing](https://symfony.com/doc/current/contributing/index.html)
 * [Report issues](https://github.com/symfony/symfony/issues) and
   [send Pull Requests](https://github.com/symfony/symfony/pulls)
   in the [main Symfony repository](https://github.com/symfony/symfony)
