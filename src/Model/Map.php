<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Model;

use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\MappingActionInterface;
use Backbrain\Automapper\Contract\MemberInterface;
use Backbrain\Automapper\Contract\NamingConventionInterface;
use Backbrain\Automapper\Contract\TypeConverterInterface;
use Backbrain\Automapper\Contract\TypeFactoryInterface;

class Map implements MapInterface
{
    /**
     * @param MemberInterface[] $members
     * @param MapInterface[]    $includeMaps
     */
    public function __construct(
        private string $sourceType,
        private string $destinationType,
        private array $members = [],
        private ?string $as = null,
        private array $includeMaps = [],
        private ?MapInterface $includeBaseMap = null,
        private ?TypeConverterInterface $typeConverter = null,
        private ?TypeFactoryInterface $typeFactory = null,
        private ?NamingConventionInterface $sourceMemberNamingConvention = null,
        private ?NamingConventionInterface $destinationMemberNamingConvention = null,
        private ?MappingActionInterface $beforeMap = null,
        private ?MappingActionInterface $afterMap = null,
    ) {
    }

    public static function from(MapInterface $map): self
    {
        return new self(
            sourceType: $map->getSourceType(),
            destinationType: $map->getDestinationType(),
            members: $map->getMembers(),
            as: $map->getAs(),
            includeMaps: $map->getIncludeMaps(),
            includeBaseMap: $map->getIncludeBaseMap(),
            typeConverter: $map->getTypeConverter(),
            typeFactory: $map->getTypeFactory(),
            sourceMemberNamingConvention: $map->getSourceMemberNamingConvention(),
            destinationMemberNamingConvention: $map->getDestinationMemberNamingConvention(),
            beforeMap: $map->getBeforeMap(),
            afterMap: $map->getAfterMap(),
        );
    }

    /**
     * Merge settings from $from into $into and return a new Map
     * Existing settings from $from have precedence and will overwrite existing values in $into.
     *
     * @param MapInterface $from Settings to merge from
     * @param MapInterface $into Settings to merge into
     */
    public static function merge(MapInterface $from, MapInterface $into): self
    {
        $map = self::from($into);
        $map->as = $from->getAs() ?? $into->getAs();
        $map->typeConverter = $from->getTypeConverter() ?? $into->getTypeConverter();
        $map->typeFactory = $from->getTypeFactory() ?? $into->getTypeFactory();
        $map->sourceMemberNamingConvention = $from->getSourceMemberNamingConvention() ?? $into->getSourceMemberNamingConvention();
        $map->destinationMemberNamingConvention = $from->getDestinationMemberNamingConvention() ?? $into->getDestinationMemberNamingConvention();

        return self::mergeMembers($from, $map);
    }

    /**
     * Merge members from $from into $into and return a new Map.
     * Existing members from $from have precedence and will overwrite existing values in $into.
     *
     * @param MapInterface $from Settings to merge from
     * @param MapInterface $into Settings to merge into
     */
    public static function mergeMembers(MapInterface $from, MapInterface $into): self
    {
        $map = self::from($into);

        $props = [];
        $members = [];
        foreach ([...$from->getMembers(), ...$into->getMembers()] as $member) {
            if (in_array($member->getDestinationProperty(), $props)) {
                continue;
            }

            $props[] = $member->getDestinationProperty();
            $members[] = $member;
        }

        return $map->withMembers(...$members);
    }

    public function withMembers(MemberInterface ...$members): self
    {
        $map = self::from($this);
        $map->members = $members;

        return $map;
    }

    public function withTypeConverter(TypeConverterInterface $typeConverter): self
    {
        $map = self::from($this);
        $map->typeConverter = $typeConverter;

        return $map;
    }

    public function withTypeFactory(?TypeFactoryInterface $typeFactory): self
    {
        $map = self::from($this);
        $map->typeFactory = $typeFactory;

        return $map;
    }

    public function withMappedBy(?string $mappedBy): self
    {
        $map = self::from($this);
        $map->as = $mappedBy;

        return $map;
    }

    public function withSourceMemberNamingConvention(NamingConventionInterface $namingConvention): self
    {
        $map = self::from($this);
        $map->sourceMemberNamingConvention = $namingConvention;

        return $map;
    }

    public function withDestinationMemberNamingConvention(NamingConventionInterface $namingConvention): self
    {
        $map = self::from($this);
        $map->destinationMemberNamingConvention = $namingConvention;

        return $map;
    }

    public function withDestinationType(string $destinationType): self
    {
        $map = self::from($this);
        $map->destinationType = $destinationType;

        return $map;
    }

    public function withSourceType(string $sourceType): self
    {
        $map = self::from($this);
        $map->sourceType = $sourceType;

        return $map;
    }

    public function withBeforeMap(?MappingActionInterface $action): self
    {
        $map = self::from($this);
        $map->beforeMap = $action;

        return $map;
    }

    public function withAfterMap(?MappingActionInterface $action): self
    {
        $map = self::from($this);
        $map->afterMap = $action;

        return $map;
    }

    /**
     * @return MemberInterface[]
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    public function getTypeConverter(): ?TypeConverterInterface
    {
        return $this->typeConverter;
    }

    public function getTypeFactory(): ?TypeFactoryInterface
    {
        return $this->typeFactory;
    }

    public function getSourceType(): string
    {
        return $this->sourceType;
    }

    public function getDestinationType(): string
    {
        return $this->destinationType;
    }

    public function getSourceMemberNamingConvention(): ?NamingConventionInterface
    {
        return $this->sourceMemberNamingConvention;
    }

    public function getDestinationMemberNamingConvention(): ?NamingConventionInterface
    {
        return $this->destinationMemberNamingConvention;
    }

    public function getAs(): ?string
    {
        return $this->as;
    }

    public function getBeforeMap(): ?MappingActionInterface
    {
        return $this->beforeMap;
    }

    public function getAfterMap(): ?MappingActionInterface
    {
        return $this->afterMap;
    }

    public function getIncludeMaps(): array
    {
        return $this->includeMaps;
    }

    public function getIncludeBaseMap(): ?MapInterface
    {
        return $this->includeBaseMap;
    }
}
