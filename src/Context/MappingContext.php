<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Context;

use Backbrain\Automapper\Contract\MapInterface;

/**
 * MappingContext tracks the recursive mapping process for debugging.
 *
 * It is immutable; mutation methods return a new instance with updated state.
 */
final class MappingContext
{
    /**
     * @param list<string> $pathSegments
     * @param list<string> $destTypes
     * @param list<string> $steps
     */
    private function __construct(
        private readonly string $sourceType,
        private readonly array $destTypes,
        private readonly array $pathSegments = [],
        private readonly ?string $appliedMapId = null,
        private readonly array $steps = [],
    ) {
    }

    /**
     * @param list<string> $destTypes
     */
    public function withDestTypes(array $destTypes): self
    {
        return new self($this->sourceType, array_values(array_map(fn ($t) => ltrim($t, '\\'), $destTypes)), $this->pathSegments, $this->appliedMapId, $this->steps);
    }

    public static function root(mixed $source, string $destinationType): self
    {
        $srcType = is_object($source) ? ltrim(get_class($source), '\\') : get_debug_type($source);

        return new self($srcType, [ltrim($destinationType, '\\')]);
    }

    public function withAppliedMap(?MapInterface $map): self
    {
        if (null === $map) {
            return $this;
        }
        $id = sprintf('%s => %s', ltrim($map->getSourceType(), '\\'), ltrim($map->getDestinationType(), '\\'));

        return new self($this->sourceType, $this->destTypes, $this->pathSegments, $id, $this->steps);
    }

    /**
     * @param list<string> $destTypes
     */
    public function withProperty(string $segment, ?string $sourceType = null, array $destTypes = []): self
    {
        $newSourceType = $sourceType ?? $this->sourceType;
        $newDestTypes = !empty($destTypes) ? array_values(array_map(fn ($t) => ltrim($t, '\\'), $destTypes)) : $this->destTypes;

        return new self($newSourceType, $newDestTypes, [...$this->pathSegments, $segment], $this->appliedMapId, $this->steps);
    }

    public function withStep(string $step): self
    {
        return new self($this->sourceType, $this->destTypes, $this->pathSegments, $this->appliedMapId, [...$this->steps, $step]);
    }

    /**
     * @return list<string>
     */
    public function getPathSegments(): array
    {
        return $this->pathSegments;
    }

    public function getPath(): string
    {
        return implode('.', $this->pathSegments);
    }

    public function getDepth(): int
    {
        return count($this->pathSegments);
    }

    /**
     * @return list<string>
     */
    public function getDestTypes(): array
    {
        return $this->destTypes;
    }

    public function getSourceType(): string
    {
        return $this->sourceType;
    }

    public function getAppliedMapId(): ?string
    {
        return $this->appliedMapId;
    }

    /**
     * @return list<string>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    public function __toString(): string
    {
        $ctx = [
            'path' => '' !== $this->getPath() ? $this->getPath() : '(root)',
            'depth' => (string) $this->getDepth(),
            'source' => $this->sourceType,
            'dest' => implode('|', $this->destTypes),
        ];
        if (null !== $this->appliedMapId) {
            $ctx['map'] = $this->appliedMapId;
        }
        if (!empty($this->steps)) {
            $ctx['steps'] = implode(' -> ', $this->steps);
        }
        // Build a compact key:value string
        $parts = [];
        foreach ($ctx as $k => $v) {
            $parts[] = sprintf('%s=%s', $k, $v);
        }

        return '{'.implode(', ', $parts).'}';
    }
}
