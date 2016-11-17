Creating new event subscribers
==============================

Creating a new Guzzle event subscriber is as easy as creating a symfony service
and using the `csa_guzzle.subscriber` tag, and giving it an alias:

```xml
<service
        id="acme.subscriber"
        class="My\Bundle\Subscriber\MySubscriber">
    <tag name="csa_guzzle.subscriber" alias="my_subscriber" />
</service>
```

Subscribers are automatically used by all your clients. However, if you wish to, you can disable a specific subscriber, 
for a given client:

```yml
csa_guzzle:
    profiler: true
    logger:   false
    clients:
        # Prototype
        github_api:
            config:
                base_url: https://api.github.com
                defaults:
                    headers:
                        Accept: application/vnd.github.v3+json
            subscribers:
                logger: false
                my_subscriber: false # Note the use of the alias defined earlier in the service definition.
```

When registering your own clients with the bundle, all the subscribers are used by default. However, you can explicitly list
the enabled subscribers. The `subscribers` attribute takes a comma-delimited list of subscriber names. In that case any other
subscriber will be disabled for that client:

```xml
<service id="acme.client" class="%acme.client.class%">
    <tag name="csa_guzzle.client" subscribers="my_subscriber,another_subscriber" />
</service>
```
