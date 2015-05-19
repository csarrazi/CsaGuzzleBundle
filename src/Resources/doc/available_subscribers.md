Available subscribers
=====================

Currently, four event subscribers are available:

* the `debug` subscriber
* the `stopwatch` subscriber
* the `logger` subscriber
* the `cache` subscriber


The `debug` and `stopwatch` subscribers
---------------------------------------

These two subscriber's objective is to provide integration with Symfony's debug tools:

* The `debug` subscriber enables the profiler.
* The `stopwatch` subscriber enables the Guzzle calls to be displayed in Symfony's timeline.

The profiler and stopwatch subscribers are only registered if the profiler is enabled.

To enable the two subscribers, you may simply configure CsaGuzzleBundle as follows:

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


The `logger` subscriber
-----------------------

The `logger` subscriber's objective is to provide a simple tool for logging Guzzle requests.

Enabling request logging, you simply need to enable it in Symfony's configuration:

```yml
csa_guzzle:
    logger:
        enabled: true
```

Like the `debug` subscriber, there's also a shorthand syntax to enable it:

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

You can configure the log format using the syntax described on [guzzle/log-subscriber's documentation](https://github.com/guzzle/log-subscriber#message-formatter).
You may also use of the three levels described in the formatter: `clf` (Apache log format), `debug`, or `short`:

```yml
csa_guzzle:
    logger:
        enabled: true
        format: debug
```


The `cache` subscriber
----------------------

The `cache` subscriber's objective is to provide a very simple cache, in order to cache Guzzle responses.

Even though only a [doctrine/cache](https://github.com/doctrine/cache) adapter is provided
(`Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\DoctrineAdapter`), the subscriber is agnostic.
If you wish to use your own cache implementation with the `cache` subscriber, you simply need
to implement `Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\StorageAdapterInterface`, and you're set!

This subscriber can be configured with the following configuration:

```yml
csa_guzzle:
    cache:
        enabled: true
        adapter: my_storage_adapter
```

To use the doctrine cache adapter, you need to use the `Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\DoctrineAdapter`
class, in which you should inject your doctrine cache service. For example, using doctrine/cache's `FilesystemCache`:

```xml
<service id="my_storage_adapter" class="Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\DoctrineAdapter">
    <argument type="service" id="my_cache_service" />
</service>

<service id="my_cache_service" class="Doctrine\Common\Cache\FilesystemCache">
    <argument>%kernel.cache_dir%/my_cache_folder</argument>
</service>
```
