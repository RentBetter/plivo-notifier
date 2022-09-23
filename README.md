Plivo Notifier
===============

Provides [Plivo](https://www.plivo.com) integration for Symfony Notifier.

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
