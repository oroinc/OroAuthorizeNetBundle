<?php

namespace Oro\Bundle\AuthorizeNetBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Allow only one integration with enabled CIM for the website
 */
class ForbidToReuseEnabledCIMWebsites extends Constraint
{
    /** @var string */
    public $messageSingleWebsite = 'oro.authorize_net.validator.forbid_to_reuse_enabled_cim_websites.single';

    /** @var string */
    public $messageMultiWebsite = 'oro.authorize_net.validator.forbid_to_reuse_enabled_cim_websites.multi';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return ForbidToReuseEnabledCIMWebsitesValidator::ALIAS;
    }
}
