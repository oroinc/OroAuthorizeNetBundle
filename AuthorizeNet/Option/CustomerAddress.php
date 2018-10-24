<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;

/**
 * Option class to represent billTo field (Authorize.Net SDK, Customer Profile)
 */
class CustomerAddress implements OptionInterface
{
    /**
     * {@inheritdoc}
     */
    public function configureOption(OptionsResolver $resolver)
    {
        $resolver
            ->addOption(new AddressOption\FirstName())
            ->addOption(new AddressOption\LastName())
            ->addOption(new AddressOption\Company())
            ->addOption(new AddressOption\Address())
            ->addOption(new AddressOption\City())
            ->addOption(new AddressOption\State())
            ->addOption(new AddressOption\Zip())
            ->addOption(new AddressOption\Country())
            ->addOption(new AddressOption\PhoneNumber($isRequired = false))
            ->addOption(new AddressOption\FaxNumber($isRequired = false));
    }
}
