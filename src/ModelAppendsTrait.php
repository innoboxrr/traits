<?php

namespace App\Support\Traits;

trait ModelAppendsTrait
{

    /**
     * Requirements:
     *  - The accessors to append to the model's array form.
     *  - The API Resource to implement this. 
     * @var array
     */

    public function setAppends(array $appends)
    {
        $this->appends = array_unique(array_merge($this->appends, $appends));
    }
}
