<?php
declare(strict_types=1);

namespace Soap\Encoding\Xml\Writer;

use Generator;
use Soap\Encoding\Xml\Reader\ChildrenReader;
use Soap\Engine\Metadata\Model\MethodMeta;
use Soap\WsdlReader\Model\Definitions\BindingStyle;
use function Psl\Vec\map;
use function VeeWee\Xml\Writer\Builder\namespaced_element;
use function VeeWee\Xml\Writer\Builder\raw;

final class OperationBuilder
{
    /**
     * @param \Closure(\XMLWriter): Generator<bool> $children
     * @param list<string> $parameters
     */
    public function __construct(
        private readonly MethodMeta $meta,
        private readonly array $parameters
    ) {
    }

    /**
     * @return Generator<bool>
     */
    public function __invoke(\XMLWriter $writer): Generator
    {
        $operationName = $this->meta->operationName()->unwrap();
        $namespace = $this->meta->inputNamespace()->or($this->meta->targetNamespace())->unwrap();

        yield from namespaced_element(
            $namespace,
            'ns1', // TODO : detect prefix from namespace map.
            $operationName,
            $this->buildChildren(...)
        )($writer);
    }

    /**
     * @return Generator<bool>
     */
    private function buildChildren(\XMLWriter $writer): Generator
    {
        $bindingStyle = BindingStyle::tryFrom($this->meta->bindingStyle()->unwrapOr(BindingStyle::DOCUMENT->value));

        yield from match($bindingStyle) {
            BindingStyle::DOCUMENT => $this->buildDocument($writer),
            BindingStyle::RPC => $this->buildRpc($writer),
        };
    }

    /**
     * @return Generator<bool>
     */
    private function buildDocument(\XMLWriter $writer): Generator
    {
        $documentParts = map($this->parameters, (new ChildrenReader())(...));

        yield from raw(implode('', $documentParts))($writer);
    }

    /**
     * @return Generator<bool>
     */
    private function buildRpc(\XMLWriter $writer): Generator
    {
        yield from raw(implode('', $this->parameters))($writer);
    }
}