<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent base Authorize.Net API request
 */
abstract class AbstractRequest implements RequestInterface
{
    /**
     * @var Option\OptionsResolver
     */
    protected $resolver;

    /**
     * @param Option\OptionsResolver $resolver
     * @return $this
     */
    protected function withResolver(Option\OptionsResolver $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * @return $this
     */
    private function addRequiredOptions(): self
    {
        $this
            ->addOption(new Option\ApiLoginId())
            ->addOption(new Option\TransactionKey());

        return $this;
    }

    #[\Override]
    final public function configureOptions(Option\OptionsResolver $resolver): void
    {
        $this
            ->withResolver($resolver)
            ->addRequiredOptions()
            ->configureRequestOptions()
            ->configureSpecificOptions()
            ->endResolver();
    }

    /**
     * @return $this
     */
    protected function configureRequestOptions()
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function configureSpecificOptions()
    {
        return $this;
    }

    /**
     * @param Option\OptionInterface $option
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function addOption(Option\OptionInterface $option)
    {
        if (!$this->resolver) {
            throw new \InvalidArgumentException('Call AbstractRequest->withResolver($resolver) first');
        }

        $this->resolver->addOption($option);

        return $this;
    }

    /**
     * @return $this
     */
    private function endResolver()
    {
        $this->resolver = null;

        return $this;
    }
}
