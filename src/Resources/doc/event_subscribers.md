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

Subscribers are automatically used by all your clients, if you are using semantic configuration.
However, if you wish to, you can disable a specific subscriber, for a given client:

```yml
csa_guzzle:
    profiler: true
    logger:   false
    clients:
        # Prototype
        github_api:
            config:
                base_url: https://api.github.com
                headers:
                    Accept: application/vnd.github.v3+json
            subscribers:
                logger: false
                my_subscriber: false # Note the use of the alias defined earlier in the service definition.
```
