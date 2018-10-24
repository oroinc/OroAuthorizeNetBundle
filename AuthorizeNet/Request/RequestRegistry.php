<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

/**
 * Registry for all request classes
 */
class RequestRegistry
{
    /**
     * @var RequestInterface[]
     */
    protected $requests = [];

    /**
     * @param RequestInterface $request
     * @return $this
     */
    public function addRequest(RequestInterface $request)
    {
        $this->requests[$request->getType()] = $request;

        return $this;
    }

    /**
     * @param string $type
     * @return RequestInterface
     */
    public function getRequest(string $type)
    {
        if (\array_key_exists($type, $this->requests)) {
            return $this->requests[$type];
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Request with type "%s" is missing. Registered requests are "%s"',
                $type,
                implode(', ', array_keys($this->requests))
            )
        );
    }
}
