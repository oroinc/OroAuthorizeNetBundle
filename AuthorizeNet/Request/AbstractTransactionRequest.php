<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent base CreateTransactionRequest (Authorize.Net API)
 */
abstract class AbstractTransactionRequest extends AbstractRequest
{
    protected function configureRequestOptions()
    {
        return $this
            ->addOption(new Option\Transaction())
            ->addOption(new Option\Amount())
            ->addOption(new Option\Currency())
            ->addOption(new Option\SolutionId($isRequired = false))
        ;
    }

    /**
     * @return $this
     */
    protected function configureSpecificOptions()
    {
        $this->resolver
            ->setDefault(Option\Transaction::TRANSACTION_TYPE, $this->getType())
            ->addAllowedValues(Option\Transaction::TRANSACTION_TYPE, $this->getType())
        ;

        return $this;
    }
}
