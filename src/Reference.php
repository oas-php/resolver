<?php declare(strict_types=1);

namespace OAS\Resolver;

class Reference implements \ArrayAccess
{
    private $ref;

    /**
     * @var array|Reference|null
     */
    private $resolved;

    /**
     * @param string $ref
     * @param Reference|array|null $resolved
     */
    private function __construct(string $ref, $resolved = null)
    {
        $this->ref = $ref;
        $this->resolved = $resolved;
    }

    public static function create(string $ref, array $resolved)
    {
        return new self($ref, $resolved);
    }

    public static function createDeferred(string $ref, callable $resolver): self
    {
        $self = new self($ref);
        $self->resolved = call_user_func($resolver, $self);

        return $self;
    }

    public function getRef(): string
    {
        return $this->ref;
    }

    /**
     * @return Reference|array|null
     */
    public function getResolved()
    {
        return $this->resolved;
    }

    public function isEmbedded(): bool
    {
        return '#' === $this->ref[0];
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->resolved);
    }

    public function offsetGet($offset)
    {
        return $this->resolved[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException();
    }

    public function offsetUnset($offset)
    {
        throw new \RuntimeException();
    }
}
