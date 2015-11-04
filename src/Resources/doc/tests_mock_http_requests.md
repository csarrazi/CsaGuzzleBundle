Mock HTTP requests in tests
===========================

When running tests, you often want to disable real HTTP requests to your (or an external) API.
The CsaGuzzleBundle can record those requests to replay them in tests.

First, enable the record mode, and play your tests:
i.e. in `config_test.yml`

```yml
csa_guzzle:
    mock:
        storage_path: "%kernel.root_dir%/../features/fixtures/guzzle"
        mode: record
```

Generated files can then be added to you VCS.
To use your mocks, change the mode to `replay`

```yml
csa_guzzle:
    mock:
        storage_path: "%kernel.root_dir%/../features/fixtures/guzzle"
        mode: replay
```

That's it !
