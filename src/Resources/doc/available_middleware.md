Available middleware
====================

Currently, five middleware are available:

* the `debug` middleware
* the `stopwatch` middleware
* the `logger` middleware
* the `cache` middleware
* the `mock` middleware


The `debug` and `stopwatch` middleware
--------------------------------------

These two middleware's objective is to provide integration with Symfony's debug tools:

* The `debug` middleware enables the profiler.
* The `stopwatch` middleware enables the Guzzle calls to be displayed in Symfony's timeline.

The profiler and stopwatch middleware are only registered if the profiler is enabled.

To enable the two middleware, you may simply configure CsaGuzzleBundle as follows:

```yml
csa_guzzle:
    profiler:
        enabled: true
```

or use the shorthand version:

```yml
csa_guzzle:
    profiler: true
```


The `logger` middleware
-----------------------

The `logger` middleware's objective is to provide a simple tool for logging Guzzle requests.

Enabling request logging, you simply need to enable it in Symfony's configuration:

```yml
csa_guzzle:
    logger:
        enabled: true
```

Like the `debug` middleware, there's also a shorthand syntax to enable it:

```yml
csa_guzzle:
    logger: true
```

Using the advanced configuration, you may also configure your own logger, as long as it implements
the PSR-3 `LoggerInterface`:

```yml
csa_guzzle:
    logger:
        enabled: true
        service: my_logger_service
```

You can configure the log format using the syntax described in [guzzlehttp/guzzle's documentation](https://github.com/guzzle/guzzle/blob/master/src/MessageFormatter.php#L12).
You may also use of the three levels described in the formatter: `clf` (Apache log format), `debug`, or `short`:

```yml
csa_guzzle:
    logger:
        enabled: true
        format: debug
```

You could also change the level of logging, for `dev`, you likely want `debug`, for `prod`, you likely want `error`. You'll find more log levels in the [LogLevel of php-fig](https://github.com/php-fig/log/blob/master/Psr/Log/LogLevel.php).

```yml
csa_guzzle:
    logger:
        enabled: true
        level: debug
```

The `cache` middleware
----------------------

The `cache` middleware's objective is to provide a very simple cache, in order to cache Guzzle responses.

Even though only a [doctrine/cache](https://github.com/doctrine/cache) adapter is provided
(`Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\DoctrineAdapter`), the middleware is agnostic.
If you wish to use your own cache implementation with the `cache` middleware, you simply need
to implement `Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\StorageAdapterInterface`, and you're set!

This middleware can be configured with the following configuration:

```yml
csa_guzzle:
    cache:
        enabled: true
        adapter: my_storage_adapter
```

To use the doctrine cache adapter, you need to use the `Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\DoctrineAdapter`
class, in which you should inject your doctrine cache service. For example, using doctrine/cache's `FilesystemCache`:

```xml
<services>
    <service id="my_storage_adapter" class="Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\DoctrineAdapter">
        <argument type="service" id="my_cache_service" />
    </service>

    <service id="my_cache_service" class="Doctrine\Common\Cache\FilesystemCache">
        <argument>%kernel.cache_dir%/my_cache_folder</argument>
    </service>
</services>
```

The `mock` middleware
---------------------

When running tests, you often want to disable real HTTP requests to your (or an external) API.
The `mock` middleware can record those requests to replay them in tests.

The `mock` middleware can work in two modes:

* record, which saves your HTTP requests inside a directory in your filesystem
* replay, which uses your saved HTTP requests from the same directory

Of course, this middleware should only be used in the `test` environment (or `dev`, if you don't have
access to the remote server):

```yml
# config_test.yml
csa_guzzle:
    mock:
        storage_path: "%kernel.root_dir%/../features/fixtures/guzzle"
        mode: record
```

The generated files can then be committed in the VCS.

To use them, simply change the mode to `replay`:

```yml
# config_test.yml
csa_guzzle:
    mock:
        storage_path: "%kernel.root_dir%/../features/fixtures/guzzle"
        mode: replay
```

A few customizations can be done with the `mock` middleware. You can indeed blacklist:

* Request headers, so they are not used for generating the mock's filename.
* Response headers, so they are not saved in the mock file.

For this, you can simply configure your client as follows:

```yml
# config_test.yml
csa_guzzle:
    mock:
        # ...
        request_headers_blacklist: ['User-Agent', 'Host', 'X-Guzzle-Cache', 'X-Foo']
        response_headers_blacklist: ['X-Guzzle-Cache', 'X-Bar']
```

Next Section: [Streaming a guzzle response](response_streaming.md)
