<?php

namespace Oro\Bundle\AuthorizeNetBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * At least one website required for integration with enabled CIM
 */
class RequiredEnabledCIMWebsites extends Constraint
{
    /** @var string */
    public $message = 'oro.authorize_net.validator.required_enabled_cim_websites';

    #[\Override]
    public function validatedBy(): string
    {
        return RequiredEnabledCIMWebsitesValidator::ALIAS;
    }
}
