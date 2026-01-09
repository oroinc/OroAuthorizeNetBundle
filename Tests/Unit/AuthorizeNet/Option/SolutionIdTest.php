<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class SolutionIdTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\SolutionId()];
    }

    #[\Override]
    public function configureOptionDataProvider(): array
    {
        return [
            'wrong_type' => [
                ['solution_id' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "solution_id" with value 12345 is expected to be of type "string", but is of type ' .
                    '"int".',
                ],
            ],
            'valid' => [
                ['solution_id' => 'AAA000001'],
                ['solution_id' => 'AAA000001'],
            ],
        ];
    }
}
