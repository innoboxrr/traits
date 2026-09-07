<?php

namespace Innoboxrr\Traits\Tests\Support;

use Innoboxrr\Traits\EnumTrait;

enum Status: string
{
    use EnumTrait;

    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
