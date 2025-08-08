<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract;

interface ResolutionContextInterface
{
    /**
     * Returns the AutoMapper instance associated with this resolution context.
     *
     * This instance is responsible for performing the mapping operations
     * and may contain configuration and state relevant to the mapping process.
     */
    public function getAutoMapper(): ?AutoMapperInterface;

    /**
     * Returns the source object being mapped.
     *
     * This is the original object or data structure from which properties
     * are being mapped to a destination object.
     */
    public function getSource(): mixed;

    /**
     * Returns the destination property definition being mapped.
     */
    public function getMember(): ?MemberInterface;

    /**
     * Returns the Map associated with this resolution context.
     *
     * This map contains the configuration and rules for how to map
     * properties from the source to the destination.
     */
    public function getMap(): ?MapInterface;

    /**
     * Returns an ArrayAccess object for storing variables.
     *
     * This allows for dynamic storage and retrieval of context-specific variables
     * that can be used during the mapping process.
     *
     * @return \ArrayAccess<string, mixed>
     */
    public function getVars(): \ArrayAccess;
}
