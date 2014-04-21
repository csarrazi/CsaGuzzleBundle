CsaGuzzleBundle
===============

[![Latest Stable Version](https://poser.pugx.org/csa/guzzle-bundle/v/stable.png)](https://packagist.org/packages/csa/guzzle-bundle "Latest Stable Version")
[![Latest Unstable Version](https://poser.pugx.org/csa/guzzle-bundle/v/unstable.png)](https://packagist.org/packages/csa/guzzle-bundle "Latest Unstable Version")
[![Build Status](https://travis-ci.org/csarrazi/CsaGuzzleBundle.png?branch=master)](https://travis-ci.org/csarrazi/CsaGuzzleBundle "Build status")
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/eceadd60-cc6c-473c-9d20-e8207654d70b/mini.png)](https://insight.sensiolabs.com/projects/eceadd60-cc6c-473c-9d20-e8207654d70b "SensioLabsInsight")

Installation
------------

Add the required package using composer.

    composer require csa/guzzle-bundle:dev-master

Add the bundle to your AppKernel.

    <?php
    // in %kernel.root_dir%/AppKernel.php
    $bundles[] = new Csa\Bundle\GuzzleBundle\CsaGuzzleBundle();

To enable the data collector (only in the ```dev``` environment, you may simply configure the CsaGuzzleBundle as follows:

    csa_guzzle:
        profiler: %kernel.debug%

Create a client using the provided factory service
--------------------------------------------------

Simply create a service as follows:

    <service
            id="acme.client"
            class="%acme.client.class%"
            factory-service="csa_guzzle.client_factory"
            factory-method="create">
        <!-- An array of configuration values -->
        <tag name="csa_guzzle.client" />
    </service>

License
-------

This library is under the MIT license. For the full copyright and license
information, please view the LICENSE file that was distributed with this source
code.

[![Built with Grunt](https://cdn.gruntjs.com/builtwith.png)](http://gruntjs.com/)
