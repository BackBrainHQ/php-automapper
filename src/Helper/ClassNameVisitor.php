<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Helper;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;

class ClassNameVisitor extends NodeVisitorAbstract
{
    /**
     * @var class-string[]
     */
    private array $classNames = [];

    public function enterNode(Node $node): int|Node|null
    {
        if ($node instanceof Class_) {
            if (null === $node->namespacedName) {
                return null;
            }

            // @phpstan-ignore-next-line
            $this->classNames[] = $node->namespacedName->toString();
        }

        return null;
    }

    /**
     * @return class-string[]
     */
    public function getClassNames(): array
    {
        return $this->classNames;
    }
}
