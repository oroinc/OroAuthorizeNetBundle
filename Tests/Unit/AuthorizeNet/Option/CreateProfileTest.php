<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CreateProfileTest extends AbstractOptionTest
{
    /** @return Option\OptionInterface[] */
    protected function getOptions()
    {
        return [new Option\CreateProfile()];
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
                    sprintf('The required option "%s" is missing.', Option\CreateProfile::NAME),
                ],
            ],
            'wrong_type' => [
                [Option\CreateProfile::NAME => 1],
                [],
                [
                    InvalidOptionsException::class,
                    sprintf(
                        'The option "%s" with value 1 is expected to be of type "bool", but is of '.
                        'type "int".',
                        Option\CreateProfile::NAME
                    )
                ],
            ],
            'validTrue' => [
                [Option\CreateProfile::NAME => true],
                [Option\CreateProfile::NAME => true]
            ],
            'validFalse' => [
                [Option\CreateProfile::NAME => false],
                [Option\CreateProfile::NAME => false]
            ]
        ];
    }
}
