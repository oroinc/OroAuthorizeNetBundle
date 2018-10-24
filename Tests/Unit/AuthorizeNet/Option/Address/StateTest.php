<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\AbstractOptionTest;

class StateTest extends AbstractOptionTest
{
    /** {@inheritdoc} */
    protected function getOptions()
    {
        return [new AddressOption\State()];
    }

    /** {@inheritdoc} */
    public function configureOptionDataProvider()
    {
        return [
            'required' => [
                [],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\MissingOptionsException',
                    'The required option "state" is missing.',
                ],
            ],
            'wrong_type' => [
                ['state' => 12345],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException',
                    'The option "state" with value 12345 is expected to be of type "string", but is of type '.
                    '"integer".',
                ],
            ],
            'valid' => [
                ['state' => 'state name'],
                ['state' => 'state name'],
            ],
        ];
    }
}
