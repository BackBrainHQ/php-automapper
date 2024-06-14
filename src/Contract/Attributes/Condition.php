<?php

namespace Backbrain\Automapper\Contract\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class Condition
{
    private string $source;

    private string $condition;

    /**
     * Configure the mapping for a specific member.
     * The arguments `condition` and `mapFrom` can be Symfony Expression Language expressions.
     * <code>
     * // using Symfony Expression Language
     * // see https://symfony.com/doc/current/reference/formats/expression_language.html
     * class AccountDTO {
     *     #[ForMember(ProfileDTO::class,
     *          mapFrom: 'source.givenName~" "~source.givenName',
     *          condition: 'source.publicProfile'
     *     )]
     *     public string $displayName;
     * }
     * </code>
     * Within the expression you can use the following variables:
     * - `source`: the source object
     * - `context`: the current `Backbrain\Automapper\Contracts\ResolutionContextInterface`.
     *
     * @param string $source    the source member type for which this member configuration is applied
     * @param string $condition a Symfony EL expression that must evaluate to `true` to map the member
     */
    public function __construct(string $source, string $condition)
    {
        $this->source = $source;
        $this->condition = $condition;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getCondition(): string
    {
        return $this->condition;
    }
}
