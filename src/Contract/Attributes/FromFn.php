<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FromFn
{
    private string $source;

    /**
     * @var string[]
     */
    private array $func;

    /**
     * @param string[] $func
     */
    public function __construct(string $source, array $func)
    {
        $this->source = $source;
        $this->func = $func;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string[]
     */
    public function getFunc(): array
    {
        return $this->func;
    }
}
