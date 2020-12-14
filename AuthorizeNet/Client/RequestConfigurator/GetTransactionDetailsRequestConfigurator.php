<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * request configurator for getTransactionDetailsRequest request
 */
class GetTransactionDetailsRequestConfigurator implements RequestConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function isApplicable(AnetAPI\ANetApiRequestType $request, array $options): bool
    {
        return $request instanceof AnetAPI\GetTransactionDetailsRequest;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(AnetAPI\ANetApiRequestType $request, array &$options): void
    {
        if (array_key_exists(Option\OriginalTransaction::ORIGINAL_TRANSACTION, $options)) {
            $request->setTransId($options[Option\OriginalTransaction::ORIGINAL_TRANSACTION]);
        }

        // Remove handled options to prevent handling in fallback configurator
        unset($options[Option\OriginalTransaction::ORIGINAL_TRANSACTION]);
    }
}
