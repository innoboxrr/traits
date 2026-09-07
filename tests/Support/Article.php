<?php

namespace Innoboxrr\Traits\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Innoboxrr\Traits\DumpsGlobalScopes;
use Innoboxrr\Traits\MetaOperations;
use Innoboxrr\Traits\ModelAppendsTrait;

/**
 * Un modelo con la forma que genera larapack: metas, payload y whitelist de
 * metas editables.
 */
class Article extends Model
{
    use DumpsGlobalScopes;
    use MetaOperations;
    use ModelAppendsTrait;

    protected $fillable = ['title', 'payload'];

    protected $appends = [];

    /**
     * @var array<int, string>
     */
    public $editable_metas = ['seo_title', 'seo_description'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function metas(): HasMany
    {
        return $this->hasMany(ArticleMeta::class);
    }

    public function getShoutedTitleAttribute(): string
    {
        return mb_strtoupper($this->title);
    }
}
