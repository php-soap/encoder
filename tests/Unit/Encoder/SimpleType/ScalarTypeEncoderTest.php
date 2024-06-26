<?php
declare(strict_types=1);

namespace Soap\Encoding\Test\Unit\Encoder\SimpleType;

use PHPUnit\Framework\Attributes\CoversClass;
use Soap\Encoding\Encoder\SimpleType\ScalarTypeEncoder;
use Soap\Encoding\Test\Unit\Encoder\AbstractEncoderTests;
use Soap\Engine\Metadata\Model\XsdType;

#[CoversClass(ScalarTypeEncoder::class)]
final class ScalarTypeEncoderTest extends AbstractEncoderTests
{
    public static function provideIsomorphicCases(): iterable
    {
        $baseConfig = [
            'encoder' => $encoder = new ScalarTypeEncoder(),
            'context' => $context = self::createContext(XsdType::guess('anySimpleType')),
        ];

        yield 'string' => [
            ...$baseConfig,
            'xml' => 'hello',
            'data' => 'hello',
        ];
        yield 'special-chars' => [
            ...$baseConfig,
            'xml' => 'hëllo\'"<>',
            'data' => 'hëllo\'"<>',
        ];
        yield 'int' => [
            ...$baseConfig,
            'xml' => '123',
            'data' => 123,
        ];
        yield 'float' => [
            ...$baseConfig,
            'xml' => '123.22',
            'data' => 123.22,
        ];
        yield 'bool-true' => [
            ...$baseConfig,
            'xml' => 'true',
            'data' => true,
        ];
        yield 'bool-false' => [
            ...$baseConfig,
            'xml' => 'false',
            'data' => false,
        ];
    }
}
