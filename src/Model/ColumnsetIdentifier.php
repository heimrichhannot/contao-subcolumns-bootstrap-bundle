<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Model;

class ColumnsetIdentifier
{
    private string $source;
    private array $params;

    public function __construct(string $source, string ...$params)
    {
        $this->source = $source;
        $this->params = $params;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param self|string $identifier
     * @return self|null
     */
    public static function deconstruct($identifier): ?self
    {
        if ($identifier instanceof self) {
            return $identifier;
        }

        $exploded = explode('.', $identifier, 3);

        if (count($exploded) !== 3) {
            return null;
        }

        return new static($exploded[0], $exploded[1], $exploded[2]);
    }

    public function __toString()
    {
        return $this->source . '.' . implode('.', $this->params);
    }

}
