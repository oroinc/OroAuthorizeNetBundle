<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\AbstractOptionTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CompanyTest extends AbstractOptionTest
{
    /**
     * {@inheritDoc}
     */
    protected function getOptions(): array
    {
        return [new AddressOption\Company()];
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptionDataProvider(): array
    {
        return [
            'required' => [
                [],
                [],
                [
                    MissingOptionsException::class,
                    'The required option "company" is missing.',
                ],
            ],
            'wrong_type' => [
                ['company' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "company" with value 12345 is expected to be of type "string", but is of type '.
                    '"int".',
                ],
            ],
            'valid' => [
                ['company' => 'company name'],
                ['company' => 'company name'],
            ],
        ];
    }
}
