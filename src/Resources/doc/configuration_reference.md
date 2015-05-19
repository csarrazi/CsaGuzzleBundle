Configuration reference
=======================

```yml
csa_guzzle:
    profiler:
        enabled:              false

        # The maximum size of the body which should be stored in the profiler (in bytes)
        max_body_size:        65536 # Example: 65536
    logger:
        enabled:              false
        service:              ~
    clients:

        # Prototype
        name:
            config:               ~
            subscribers:

                # Prototype
                subscriber_name:      ~
    factory_class:        GuzzleHttp\Client
    cache:
        enabled: false
        adapter: ~
```
