## Usage

Assuming you installed [suggested packages](./01_installation.md#suggested-packages), the simplest use case may look like:
```PHP
$resolved = (new Resolver)->resolve('https://oas-php.github.io/sample/theater/openapi.json');

// encode resolved document as JSON
echo EncoderFactory::create()->encode($resolved, 'json');
```


However, if your need to do something else than encoding resolved document as JSON (check [decoders](./02_configuration.md) for other formats) you might be interested in `$resolved` variable. 


`Resolver::resolve` returns a map where all `$ref`'s nodes are replaced by instances of [OAS\Reference](../src/Reference.php) class objects which represent resolved documents. For example, for the following documents:

```json
// https://oas-php.github.io/sample/theater/components/schemas/show.json

{
   "type":"object",
   "properties":{
      "movie":{
         "$ref":"https://oas-php.github.io/sample/library/components/schemas/movie.json"
      },
      "time":{
         "type":"string",
         "format":"datetime"
      },
      "hall":{
         "type":"string"
      }
   }
}


// https://oas-php.github.io/sample/library/components/schemas/movie.json

{
   "type":"object",
   "properties":{
      "title":{
         "type":"string"
      },
      "genre":{
         "type":"string"
      },
      "year":{
         "type":"integer"
      }
   }
}
```

`var_dump($resolved)` outputs

```
array(2) {
  'type' =>
  string(6) "object"
  'properties' =>
  array(3) {
    'movie' =>
    class OAS\Resolver\Reference#16 (2) {
      private $ref =>
      string(70) "https://oas-php.github.io/sample/library/components/schemas/movie.json"
      private $resolved =>
      array(2) {
        'type' =>
        string(6) "object"
        'properties' =>
        array(3) {
            'title' => 
            array(1) {
                'type' => 
                string(6) => "string"
            }
            'genre' => 
            array(1) {
                'type' => 
                string(6) => "string"
            }
            'year' => 
            array(1) {
                'type' => 
                string(7) => "integer"
            }
        }        
      }
    }
    'time' =>
    array(2) {
      'type' =>
      string(6) "string"
      'format' =>
      string(8) "datetime"
    }
    'hall' =>
    array(1) {
      'type' =>
      string(6) "string"
    }
  }
}

```

For your convenience [OAS\Reference](../src/Reference.php) implements [ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php):

```php
assert($resolved['properties']['movie']['properties']['title']['type'] === 'string');
```
