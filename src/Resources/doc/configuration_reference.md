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
        format:               '{hostname} {req_header_User-Agent} - [{date_common_log}] "{method} {target} HTTP/{version}" {code} {res_header_Content-Length}'
        level:                debug
    cache:
        enabled:              false
        adapter:              ~
    clients:

        # Prototype
        name:
            class:            GuzzleHttp\Client
            config:           ~
            middleware:       []
            alias:            ~
    mock:
        enabled:              false
        storage_path:         ~ # Required
        mode:                 replay
```

[Back to index](../../../README.md)
