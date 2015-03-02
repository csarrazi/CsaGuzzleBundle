Streaming a guzzle response
===========================

CsaGuzzleBundle exposes a Symfony response, which can stream a guzzle response to the user.
The goal is to send a guzzle response directly to the final user, while consuming the least amount of memory.

For this, you simply need to encapsulate your Guzzle response using the provided `Csa\Bundle\GuzzleBundle\HttpKernel\StreamResponse` class:

```php
<?php

use Symfony\Component\DependencyInjection\ContainerAware;
use Csa\Bundle\GuzzleBundle\HttpFoundation\StreamResponse;

class MyController extends ContainerAware
{
    public function indexAction()
    {
        // Call your client
        $client = $this->get('csa_guzzle.client.my_client');
        $response = $client->get('/');
        return new StreamResponse($response);
    }
}
```
