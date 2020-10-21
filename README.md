# oas-php / **resolver**
### JSON Reference resolver
> An OpenAPI document MAY be made up of a single document or be divided into multiple, connected parts at the discretion of the user. In the latter case, $ref fields MUST be used in the specification to reference those parts as follows from the JSON Schema definitions.

This package is part of [oas-php project](https://github.com/oas-php) and is used internally by `oas-php/document` to resolve `$ref's`. However, it's not confined just to [OAS](https://github.com/OAI/OpenAPI-Specification). In fact, it might be used with any document which uses [JSON References](https://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03). 

#### installation
The `oas-php/resolver` is a composer package. Install it by running:

```
composer req oas-php/resolver
```
The package dependency list is short as possible. However, I suggest installing a few [optional packages](./doc/01_installation.md#suggested-packages), so you don't need to configure anything yourself.

#### usage

If you install [suggested packages](./doc/01_installation.md#suggested-packages), the simplest use case may look like:
```PHP
$resolved = (new Resolver)->resolve('https://oas-php.github.io/sample/theater/openapi.json');

// encode resolved document as JSON 
echo EncoderFactory::create()->encode($resolved, 'json');
```

See [documentation](./doc/README.md) to learn more.

#### license
MIT
