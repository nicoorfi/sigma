<?php

declare(strict_types=1);

namespace Sigmie\Base\Contracts;

use Sigmie\Base\Http\ElasticsearchResponse;

trait API
{
    protected HttpConnection $httpConnection;

    public function setHttpConnection(HttpConnection $connection): void
    {
        $this->httpConnection = $connection;
    }

    public function getHttpConnection()
    {
        return $this->httpConnection;
    }

    protected function httpCall(ElasticsearchRequest $request): ElasticsearchResponse
    {
        return ($this->httpConnection)($request);
    }
}
