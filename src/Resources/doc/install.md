Installation and Configuration
==============================

Installation
------------

Add the required package using composer.

### Stable version

```bash
composer require csa/guzzle-bundle:^3.0
```

### Legacy version

```bash
composer require csa/guzzle-bundle:^2.0
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

Autowiring
----------

If you rely on Symfony autowiring, you can choose to alias a specific service to the `GuzzleHttp\ClientInterface`
interface and `GuzzlHttp\Client` class.

```yml
csa_guzzle:
    profiler: '%kernel.debug%'
    logger: true
    clients:
        github_api:
            config:
                base_uri: 'https://api.github.com'
                headers:
                    Accept: application/vnd.github.v3+json
        jsonplaceholder:
            config:
                base_uri: 'https://jsonplaceholder.typicode.com'
                headers:
                    Accept: application/json
    default_client: github_api
```

Then, your github_api client will be automatically injected into your controller or service:

```php
<?php

namespace App\Controller;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;

class DefaultController
{
    private $twig;
    private $client;

    public function __construct(Environment $twig, Client $client)
    {
        $this->twig = $twig;
        $this->client = $client;
    }

    public function index()
    {
        $this->client->get('/users');

        return new Response($this->twig->render("base.html.twig"), 200, ['Content-Type' => 'text/html']);
    }
}
```

Next section: [Creating clients](clients.md)
