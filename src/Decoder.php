<?php
declare(strict_types=1);

namespace Soap\Encoding;

use Soap\Encoding\Encoder\Context;
use Soap\Encoding\Xml\Reader\OperationReader;
use Soap\Engine\Decoder as SoapDecoder;
use Soap\Engine\HttpBinding\SoapResponse;
use Soap\Engine\Metadata\Metadata;
use Soap\WsdlReader\Model\Definitions\BindingUse;
use Soap\WsdlReader\Model\Definitions\Namespaces;
use function Psl\Vec\map;

final class Decoder implements SoapDecoder
{
    public function __construct(
        private readonly Metadata $metadata,
        private readonly Namespaces $namespaces,
        private readonly EncoderRegistry $registry
    ) {
    }

    public function decode(string $method, SoapResponse $response): mixed
    {
        $methodInfo = $this->metadata->getMethods()->fetchByName($method);
        $meta = $methodInfo->getMeta();
        $bindingUse = $meta->outputBindingUsage()->map(BindingUse::from(...))->unwrapOr(BindingUse::LITERAL);

        $returnType = $methodInfo->getReturnType();
        $context = new Context($returnType, $this->metadata, $this->registry, $this->namespaces, $bindingUse);
        $decoder = $this->registry->detectEncoderForContext($context);

        // The SoapResponse only contains the payload of the response (with no headers).
        // It can be parsed directly as XML.
        $parts = (new OperationReader($meta))($response->getPayload());

        return match(count($parts)) {
            0 => null,
            1 => $decoder->iso($context)->from($parts[0]),
            default => map($parts, $decoder->iso($context)->from(...)),
        };
    }
}
