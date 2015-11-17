CsaGuzzleBundle
===============

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/csarrazi/CsaGuzzleBundle?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Latest Stable Version](https://poser.pugx.org/csa/guzzle-bundle/v/stable.png)](https://packagist.org/packages/csa/guzzle-bundle "Latest Stable Version")
[![Latest Unstable Version](https://poser.pugx.org/csa/guzzle-bundle/v/unstable.png)](https://packagist.org/packages/csa/guzzle-bundle "Latest Unstable Version")
[![License](https://poser.pugx.org/csa/guzzle-bundle/license)](https://packagist.org/packages/csa/guzzle-bundle)
[![Travis Build Status](https://travis-ci.org/csarrazi/CsaGuzzleBundle.png?branch=master)](https://travis-ci.org/csarrazi/CsaGuzzleBundle "Build status")
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/eceadd60-cc6c-473c-9d20-e8207654d70b/mini.png)](https://insight.sensiolabs.com/projects/eceadd60-cc6c-473c-9d20-e8207654d70b "SensioLabsInsight")
[![Appveyor Build Status](https://ci.appveyor.com/api/projects/status/e5sev7kmv8l4q13q/branch/master?svg=true)](https://ci.appveyor.com/project/csarrazi/csaguzzlebundle/branch/master "Appveyor")

Description
-----------

This bundle integrates Guzzle >=4.0 in Symfony. Currently, it supports the following features:

* Integration with Symfony's debug tools (web debug toolbar, profiler, logger, timeline, ...)
* Configuring a Guzzle client simply using configuration
* Service descriptions to describe your services is json format (only in the 1.3 branch, though)

![Web debug Toolbar](https://cloud.githubusercontent.com/assets/465798/7407652/dda8bda4-ef14-11e4-9e9e-1db2fa6a346d.png)
![Profiler panel integration](https://cloud.githubusercontent.com/assets/465798/7407654/e4432b2c-ef14-11e4-8f84-b11b32dcce86.png)
![Profiler timeline integration](https://cloud.githubusercontent.com/assets/465798/7407656/e7241e14-ef14-11e4-875c-d36ef726679e.png)

Installation
------------

### Stable version

Add the required package using composer.

```bash
composer require csa/guzzle-bundle:@stable
```

### Bleeding-edge version

Add the required package using composer.

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

To enable the data collector (only in the `dev` environment, you may simply
configure the CsaGuzzleBundle as follows:

```yml
csa_guzzle:
    profiler: %kernel.debug%
```

You may also enable the included logger, in order log outcoming requests:

```yml
csa_guzzle:
    logger: true
```

Upgrade
-------

Although I try to guarantee forward-compatibility of the bundle with previous versions.
Here are the upgrade notes between each version.

See [Upgrade.md](UPGRADE.md).

Documentation
-------------

### Documentation for stable (2.0@dev/dev-master)

* [Creating clients](src/Resources/doc/clients.md)
* [Registering new middleware](src/Resources/doc/middleware.md)
* [Available middleware](src/Resources/doc/available_middleware.md)
* [Configuration reference](src/Resources/doc/configuration_reference.md)
* [Streaming a guzzle response](src/Resources/doc/response_streaming.md)
* [Mock HTTP requests in tests](src/Resources/doc/tests_mock_http_requests.md)

### Documentation for legacy (1.3)

* [Creating clients](../1.3/src/Resources/doc/clients.md)
* [Registering new event subscribers](../1.3/src/Resources/doc/event_subscribers.md)
* [Available event subscribers](../1.3/src/Resources/doc/available_subscribers.md)
* [Configuration reference](../1.3/src/Resources/doc/configuration_reference.md)
* [Streaming a guzzle response](../1.3/src/Resources/doc/response_streaming.md)
* [Service descriptions](../1.3/src/Resources/doc/service_descriptions.md)

Contributing
------------

CsaGuzzleBundle is an open source project. If you'd like to contribute, please read
the [Contributing Guidelines](CONTRIBUTING.md).

License
-------

This library is under the MIT license. For the full copyright and license
information, please view the [LICENSE](src/Resources/meta/LICENSE) file that was
distributed with this source code.

[![Built with Grunt](https://cdn.gruntjs.com/builtwith.png)](http://gruntjs.com/)
