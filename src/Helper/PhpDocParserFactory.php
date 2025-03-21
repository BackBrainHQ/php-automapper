<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Helper;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;

/**
 * @internal This class is not part of the public API and may change at any time!
 */
class PhpDocParserFactory
{
    /**
     * @return array{PhpDocParser, TokenIterator}
     */
    public static function create(string $type): array
    {
        // Different PhpDocParser versions have different APIs
        if (class_exists(ParserConfig::class)) {
            // PHPStan PhpDocParser 2.x
            $config = new ParserConfig(usedAttributes: ['lines' => true, 'indexes' => true]);
            $lexer = new Lexer($config);
            $constExprParser = new ConstExprParser($config);
            $typeParser = new TypeParser($config, $constExprParser);
            $phpDocParser = new PhpDocParser($config, $typeParser, $constExprParser);
        } else {
            // PHPStan PhpDocParser 1.x
            $lexer = new Lexer();
            $constExprParser = new ConstExprParser();
            $typeParser = new TypeParser($constExprParser);
            $phpDocParser = new PhpDocParser($typeParser, $constExprParser);
        }

        $tokens = new TokenIterator($lexer->tokenize(sprintf('/** @return %s */', $type)));

        return [$phpDocParser, $tokens];
    }
}
