<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class ChargeTypeTest extends AbstractOptionTest
{
    /** @return Option\OptionInterface[] */
    protected function getOptions()
    {
        return [new Option\ChargeType()];
    }

    /** @return array */
    public function configureOptionDataProvider()
    {
        return [
            'required' => [
                [],
                [],
                [
                    MissingOptionsException::class,
                    sprintf('The required option "%s" is missing.', Option\ChargeType::NAME)
                ]
            ],
            'wrongType' => [
                [Option\ChargeType::NAME => (string) Option\ChargeType::TYPE_CREDIT_CARD],
                [],
                [
                    InvalidOptionsException::class,
                    sprintf(
                        'The option "%s" with value "%s" is expected to be of type "int", but is of '.
                        'type "string".',
                        Option\ChargeType::NAME,
                        (string) Option\ChargeType::TYPE_CREDIT_CARD
                    )
                ],
            ],
            'wrongValue' => [
                [Option\ChargeType::NAME => 999],
                [],
                [
                    InvalidOptionsException::class,
                    sprintf(
                        'The option "%s" with value %d is invalid. Accepted values are: %s.',
                        Option\ChargeType::NAME,
                        999,
                        implode(', ', Option\ChargeType::ALLOWED_VALUES)
                    )
                ],
            ],
            'creditCardType' => [
                [Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD],
                [Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD]
            ],
            'paymentProfileType' => [
                [Option\ChargeType::NAME => Option\ChargeType::TYPE_PAYMENT_PROFILE],
                [Option\ChargeType::NAME => Option\ChargeType::TYPE_PAYMENT_PROFILE]
            ]
        ];
    }
}
