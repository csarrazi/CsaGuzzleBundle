CsaGuzzleBundle
===============

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/csarrazi/CsaGuzzleBundle?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Latest Stable Version](https://poser.pugx.org/csa/guzzle-bundle/v/stable.png)](https://packagist.org/packages/csa/guzzle-bundle "Latest Stable Version")
[![Latest Unstable Version](https://poser.pugx.org/csa/guzzle-bundle/v/unstable.png)](https://packagist.org/packages/csa/guzzle-bundle "Latest Unstable Version")
[![Build Status](https://travis-ci.org/csarrazi/CsaGuzzleBundle.png?branch=master)](https://travis-ci.org/csarrazi/CsaGuzzleBundle "Build status")
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/eceadd60-cc6c-473c-9d20-e8207654d70b/mini.png)](https://insight.sensiolabs.com/projects/eceadd60-cc6c-473c-9d20-e8207654d70b "SensioLabsInsight")

Installation
------------

Add the required package using composer.

```bash
composer require csa/guzzle-bundle:@stable
```

Add the bundle to your AppKernel.

```php
<?php
// in %kernel.root_dir%/AppKernel.php
$bundles[] = new Csa\Bundle\GuzzleBundle\CsaGuzzleBundle();
```

To enable the data collector (only in the ```dev``` environment, you may simply
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

* Upgrade [from 1.0 to 1.1](UPGRADE-1.1.md)
* Upgrade [from 1.1 to 1.2](UPGRADE-1.2.md)

Documentation
-------------

* [Creating clients](src/Resources/doc/clients.md)
* [Registering new event subscribers](src/Resources/doc/event_subscribers.md)
* [Available event subscribers](src/Resources/doc/available_subscribers.md)
* [Configuration reference](src/Resources/doc/configuration_reference.md)

License
-------

This library is under the MIT license. For the full copyright and license
information, please view the [LICENSE](src/Resources/meta/LICENSE) file that was
distributed with this source code.

[![Built with Grunt](https://cdn.gruntjs.com/builtwith.png)](http://gruntjs.com/)
