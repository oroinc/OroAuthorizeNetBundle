<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

/**
 * The registry that allows to get an AuthorizeNet request by its type.
 */
class RequestRegistry
{
    /** @var iterable|RequestInterface[] */
    private $requests;

    /** @var RequestInterface[] [request type => request, ...] */
    private $loadedRequests;

    /**
     * @param iterable|RequestInterface[] $requests
     */
    public function __construct(iterable $requests)
    {
        $this->requests = $requests;
    }

    /**
     * @param string $type
     *
     * @return RequestInterface
     */
    public function getRequest(string $type): RequestInterface
    {
        if (null === $this->loadedRequests) {
            $this->loadedRequests = $this->loadRequests();
        }

        if (isset($this->loadedRequests[$type])) {
            return $this->loadedRequests[$type];
        }

        throw new \InvalidArgumentException(sprintf(
            'Request with type "%s" is missing. Registered requests are "%s"',
            $type,
            implode(', ', array_keys($this->loadedRequests))
        ));
    }

    /**
     * @return RequestInterface[] [request type => request, ...]
     */
    private function loadRequests(): array
    {
        $loadedRequests = [];
        foreach ($this->requests as $request) {
            $loadedRequests[$request->getType()] = $request;
        }

        return $loadedRequests;
    }
}
