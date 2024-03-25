<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract;

interface MapInterface
{
    /**
     * @return array<string, MemberInterface>
     */
    public function getMembers(): array;

    public function getTypeConverter(): ?TypeConverterInterface;

    public function getTypeFactory(): ?TypeFactoryInterface;

    public function getAs(): ?string;

    public function getSourceType(): string;

    public function getDestinationType(): string;

    public function getSourceMemberNamingConvention(): ?NamingConventionInterface;

    public function getDestinationMemberNamingConvention(): ?NamingConventionInterface;

    public function getBeforeMap(): ?MappingActionInterface;

    public function getAfterMap(): ?MappingActionInterface;

    /**
     * @return MapInterface[]
     */
    public function getIncludeMaps(): array;

    public function getIncludeBaseMap(): ?MapInterface;
}
