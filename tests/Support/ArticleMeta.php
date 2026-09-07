<?php

namespace Innoboxrr\Traits\Tests\Support;

use Illuminate\Database\Eloquent\Model;

class ArticleMeta extends Model
{
    protected $fillable = ['article_id', 'key', 'value'];
}
