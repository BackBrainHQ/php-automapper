<?php

namespace Backbrain\Automapper\Contract\Attributes;

use Backbrain\Automapper\Contract\ValueResolverInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class MapFrom
{
    private string $source;

    private ValueResolverInterface|string $mapFrom;

    /**
     * Configure the mapping for a specific member.
     * The argument `mapFrom` can be Symfony Expression Language expressions.
     * <code>
     * // using Symfony Expression Language
     * // see https://symfony.com/doc/current/reference/formats/expression_language.html
     * class AccountDTO {
     *     #[ForMember(ProfileDTO::class,
     *          mapFrom: 'source.givenName~" "~source.givenName',
     *     )]
     *     public string $displayName;
     * }
     * </code>
     * Within the expression you can use the following variables:
     * - `source`: the source object
     * - `context`: the current `Backbrain\Automapper\Contracts\ResolutionContextInterface`.
     *
     * @param string                        $source  the source member type for which this member configuration is applied
     * @param ValueResolverInterface|string $mapFrom it takes a ValueResolverInterface or a valid Symfony EL expression that will be used resolve the member value
     */
    public function __construct(string $source, ValueResolverInterface|string $mapFrom)
    {
        $this->source = $source;
        $this->mapFrom = $mapFrom;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getMapFrom(): ValueResolverInterface|string
    {
        return $this->mapFrom;
    }
}
