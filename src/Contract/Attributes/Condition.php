<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Attributes;

use Symfony\Component\ExpressionLanguage\Expression;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class Condition
{
    private string $source;

    private Expression $expression;

    /**
     * Configure the mapping for a specific member.
     * The arguments `condition` and `mapFrom` can be Symfony Expression Language expressions.
     * <code>
     * // using Symfony Expression Language
     * // see https://symfony.com/doc/current/reference/formats/expression_language.html
     * class AccountDTO {
     *     #[ForMember(ProfileDTO::class,
     *          condition: 'source.publicProfile'
     *     )]
     *     public string $email;
     * }
     * </code>
     * Within the expression you can use the following variables:
     * - `source`: the source object
     * - `context`: the current `Backbrain\Automapper\Contracts\ResolutionContextInterface`.
     *
     * @param string            $source     the source member type for which this member configuration is applied
     * @param string|Expression $expression a Symfony EL expression that must evaluate to `true` to map the member
     */
    public function __construct(string $source, string|Expression $expression)
    {
        $this->source = $source;
        $this->expression = is_string($expression) ? new Expression($expression) : $expression;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getExpression(): Expression
    {
        return $this->expression;
    }
}
