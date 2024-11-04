<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\AbstractOptionTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class StateTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new AddressOption\State()];
    }

    #[\Override]
    public function configureOptionDataProvider(): array
    {
        return [
            'required' => [
                [],
                [],
                [
                    MissingOptionsException::class,
                    'The required option "state" is missing.',
                ],
            ],
            'wrong_type' => [
                ['state' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "state" with value 12345 is expected to be of type "string", but is of type '.
                    '"int".',
                ],
            ],
            'valid' => [
                ['state' => 'state name'],
                ['state' => 'state name'],
            ],
        ];
    }
}
