<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\ShippingAddress;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class ShippingAddressTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new ShippingAddress()];
    }

    #[\Override]
    public function configureOptionDataProvider(): array
    {
        $nonExistingField = 'not_existing_option';
        $allFields = ShippingAddress::ALL_OPTION_KEYS;
        sort($allFields);
        $validData = [];

        foreach (ShippingAddress::ALL_OPTION_KEYS as $field) {
            $validData[$field] = $field;
        }

        return [
            'empty is valid' => [],
            'not_existing_option' => [
                [$nonExistingField => 'some value'],
                [],
                [
                    UndefinedOptionsException::class,
                    sprintf(
                        'The option "%s" does not exist. Defined options are: "%s".',
                        $nonExistingField,
                        implode('", "', $allFields)
                    )
                ],
            ],
            'not string type' => [
                [ShippingAddress::FIRST_NAME => 0],
                [],
                [
                    InvalidOptionsException::class,
                    sprintf(
                        'The option "%s" with value 0 is expected to be of type "string", ' .
                        'but is of type "int".',
                        ShippingAddress::FIRST_NAME
                    )
                ],
            ],
            'valid' => [
                $validData,
                $validData
            ]
        ];
    }
}
