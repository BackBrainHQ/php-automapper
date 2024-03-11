<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract;

interface AutoMapperInterface
{
    /**
     * Maps a source object to a destination type.
     *
     * @template T of object
     *
     * @param class-string<T> $destinationType the destination type
     *
     * @return T
     */
    public function map(object $source, string $destinationType): object;

    /**
     * Maps a source iterable to a destination type.
     *
     * @param iterable<mixed, mixed> $source
     *
     * @return iterable<mixed, mixed>
     */
    public function mapIterable(iterable $source, string $destinationType): iterable;

    /**
     * Maps a source object to a destination object.
     */
    public function mutate(object $source, object $destination): void;
}
