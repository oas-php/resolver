<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use OAS\Resolver\Factory\EncoderFactory;
use OAS\Resolver\Resolver;

class EncoderTest extends TestCase
{
    const EXPECTED_JSON = <<<JSON
    {
      "openapi": "3.0.0",
      "info": {
        "version": "1.0.0",
        "title": "Movie Theater",
        "license": {
          "name": "MIT"
        }
      },
      "servers": [
        {
          "url": "http://localhost/openapi.json"
        }
      ],
      "paths": {
        "/shows": {
          "get": {
            "summary": "Cinema's repertoire",
            "operationId": "shows",
            "responses": {
              "200": {
                "description": "Expected response to a valid request",
                "content": {
                  "application/json": {
                    "schema": {
                      "type": "object",
                      "properties": {
                        "data": {
                          "type": "array",
                          "items": {
                            "\$ref": "#components/schemas/Show"
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      },
      "components": {
        "schemas": {
          "Show": {
            "type": "object",
            "properties": {
              "movie": {
                "type": "object",
                "properties": {
                  "title": {
                    "type": "string"
                  },
                  "genre": {
                    "type": "string"
                  },
                  "year": {
                    "type": "integer"
                  },
                  "director": {
                    "allOf": [
                      {
                        "type": "object",
                        "properties": {
                          "firstName": {
                            "type": "string"
                          },
                          "lastName": {
                            "type": "string"
                          },
                          "yearOfBirth": {
                            "type": "integer"
                          }
                        },
                        "required": [
                          "firstName",
                          "lastName",
                          "yearOfBirth"
                        ],
                        "example": {
                          "firstName": "Jack",
                          "lastName": "Nicholson",
                          "yearOfBirth": 1937
                        }
                      },
                      {
                        "type": "object",
                        "properties": {
                          "directed": {
                            "type": "array",
                            "items": {
                              "\$ref": "./components/schemas/movie.json"
                            }
                          }
                        }
                      }
                    ]
                  },
                  "actors": {
                    "type": "array",
                    "items": {
                      "allOf": [
                        {
                          "type": "object",
                          "properties": {
                            "firstName": {
                              "type": "string"
                            },
                            "lastName": {
                              "type": "string"
                            },
                            "yearOfBirth": {
                              "type": "integer"
                            }
                          },
                          "required": [
                            "firstName",
                            "lastName",
                            "yearOfBirth"
                          ],
                          "example": {
                            "firstName": "Jack",
                            "lastName": "Nicholson",
                            "yearOfBirth": 1937
                          }
                        },
                        {
                          "type": "object",
                          "properties": {
                            "actedId": {
                              "type": "array",
                              "items": {
                                "\$ref": "./components/schemas/movie.json"
                              }
                            }
                          }
                        }
                      ]
                    }
                  }
                },
                "example": {
                  "title": "The Shining",
                  "year": 1980,
                  "director": {
                    "firstName": "Stanley",
                    "lastName": "Kubrick",
                    "yearOfBirth": 1928
                  },
                  "actors": [
                    {
                      "firstName": "Jack",
                      "lastName": "Nicholson",
                      "yearOfBirth": 1937
                    },
                    {
                      "firstName": "Shelley",
                      "lastName": "Duvall",
                      "yearOfBirth": 1949
                    }
                  ]
                }
              },
              "time": {
                "type": "string",
                "format": "datetime"
              },
              "hall": {
                "type": "string"
              }
            }
          }
        }
      }
    }

    JSON;

    /**
     * @test
     */
    public function itEncodesResolvedDocumentToJSON()
    {
        $jsonEncoded = EncoderFactory::create()->encode(
            (new Resolver())->resolve('http://localhost/theater/openapi.json'), 'json'
        );

        $this->assertJsonStringEqualsJsonString(self::EXPECTED_JSON, $jsonEncoded);
    }
}
