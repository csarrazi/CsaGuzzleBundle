Using specific serializer
=========================

By default Guzzle allows you to set a specific variable in the configuration of a request.
It will be automatically be transformed to JSON with the ```json_encode``` method of PHP.

If you want to send complex objects with different views or receive some data in the right format, you have to
use a serializer.

There is two well known serializer:
- the (serializer component)[http://symfony.com/doc/current/components/serializer.html] of Symfony
- the (JMS Serializer)[http://jmsyst.com/libs/serializer]

To use a serializer with this bundle, you will have to use an adapter. By default an adapter is provided for the
serializer component and an other one for the JMS Serializer.

Configuration
-------------

You can set a serializer adapter for all your clients:

```yml
csa_guzzle:
    serializer:
        adapter: csa_guzzle.serializer.adapter.jms
```

Or for a specific client:

```yml
csa_guzzle:
    clients:
        github_api:
            serializer:
                adapter: csa_guzzle.serializer.adapter.symfony
```

Usage
-----

```php
public function indexAction(Request $request)
{
    $client = $this->get('csa_guzzle.client.github_api');
    $data = $client->requestAsync(
        'GET',
        '/api/test',
        [
            'json' => ['foo' => 'bar'],
            'serialization' => ['type' => 'MyClass']
        ]
    );
}
```

In this example, ```$data``` will contain an object of type MyClass.

DIY
---

You can use any serializer you want, you just have to setup an adapter. Just create a new service that implements 
SerializerAdapterInterface and use his id in the configuration.

```php
use Csa\Bundle\GuzzleBundle\Serializer\SerializerAdapterInterface;

class MySerializerAdapter implements SerializerAdapterInterface
{
    [...]
    
    public function serialize($data, $format, $context = null);
    {
        // Call the serializer to serialize
    }
    
    [...]
    
    public function deserialize($data, $type, $format, $context = null);
    {
        // Call the serializer to deserialize
    }
}
```
