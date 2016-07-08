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

Middleware are automatically used by all your clients, if you are using the semantic configuration.
However, if you wish to, you can enable specific middleware, for a given client:

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

Disabled middleware have priority over enabled middleware.

When registering your own clients with the bundle, you can explicitly list all
enabled middleware. The `middleware` attribute takes a space-delimited list of
middleware names. In that case only the specified middleware will be registered
for that client:

**XML:**

```xml
<service id="acme.client" class="%acme.client.class%">
    <tag name="csa_guzzle.client" middleware="my_middleware another_middleware !yet_another_middleware" />
</service>
```

**YAML:**

```yml
acme.client:
    class: %acme.client.class%

    tags:
      - { name: csa_guzzle.client, middleware: 'my_middleware another_middleware !yet_another_middleware'}
```

Next section: [Available middleware](available_middleware.md)
