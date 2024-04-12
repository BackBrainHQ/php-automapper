<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FromExpr
{
    private string $source;

    private string $expr;

    public function __construct(string $source, string $expr)
    {
        $this->source = $source;
        $this->expr = $expr;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getExpr(): string
    {
        return $this->expr;
    }
}
