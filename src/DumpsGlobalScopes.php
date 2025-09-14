<?php

namespace Innoboxrr\Traits;

trait DumpsGlobalScopes
{
    public static function dumpMyGlobalScopes(): array
    {
        $model = new static();

        $rc = new \ReflectionClass($model);
        $m  = $rc->getMethod('getGlobalScopes');
        $m->setAccessible(true);

        $scopes = $m->invoke($model);

        return array_map(function ($s) {
            return is_object($s) ? get_class($s) : (is_callable($s) ? 'closure' : gettype($s));
        }, $scopes);
    }
}