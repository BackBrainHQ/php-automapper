<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Attributes;

use Backbrain\Automapper\Contract\ValueResolverInterface;
use Symfony\Component\ExpressionLanguage\Expression;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class MapFrom
{
    private string $source;

    private Expression|ValueResolverInterface $valueResolverOrExpression;

    /**
     * Configure the mapping for a specific member.
     * The argument `mapFrom` can be Symfony Expression Language expressions.
     * <code>
     * // using Symfony Expression Language
     * // see https://symfony.com/doc/current/reference/formats/expression_language.html
     * use Symfony\Component\ExpressionLanguage\Expression;
     * class AccountDTO {
     *     #[MapFrom(ProfileDTO::class, 'source.givenName~" "~source.givenName')]
     *     public string $displayName;
     * }
     * </code>
     * Within the expression you can use the following variables:
     * - `source`: the source object
     * - `context`: the current `Backbrain\Automapper\Contracts\ResolutionContextInterface`.
     *
     * @param string                                   $source                    the source member type for which this member configuration is applied
     * @param ValueResolverInterface|Expression|string $valueResolverOrExpression Can be an instance of ValueResolverInterface or a Symfony EL expression that will be used resolve the member value
     */
    public function __construct(string $source, string|Expression|ValueResolverInterface $valueResolverOrExpression)
    {
        $this->source = $source;
        $this->valueResolverOrExpression = is_string($valueResolverOrExpression) ? new Expression($valueResolverOrExpression) : $valueResolverOrExpression;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getValueResolverOrExpression(): Expression|ValueResolverInterface
    {
        return $this->valueResolverOrExpression;
    }
}
