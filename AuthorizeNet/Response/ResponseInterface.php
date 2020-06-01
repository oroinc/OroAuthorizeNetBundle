<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response;

/**
 * Response interface
 */
interface ResponseInterface
{
    /**
     * The transaction was successfully created and approved
     */
    public const TRANS_SUCCESSFUL_RESPONSE_CODE = '1';

    /**
     * The transaction was created but not approved.
     * Oro gets this status when Fraud detection filter in Authorize.Net account configured
     * with "Authorize and hold for review" or "Do not authorize, but hold for review" options.
     */
    public const TRANS_NOT_APPROVED_RESPONSE_CODE = '4';

    /**
     * @return bool
     */
    public function isSuccessful();

    /**
     * @return string|null
     */
    public function getReference();

    /**
     * @return string|null
     */
    public function getMessage();
    
    /**
     * @return mixed
     */
    public function getData();
}
