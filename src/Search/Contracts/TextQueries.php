<?php

declare(strict_types=1);

namespace Sigmie\Search\Contracts;

interface TextQueries
{
    public function queries(string $queryString): array;
}
