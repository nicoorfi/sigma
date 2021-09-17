<?php

declare(strict_types=1);

namespace Sigmie\English\Filter;

use Sigmie\Base\Analysis\TokenFilter\TokenFilter;

use function Sigmie\Helpers\name_configs;

/**
 * @see https://snowballstem.org/algorithms/english/stemmer.html
 */
class Porter2Stemmer extends TokenFilter
{
    public function __construct(string $name = 'english_stemmer_porter_2')
    {
        parent::__construct($name);
    }

    public function type(): string
    {
        return 'stemmer';
    }

    public static function fromRaw(array $raw): static
    {
        [$name, $config] = name_configs($raw);

        return new static($name);
    }

    protected function getValues(): array
    {
        return [
            'language' => 'porter2',
        ];
    }
}