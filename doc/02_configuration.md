## Configuration

1. **Zero configuration**

    The library gateway class [OAS\Resolver](../src/Resolver.php) accepts one parameter of [OAS\Configuration](../src/Configuration.php) type. If you installed [suggested packages](./01_installation.md#suggested-packages) this parameter is optional and, if missing, the default configuration is used. 

    ```PHP
    use OAS\Resolver;

    // zero-configuration
    $resolve = new Resolver();
    ```

2. **Tweaks**

    If you wish, you can instantiate [OAS\Configuration](../src/Configuration.php) on your own.

    ```PHP
   use OAS\Configuration;
   use OAS\Resolver;
   
   /** @var $uriFactory Psr\Http\Message\UriFactoryInterface */
    $uriFactory = ...
    /** @var $decoder OAS\Resolver\DecoderInterface $decoder */
    $decoder = ...
    /** @var  $cache Psr\SimpleCache\CacheInterface $cache */
    $cache = ...
    
    $configuration = new Configuration($uriFactory, $decoder, $cache);
    $resolver = new Resolver($configuration);
    ```

* **UriFactory**

    [URIs](https://www.php-fig.org/psr/psr-7/#35-psrhttpmessageuriinterface) are heavily used under the hood for convenience and code clarity. It's well-known PSR standard, widely implemented so there are many options to choose from. If you installed suggested package you can skip this parameter altogether (by passing `null`).
    
     
* **Decoder**

    The resolver needs a decoder to transform documents into PHP terms. You can go with default one which supports **json** (if `ext-json` is available) and **yaml** (if `symfony/yaml` package is installed) formats:
    
    ```PHP
    use OAS\Resolver\Factory\DecoderFactory;
  
    $decoder = DecoderFactory::create();
    ```  
    You skip this parameter (by passing `null`) if you alright with the default option.
    
* **Cache**
    
    Although optional, caching is recommended for documents with multiple `$ref's` to the same external resource.
