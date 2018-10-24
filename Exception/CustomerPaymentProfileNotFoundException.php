<?php

namespace Oro\Bundle\AuthorizeNetBundle\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Exception if CustomerPaymentProfile was not found at api
 */
class CustomerPaymentProfileNotFoundException extends NotFoundHttpException
{
}
