Guzzle service definitions
==========================

Importing definitions
---------------------

Since version `1.3`, CsaGuzzleBundle also integrates with `guzzlehttp/guzzle-services`.
This dependency is optional, so don't forget to add the library to your `composer.json`:

```console
    composer require guzzlehttp/services:~0.3
```

In order to use a service description, the only thing necessary is to specify the description file path
when you configure your Guzzle client in the semantic configuration:

```yml
csa_guzzle:
    clients:
        github_api:
            base_url: https://api.github.com
            defaults:
                headers:
                    Accept: application/vnd.github.v3+json
            description: /path/to/file.json
```

This will create a new service (`csa_guzzle.service.github`), which will use your description. You can use the service-enabled client the same way as you would use your normal client, in your controller or in another service:

```php
<?php

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MyController extends Controller
{
    public function indexAction()
    {
        $client = $this->get('csa_guzzle.service.github_api');
        // ...
    }
}
```

Creating new description loaders
--------------------------------

If you wish to add your own loader, to fetch a remote definition, you simply create a class implementing
`Symfony\Component\Config\Loader\LoaderInterface`, expose it as a service, and use the `csa_guzzle.description_loader`
dependency injection tag.

```php
<?php

use Symfony\Component\Config\Loader\Loader;

class JsonLoader extends Loader
{
    public function load($resource, $type = null)
    {
        return json_decode(file_get_contents($resource), true);
    }

    public function supports($resource, $type = null)
    {
        return 'json' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}
```

```xml
<service id="acme.loader" class="JsonLoader">
    <tag name="csa_guzzle.description_loader" />
</service>
```
