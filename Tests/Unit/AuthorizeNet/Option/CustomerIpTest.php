<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CustomerIpTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\CustomerIp()];
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
                    sprintf('The required option "%s" is missing.', Option\CustomerIp::NAME)
                ]
            ],
            'wrong_type' => [
                [Option\CustomerIp::NAME => 123],
                [],
                [
                    InvalidOptionsException::class,
                    sprintf(
                        'The option "%s" with value 123 is expected to be of type "string", but is of '.
                        'type "int".',
                        Option\CustomerIp::NAME
                    )
                ]
            ],
            'valid' => [
                [Option\CustomerIp::NAME => '192.168.1.1'],
                [Option\CustomerIp::NAME => '192.168.1.1']
            ]
        ];
    }
}
