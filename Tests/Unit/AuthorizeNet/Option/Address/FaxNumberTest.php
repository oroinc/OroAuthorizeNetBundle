<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\AbstractOptionTest;

class FaxNumberTest extends AbstractOptionTest
{
    /** {@inheritdoc} */
    protected function getOptions()
    {
        return [new AddressOption\FaxNumber()];
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
                    'The required option "fax_number" is missing.',
                ],
            ],
            'wrong_type' => [
                ['fax_number' => 12345],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException',
                    'The option "fax_number" with value 12345 is expected to be of type "string", but is of type '.
                    '"integer".',
                ],
            ],
            'valid' => [
                ['fax_number' => '123456'],
                ['fax_number' => '123456'],
            ],
        ];
    }
}
