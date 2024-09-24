<?php

namespace Oro\Bundle\AuthorizeNetBundle\Validator\Constraints;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Form\Extension\EnabledCIMWebsitesSelectExtension;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Check that at least one website selected for the integration with enabled CIM
 */
class RequiredEnabledCIMWebsitesValidator extends ConstraintValidator
{
    const ALIAS = 'oro_authorize_net.validator.required_enabled_cim_websites';

    /**
     * @param AuthorizeNetSettings        $entity
     * @param RequiredEnabledCIMWebsites  $constraint
     */
    #[\Override]
    public function validate($entity, Constraint $constraint)
    {
        if (! $entity instanceof AuthorizeNetSettings) {
            return;
        }

        if ($entity->getChannel() && !$entity->getChannel()->isEnabled()) {
            return;
        }

        if (!$entity->isEnabledCIM() || 0 !== count($entity->getEnabledCIMWebsites())) {
            return;
        }

        $this->addFieldViolation(
            EnabledCIMWebsitesSelectExtension::FIELD_NAME,
            $constraint->message
        );
    }

    /**
     * @param string $field
     * @param string $message
     */
    private function addFieldViolation($field, $message)
    {
        /** @var ExecutionContextInterface $context */
        $context = $this->context;
        $context
            ->buildViolation($message)
            ->atPath($field)
            ->addViolation();
    }
}
