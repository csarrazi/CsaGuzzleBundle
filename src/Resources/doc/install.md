Installation and Configuration
==============================

Installation
------------

Add the required package using composer.

### Stable version

```bash
composer require csa/guzzle-bundle:^2.0
```

### Legacy version

```bash
composer require csa/guzzle-bundle:^1.3
```

### Bleeding-edge version

```bash
composer require csa/guzzle-bundle:@dev
```

### Enabling the bundle

Add the bundle to your AppKernel.

```php
// in %kernel.root_dir%/AppKernel.php
$bundles = array(
    // ...
    new Csa\Bundle\GuzzleBundle\CsaGuzzleBundle(),
    // ...
);
```

Configuration
-------------

To enable the data collector (only in the `dev` environment, you may simply
configure the CsaGuzzleBundle as follows:

```yml
csa_guzzle:
    profiler: '%kernel.debug%'
```

You may also enable the included logger, in order log outcoming requests:

```yml
csa_guzzle:
    logger: true
```

Next section: [Creating clients](clients.md)
