UPGRADE FROM 1.0 to 1.1
=======================

Known Backwards-Compatibility Breaks
------------------------------------

* If you use the `Csa\Bundle\GuzzleBundle\Factory\Client`, the class was removed as it is no longer needed.

  You should now use the base `GuzzleHttp\Client` class, or your own class, extending Guzzle's class.
* If you registered event subscribers using the compiler pass, you now need to give it an alias.

Before:

```xml
<container>
    <services>
        <service id="acme_demo.subscriber.custom" class="Acme\DemoBundle\Guzzle\Subscriber\CustomSubscriber">
            <tag name="csa_guzzle.subscriber" />
        </service>
    </services>
</container>
```

After:


```xml
<container>
    <services>
        <service id="acme_demo.subscriber.custom" class="Acme\DemoBundle\Guzzle\Subscriber\CustomSubscriber">
            <tag name="csa_guzzle.subscriber" alias="acme_custom" />
        </service>
    </services>
</container>
```
