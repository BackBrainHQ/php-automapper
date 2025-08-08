<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract;

interface ResolutionContextProviderInterface
{
    /**
     * Creates a new ResolutionContextInterface instance.
     *
     * @param AutoMapperInterface              $autoMapper the AutoMapper instance to use
     * @param MapInterface|null                $map        the Map instance, if available
     * @param MemberInterface|null             $member     the Member instance, if available
     * @param mixed                            $source     the source data to map from
     * @param \ArrayAccess<string, mixed>|null $vars       optional variables for the context
     *
     * @return ResolutionContextInterface the created resolution context
     */
    public function get(
        AutoMapperInterface $autoMapper,
        ?MapInterface $map = null,
        ?MemberInterface $member = null,
        mixed $source = null,
        ?\ArrayAccess $vars = null,
    ): ResolutionContextInterface;
}
