Creating new middleware
=======================

Creating a new Guzzle [middleware](http://guzzle.readthedocs.org/en/latest/handlers-and-middleware.html#middleware)
is as easy as creating a symfony service and using the `csa_guzzle.middleware` tag, giving it an alias and
(optionally) a priority:

```xml
<service
        id="acme.middleware"
        class="Closure">
    <factory class="My\Middleware" method="my_middleware" />
    <tag name="csa_guzzle.middleware" alias="my_middleware" priority="100" />
</service>
```

You can also define middleware as a class with the `__invoke` method like this:

```php
class Middleware
{
    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $request = $request->withHeader('X-Test', 'I was here');

            return $handler($request, $options);
        };
    }
}

```

The service definition for such a class is then:

```yaml
My\Middleware:
    tags:
        - { name: csa_guzzle.middleware, alias: my_middleware, priority: 100 }
```

Middleware are automatically used by all your clients, if you are using the semantic configuration.
However, if you wish to, you can limit a client to a list of specific middleware:

```yml
csa_guzzle:
    # ...
    clients:
        # Prototype
        github_api:
            config:
                base_uri: https://api.github.com
                headers:
                    Accept: application/vnd.github.v3+json
            middleware: ['debug', 'my_middleware'] # Note the use of the alias defined earlier in the service definition.
```

You can also disable specific middleware, by prefixing the middleware name with a `!` character:

```yml
csa_guzzle:
    # ...
    clients:
        github_api:
            # ...
            middleware: ['!my_middleware']
```

You can either whitelist or blacklist middleware. Using both whitelisting and blacklisting will trigger an exception.

When registering your own clients with the bundle, you can explicitly list all
enabled middleware. The `middleware` attribute takes a space-delimited list of
middleware names. In that case only the specified middleware will be registered
for that client:

**XML:**

```xml
<service id="acme.client" class="%acme.client.class%">
    <tag name="csa_guzzle.client" middleware="my_middleware another_middleware" />
</service>
```

**YAML:**

```yml
acme.client:
    class: %acme.client.class%

    tags:
      - { name: csa_guzzle.client, middleware: 'my_middleware another_middleware'}
```

Next section: [Available middleware](available_middleware.md)
