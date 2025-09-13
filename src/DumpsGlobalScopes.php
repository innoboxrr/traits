<?php

namespace Innoboxrr\Traits;

trait DumpsGlobalScopes
{
    public static function dumpGlobalScopes(): array
    {
        $rc = new \ReflectionClass(static::class);
        $prop = $rc->getProperty('globalScopes');
        $prop->setAccessible(true);
        $scopes = $prop->getValue();
        return array_map(function ($s) {
            return is_object($s) ? get_class($s) : gettype($s);
        }, $scopes);
    }
}
