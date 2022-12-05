## Usage

### [WIP]

Assuming you installed [suggested packages](./01_installation.md#suggested-packages), the simplest use case may look like:
```PHP
$resolved = (new Resolver)->resolve('https://oas-php.github.io/sample/theater/openapi.json');

// encode resolved document as JSON
echo json_encode($resolved, JSON_PRETTY_PRINT);
```


