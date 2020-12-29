<?php declare(strict_types=1);

namespace OAS\Resolver;

use Psr\Http\Message\UriInterface;

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
    $separator = hasFileScheme($uri) ? DIRECTORY_SEPARATOR : '/';
    $path = resolvePath($uri->getPath(), $context ? $context->getPath() : null, $separator);

    return $uri->withPath($path == $separator ? '' : $path);
}

function resolvePath(string $path, string $context = null, string $segmentSeparator = DIRECTORY_SEPARATOR): string
{
    $pathSegments = array_reduce(
        pathSegments($path),
        function (array $contextPathSegments, string $relativePathSegment): array {
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
        $context ? pathSegments($context) : []
    );

    return $segmentSeparator.\join($segmentSeparator, $pathSegments);
}

function pathSegments(string $path, string $segmentSeparator = DIRECTORY_SEPARATOR): array
{
    return \array_values(
        \array_filter(
            \explode($segmentSeparator, $path),
            fn ($pathSegment) => '' !== $pathSegment
        )
    );
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

function hasFileScheme(UriInterface $uri): bool
{
    $scheme = $uri->getScheme();

    return 'file' == $uri->getScheme() || ('' == $scheme && '' == $uri->getHost());
}

function join(string $pathA, string $pathB, $pathSeparator = DIRECTORY_SEPARATOR): string
{
    return $pathSeparator . \join($pathSeparator,
        \array_merge(
            pathSegments($pathA, $pathSeparator),
            pathSegments($pathB, $pathSeparator)
        )
    );
}

function includes(UriInterface $uriA, UriInterface $uriB): bool
{
    if (((string) $uriA) == ((string) $uriB))
        return true;

    if (((string) $uriA->withFragment('')) != ((string) $uriB->withFragment('')))
        return false;

    $fragmentSegmentsA = pathSegments($uriA->getFragment());
    $fragmentSegmentsB = pathSegments($uriB->getFragment());

    if (\count($fragmentSegmentsB) < \count($fragmentSegmentsA)) {
        return false;
    }

    while(!empty($fragmentSegmentsA)) {
        $fragmentSegmentA =  array_shift($fragmentSegmentsA);
        $fragmentSegmentB =  array_shift($fragmentSegmentsB);

        if ($fragmentSegmentA != $fragmentSegmentB) {
            return false;
        }

    }

    return true;
}

function equals(UriInterface $uriA, UriInterface $uriB): bool
{
    if ($uriA->getFragment() === '/') {
        $uriA = $uriA->withFragment('');
    }

    if ($uriB->getFragment() === '/') {
        $uriB = $uriB->withFragment('');
    }

    return (string) $uriA == (string) $uriB;
}


function jsonPointerEncode(string $jsonPointer): string
{
    return str_replace(['~', '/'], ['~0', '~1'], $jsonPointer);
}

function jsonPointerDecode(string $encodedJsonPointer): string
{
    return str_replace(['~0', '~1'], ['~', '/'], $encodedJsonPointer);
}
