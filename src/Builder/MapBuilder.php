<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Builder;

use Backbrain\Automapper\Contract\Builder\MapBuilderInterface;
use Backbrain\Automapper\Contract\Builder\ProfileBuilderInterface;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\MappingActionInterface;
use Backbrain\Automapper\Contract\MemberInterface;
use Backbrain\Automapper\Contract\NamingConventionInterface;
use Backbrain\Automapper\Contract\ProfileInterface;
use Backbrain\Automapper\Contract\TypeConverterInterface;
use Backbrain\Automapper\Contract\TypeFactoryInterface;
use Backbrain\Automapper\Helper\Func;
use Backbrain\Automapper\Model\Map;

class MapBuilder implements MapBuilderInterface
{
    private ?TypeConverterInterface $typeConverter = null;

    private ?TypeFactoryInterface $typeFactory = null;

    private ?NamingConventionInterface $sourceMemberNamingConvention = null;

    private ?NamingConventionInterface $destinationMemberNamingConvention = null;

    private ?MappingActionInterface $beforeMap = null;

    private ?MappingActionInterface $afterMap = null;

    /**
     * @var MemberInterface[]
     */
    private array $members = [];

    private ?string $mappedBy = null;

    /**
     * @var MapInterface[]
     */
    private array $includeMaps = [];

    private ?MapInterface $includeBaseMap = null;

    public function __construct(
        private readonly ProfileBuilderInterface $mapperConfigurationBuilder,
        private readonly string $sourceType,
        private readonly string $destinationType
    ) {
    }

    public function build(): MapInterface
    {
        return new Map(
            sourceType: $this->sourceType,
            destinationType: $this->destinationType,
            members: $this->members,
            mappedBy: $this->mappedBy,
            includeMaps: $this->includeMaps,
            includeBaseMap: $this->includeBaseMap,
            typeConverter: $this->typeConverter,
            typeFactory: $this->typeFactory,
            sourceMemberNamingConvention: $this->sourceMemberNamingConvention,
            destinationMemberNamingConvention: $this->destinationMemberNamingConvention,
            beforeMap: $this->beforeMap,
            afterMap: $this->afterMap,
        );
    }

    public function sourceMemberNamingConvention(string|NamingConventionInterface $namingConvention): static
    {
        if (is_string($namingConvention)) {
            if (!class_exists($namingConvention)) {
                throw new \InvalidArgumentException(sprintf('The naming convention class "%s" does not exist', $namingConvention));
            }

            $namingConvention = new $namingConvention();
        }

        if (!$namingConvention instanceof NamingConventionInterface) {
            throw new \InvalidArgumentException(sprintf('The naming convention must be an instance of "%s", got "%s"', NamingConventionInterface::class, get_debug_type($namingConvention)));
        }

        $this->sourceMemberNamingConvention = $namingConvention;

        return $this;
    }

    public function destinationMemberNamingConvention(string|NamingConventionInterface $namingConvention): static
    {
        if (is_string($namingConvention)) {
            if (!class_exists($namingConvention)) {
                throw new \InvalidArgumentException(sprintf('The naming convention class "%s" does not exist', $namingConvention));
            }

            $namingConvention = new $namingConvention();
        }

        if (!$namingConvention instanceof NamingConventionInterface) {
            throw new \InvalidArgumentException(sprintf('The naming convention must be an instance of "%s", got "%s"', NamingConventionInterface::class, get_debug_type($namingConvention)));
        }

        $this->destinationMemberNamingConvention = $namingConvention;

        return $this;
    }

    public function createMap(string $sourceType, string $destinationType): MapBuilderInterface
    {
        return $this->mapperConfigurationBuilder->createMap($sourceType, $destinationType);
    }

    public function addProfile(ProfileInterface $profile): ProfileBuilderInterface
    {
        return $this->mapperConfigurationBuilder->addProfile($profile);
    }

    public function forMember(string $destinationProperty, callable $optFn): static
    {
        $builder = new MemberOptionsBuilder($destinationProperty);
        $optFn($builder);

        // override duplicate member definition with the current
        // so the last definition wins
        foreach ($this->members as $index => $member) {
            if ($member->getDestinationProperty() === $destinationProperty) {
                $this->members[$index] = $builder->build();

                return $this;
            }
        }

        $this->members[] = $builder->build();

        return $this;
    }

    public function constructUsing(TypeFactoryInterface|callable $factory): static
    {
        $this->typeFactory = Func::typeFactoryFromFn($factory);

        return $this;
    }

    public function mappedBy(string $destinationType): static
    {
        $this->mappedBy = $destinationType;

        return $this;
    }

    public function convertUsing(TypeConverterInterface|callable $converter): static
    {
        $this->typeConverter = Func::typeConverterFromFn($converter);

        return $this;
    }

    public function beforeMap(callable|MappingActionInterface $action): static
    {
        $this->beforeMap = Func::mappingActionFromFn($action);

        return $this;
    }

    public function afterMap(callable|MappingActionInterface $action): static
    {
        $this->afterMap = Func::mappingActionFromFn($action);

        return $this;
    }

    public function include(string $sourceType, string $destinationType): static
    {
        foreach ($this->includeMaps as $map) {
            if ($map->getSourceType() === $sourceType && $map->getDestinationType() === $destinationType) {
                return $this;
            }
        }

        $this->includeMaps[] = new Map(
            sourceType: $sourceType,
            destinationType: $destinationType
        );

        return $this;
    }

    public function includeBase(string $sourceType, string $destinationType): static
    {
        $this->includeBaseMap = new Map(
            sourceType: $sourceType,
            destinationType: $destinationType
        );

        return $this;
    }
}
