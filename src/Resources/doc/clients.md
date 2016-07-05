Creating a service for your client
==================================

There are two ways for creating a service for your client:

* Using the semantic configuration (Beginners)
* Registering your own service (Advanced users)

Creating a client using semantic configuration
----------------------------------------------

Simply write the following code:

```yml
csa_guzzle:
    clients:
        github_api:
            config: # you can specify the options as in http://docs.guzzlephp.org/en/latest/quickstart.html#creating-a-client
                base_uri: https://api.github.com
                timeout: 2.0
                headers:
                    Accept: application/vnd.github.v3+json
```

The previous code will create a new service, called `csa_guzzle.client.github_api`, that you can use in your controller, or that you can inject in another service:

```php
<?php

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MyController extends Controller
{
    public function indexAction()
    {
        $client = $this->get('csa_guzzle.client.github_api');
        // ...
    }
}
```

You may want to mark the service as [lazy](http://symfony.com/doc/current/components/dependency_injection/lazy_services.html).

```yml
csa_guzzle:
    clients:
        my_client:
            lazy: true
            # ...
```

If you override your client's class, you can also set the class for your client:

```yml
csa_guzzle:
    clients:
        my_client:
            class: AppBundle\Client
            # ...
```

Of course, you need to make sure that your client class' constructor has exactly the same signature as Guzzle's Client class.

Registering your own service
----------------------------

To have a client supported by the bundle, simply tag it as such:

**XML:**

```xml
<service id="acme.client" class="%acme.client.class%">
    <argument type="collection">
        <argument key="base_uri">http://acme.com</argument>
        <argument key="timeout">2.0</argument>
    </argument>
    <tag name="csa_guzzle.client" />
</service>
```

**YAML:**

```yml
acme.client:
    class: %acme.client.class%
    arguments: [{ base_uri: http://acme.com, timeout: 2.0} ]
```

Next section: [Registering new middleware](middleware.md)
