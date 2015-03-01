Creating a service for your client
==================================

There are two ways for creating a service for your client:

* Using the semantic configuration (Beginners)
* Creating your own service, using the provided factory (Advanced users)

Creating a client using semantic configuration
----------------------------------------------

Simply write the following code:

```yml
csa_guzzle:
    clients:
        github_api:
            config:
                base_url: https://api.github.com
                headers:
                    Accept: application/vnd.github.v3+json
```

The previous code will create a new service, called `csa_guzzle.client.github_api`.

Creating a client using the provided factory service
----------------------------------------------------

Simply create a service as follows:

```xml
<service
        id="acme.client"
        class="%acme.client.class%"
        factory-service="csa_guzzle.client_factory"
        factory-method="create">
    <!-- An array of configuration values -->
</service>
```
