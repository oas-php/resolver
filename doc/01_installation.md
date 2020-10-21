## Installation

The `oas-php/resolver` is a [composer](https://getcomposer.org) package. Install it by running:

```
composer req oas-php/resolver
```


#### Suggested packages
There a few more packages to install, unless you want to provide your own implementation. 



* for working with [URIs](https://www.php-fig.org/psr/psr-7/#35-psrhttpmessageuriinterface)

    ```
    composer req guzzlehttp/psr7:^1.1
    ```

* for caching (optional)

    ```
    composer req cache/array-adapter:^1.0
    ```

* for encoding/decoding YAML documents (optional)

    ```
    composer req "symfony/yaml:^5.1@dev"
    ```                        


