<?php

namespace Shirokovnv\DomainReflection\Facades;

use Illuminate\Support\Facades\Facade;

class DomainReflection extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'domain-reflection';
    }
}
