<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/../vendor/autoload.php';

use OAS\Resolver\Resolver;
use OAS\Resolver\Factory\EncoderFactory;

$scheme = getenv('OAS_SCHEME');
$host = getenv('OAS_HOST');
$basePath = getenv('OAS_BASEPATH');

// by default 'https://oas-php.github.io/sample/theater/openapi.json'
$uri = sprintf(
    '%s://%s%s/theater/openapi.json',
    (false !== $scheme) ? $scheme : 'https',
    (false !== $host) ? $host : 'oas-php.github.io',
    (false !== $basePath) ? $basePath : '/sample'
);

$resolved = (new Resolver())->resolve($uri);
