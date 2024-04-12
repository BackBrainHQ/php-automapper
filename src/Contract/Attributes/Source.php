<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class Source
{
    private string $source;

    /**
     * @var string[]
     */
    private array $include = [];

    /**
     * @param string[] $include
     */
    public function __construct(string $source, array $include = [])
    {
        $this->source = $source;
        $this->include = $include;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string[]
     */
    public function getInclude(): array
    {
        return $this->include;
    }
}
