<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Abstract option for general option logic
 */
abstract class AbstractOption implements OptionInterface
{
    /**
     * @var bool
     */
    protected $requiredOption;

    /**
     * @return string
     */
    abstract protected function getName();

    /**
     * @param bool $isRequired
     */
    public function __construct($isRequired = true)
    {
        $this->requiredOption = (bool) $isRequired;
    }

    /**
     * @return string|string[]
     */
    protected function getAllowedTypes()
    {
        return [];
    }

    /**
     * @return mixed
     */
    protected function getAllowedValues()
    {
        return [];
    }

    #[\Override]
    final public function configureOption(OptionsResolver $resolver)
    {
        $resolver->setDefined($this->getName());

        if ($this->requiredOption) {
            $resolver->setRequired($this->getName());
        }

        if (!empty($this->getAllowedTypes())) {
            $resolver->addAllowedTypes($this->getName(), $this->getAllowedTypes());
        }

        if (!empty($this->getAllowedValues())) {
            $resolver->addAllowedValues($this->getName(), $this->getAllowedValues());
        }
    }
}
