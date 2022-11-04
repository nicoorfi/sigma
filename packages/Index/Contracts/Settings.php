<?php

declare(strict_types=1);

namespace Sigmie\Index\Contracts;

use Sigmie\Base\Contracts\Raw;
use Sigmie\Index\Contracts\Analysis;

interface Settings extends Raw
{
    public function toRaw(): array;

    public static function fromRaw(array $raw): static;

    public function primaryShards(): int;

    public function replicaShards(): int;

    public function config(string $name, string $value): self;

    public function analysis(): Analysis;
}