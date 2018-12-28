<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Layout\DataProvider;

use Oro\Bundle\AuthorizeNetBundle\Layout\DataProvider\PaymentProfileDTOFormProvider as BaseProvider;

class PaymentProfileDTOFormProvider extends BaseProvider
{
    /**
     * @return array
     */
    public function getPageComponentOptions()
    {
        $options = parent::getPageComponentOptions();
        $options['acceptJsUrls'] = [
            'test' => 'oroauthorizenet/js/stubs/AcceptStub',
            'prod' => 'oroauthorizenet/js/stubs/AcceptStub',
        ];

        return $options;
    }
}
