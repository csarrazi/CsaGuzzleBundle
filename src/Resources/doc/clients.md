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
            config:
                base_url: https://api.github.com
                defaults:
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

If you override your client's class, you can also set the class for your client:

```yml
csa_guzzle:
    clients:
        my_client:
            class: AppBundle\Client
            # ...
```

Of course, you need to make sure that your client class has no constructor arguments.

If you need to pass constructor arguments to your class, then you should use the tag syntax (see below).

Registering your own service
----------------------------

To have a client supported by the bundle, simply tag it as such:

```xml
<service id="acme.client" class="%acme.client.class%">
    <tag name="csa_guzzle.client" />
</service>
```
