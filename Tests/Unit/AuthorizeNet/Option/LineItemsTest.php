<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class LineItemsTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\LineItems(false)];
    }

    #[\Override]
    public function configureOptionDataProvider(): array
    {
        $lineItems = [new OrderLineItem()];
        return [
            'empty is valid' => [[]],
            'wrong_type' => [
                [Option\LineItems::NAME => ['wrong array item']],
                [],
                [
                    InvalidOptionsException::class,
                    sprintf(
                        'The option "%s" with value array is expected to be of type "%s", '.
                        'but one of the elements is of type "string".',
                        Option\LineItems::NAME,
                        sprintf('%s[]', OrderLineItem::class)
                    )
                ]
            ],
            'valid' => [
                [Option\LineItems::NAME => $lineItems],
                [Option\LineItems::NAME => $lineItems]
            ]
        ];
    }
}
