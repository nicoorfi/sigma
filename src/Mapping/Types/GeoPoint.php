<?php

namespace Sigma\Mapping\Types;

use Sigma\Mapping\Type;

class GeoPoint extends Type
{
    /**
     * Native field name
     *
     * @return string
     */
    public function field(): string
    {
        return 'geo_point';
    }
}