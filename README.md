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

This bundle integrates Guzzle >=4.0 in Symfony. The `1.x` branch supports Guzzle 4 and 5, and the `master` branch supports Guzzle 6.

Currently, it supports the following features:

* Integration with Symfony's debug tools (web debug toolbar, profiler, logger, timeline, ...)
* Configuring a Guzzle client simply using configuration
* Multiple middleware / subscribers (logger, profiler, timeline, cache, mock, and more to come)
* Service descriptions to describe your services is json format (only in the 1.3 branch, though)

![Web debug Toolbar](https://cloud.githubusercontent.com/assets/4208880/12815019/c49faeec-cb43-11e5-9de9-dc3423ea6c35.jpg)
![Profiler panel integration](https://cloud.githubusercontent.com/assets/4208880/12815021/c4a16746-cb43-11e5-9061-f1ea15b04e62.jpg)
![Profiler timeline integration](https://cloud.githubusercontent.com/assets/4208880/12815020/c49fc7ec-cb43-11e5-89c3-93ee82711dc2.jpg)

Installation
------------

All the installation instructions are located in the documentation

Upgrade
-------

Although I try to guarantee forward-compatibility of the bundle with previous versions.
Here are the upgrade notes between each version.

See [Upgrade.md](UPGRADE.md).

Documentation
-------------

### Documentation for stable (2.*)

* [Installation](../2.x/src/Resources/doc/install.md)
* [Creating clients](../2.x/src/Resources/doc/clients.md)
* [Registering new middleware](../2.x/src/Resources/doc/middleware.md)
* [Available middleware](../2.x/src/Resources/doc/available_middleware.md)
* [Streaming a guzzle response](../2.x/src/Resources/doc/response_streaming.md)
* [Configuration reference](../2.x/src/Resources/doc/configuration_reference.md)

### Documentation for legacy (1.3)

* [Creating clients](../1.3/src/Resources/doc/clients.md)
* [Registering new event subscribers](../1.3/src/Resources/doc/event_subscribers.md)
* [Available event subscribers](../1.3/src/Resources/doc/available_subscribers.md)
* [Streaming a guzzle response](../1.3/src/Resources/doc/response_streaming.md)
* [Service descriptions](../1.3/src/Resources/doc/service_descriptions.md)
* [Configuration reference](../1.3/src/Resources/doc/configuration_reference.md)

### Documentation for next version (master)

* [Installation](../master/src/Resources/doc/install.md)
* [Creating clients](../master/src/Resources/doc/clients.md)
* [Registering new middleware](../master/src/Resources/doc/middleware.md)
* [Available middleware](../master/src/Resources/doc/available_middleware.md)
* [Streaming a guzzle response](../master/src/Resources/doc/response_streaming.md)
* [Configuration reference](../master/src/Resources/doc/configuration_reference.md)

Contributing
------------

CsaGuzzleBundle is an open source project. If you'd like to contribute, please read
the [Contributing Guidelines](CONTRIBUTING.md).

License
-------

This library is under Apache License 2.0. For the full copyright and license
information, please view the [LICENSE](src/Resources/meta/LICENSE) file that was
distributed with this source code.

