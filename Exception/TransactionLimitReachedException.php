<?php

namespace Oro\Bundle\AuthorizeNetBundle\Exception;

/**
 * Exception if the transaction limit reached and hold transaction option is disabled
 */
class TransactionLimitReachedException extends \LogicException
{
}
