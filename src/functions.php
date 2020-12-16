<?php declare(strict_types=1);

namespace OAS\Resolver;

use Psr\Http\Message\UriInterface;

/**
 * @internal
 *
 * @param array $graph
 * @param array $path
 * @param mixed $replacement
 * @return mixed
 */
function replaceAtPath(array $graph, array $path, $replacement)
{
    if (empty($path)) {
        return $replacement;
    }

    $toReplace = &retrieveByPath($graph, $path);
    $toReplace = $replacement;

    return $graph;
}

/**
 * @internal
 *
 * @param \ArrayAccess|array $graph
 * @param array $path
 * @return array|mixed
 */
function &retrieveByPath(&$graph, array $path)
{
    if (!is_array($graph) && !$graph instanceof \ArrayAccess) {
        throw new \InvalidArgumentException(
            'The first parameter must be of array|\ArrayAccess type'
        );
    }

    $current = &$graph;

    foreach ($path as $pathSegment) {
        if (!array_key_exists($pathSegment, $current)) {
            throw new \RuntimeException(sprintf('Path "%s" does not exist', \join(' -> ', $path)));
        }

        $current = &$current[$pathSegment];
    }

    return $current;
}

function append(array $collection, $element): array
{
    array_push($collection, $element);

    return $collection;
}

/**
 * @internal
 *
 * @param $key
 * @param $array
 * @return bool
 */
function array_key_exists($key, $array)
{
    return $array instanceof \ArrayAccess
        ? $array->offsetExists($key)
        : \array_key_exists($key, $array);
}

function put(array $map, string $key, $value): array
{
    $map[$key] = $value;

    return $map;
}

function dirname(UriInterface $uri): UriInterface
{
    return $uri->withPath(
        \dirname(
            realPath($uri)->getPath()
        )
    );
}

function realPath(UriInterface $uri, UriInterface $context = null): UriInterface
{
    $contextPathSegments = array_reduce(
        pathSegments($uri->getPath()),
        function (array $contextPathSegments, string $relativePathSegment): array {
            // current directory
            if ('..' == $relativePathSegment) {
                if (empty($contextPathSegments))  {
                    throw new \InvalidArgumentException('Invalid relative path: out of context boundary');
                }

                array_pop($contextPathSegments);
            } else if ('.' != $relativePathSegment) {
                array_push($contextPathSegments, $relativePathSegment);
            }

            return $contextPathSegments;
        },
        $context ? pathSegments($context->getPath()) : []
    );

    return $uri->withPath(
        DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $contextPathSegments)
    );
}

function pathSegments(string $path): array
{
    if (DIRECTORY_SEPARATOR === ($path[0] ?? null)) {
        $path = substr($path, 1);
    }

    if (DIRECTORY_SEPARATOR === ($path[strlen($path) - 1] ?? null)) {
        $path = substr($path, 0, -1);
    }

    return '' === $path ? [] : explode(DIRECTORY_SEPARATOR, $path);
}

function withoutFragment(UriInterface $uri): UriInterface
{
    return $uri->withFragment('');
}

function hasFragment(UriInterface $uri): bool
{
    return !empty($uri->getFragment());
}

function isAbsolute(UriInterface $uri): bool
{
    $path = $uri->getPath();

    return !empty($path) && '/' == $path[0];
}

function hasPath(UriInterface $uri): bool
{
    $path = $uri->getPath();

    return !empty($path);
}

function hasHost(UriInterface $uri): bool
{
    return !is_null($uri->getHost());
}

function hasScheme(UriInterface $uri): bool
{
    return !is_null($uri->getScheme());
}
