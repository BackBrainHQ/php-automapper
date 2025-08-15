<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Exceptions;

use Backbrain\Automapper\Context\MappingContext;

class ContextAwareMapperException extends MapperException
{
    public function __construct(
        private readonly MappingContext $context,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getContext(): MappingContext
    {
        return $this->context;
    }

    public static function fromMapperException(MapperException $exception, MappingContext $context): self
    {
        // If it's already a ContextAwareMapperException, return it as-is
        if ($exception instanceof self) {
            return $exception;
        }

        $message = $exception->getMessage();
        if (!str_contains($message, 'Context: ')) {
            $message = sprintf('%s Context: %s', $message, (string) $context);
        }

        // Create a new ContextAwareMapperException from the original and attach context
        return new self($context, $message, $exception->getCode(), $exception->getPrevious());
    }
}
