<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Metadata;

use Backbrain\Automapper\Contract\Metadata\DirectoryMetadataProviderInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal This class is not part of the public API and may change at any time!
 */
class DirectoryMetadataProvider implements DirectoryMetadataProviderInterface
{
    /**
     * This method is used to scan the given path recursively for PHP classes and return their fully qualified class names.
     *
     * @return class-string[]
     */
    public function scanPath(string ...$path): array
    {
        $parserFactory = new ParserFactory();

        // we have to use an if statement as conditional assignment does not work with 2 phpstan problems
        // @phpstan-ignore-next-line
        if (method_exists($parserFactory, 'createForNewestSupportedVersion')) {
            $parser = $parserFactory->createForNewestSupportedVersion();
        } else {
            // @phpstan-ignore-next-line
            $parser = $parserFactory->create(ParserFactory::PREFER_PHP7);
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());

        $finder = new Finder();
        $finder->files()->in($path)->name('*.php');

        $classes = [];
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $ast = $parser->parse($file->getContents());

            $classNameVisitor = new class extends NodeVisitorAbstract {
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
            };

            $traverser->addVisitor($classNameVisitor);

            $traverser->traverse($ast);

            foreach ($classNameVisitor->getClassNames() as $className) {
                if (!class_exists($className)) {
                    continue;
                }

                $classes[] = $className;
            }
        }

        return $classes;
    }
}
