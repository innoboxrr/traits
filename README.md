# Traits

**Model and value-object helpers, extracted from production Laravel work.**

A small set of focused traits. Each one is independent — take only what you need.

| Trait | Purpose |
|---|---|
| `ArrayOperations` | `isNotEmpty` and `wrapImplode` for arrays |
| `DtoTrait` | Turn models into data-transfer objects with a consistent shape |
| `EnumTrait` | Ergonomics for PHP enums — labels, options, validation |
| `MetaOperations` | Key/value metas in a `<model>_metas` table, plus a JSON `payload` copy |
| `ModelAppendsTrait` | Manage appended attributes without bloating the model |
| `DumpsGlobalScopes` | Inspect which global scopes are active on a query — a debugging aid |
| `SemVerOperations` | Parse, compare and bump semantic versions |

## MetaOperations

Metas are extra fields that don't deserve a column: each one is a `key`/`value` row in the model's `<model>_metas` table. `payload` is a JSON column on the model that gathers them, so reading them costs no queries.

```php
use Illuminate\Database\Eloquent\Relations\HasMany;
use Innoboxrr\Traits\MetaOperations;

class Article extends Model
{
    use MetaOperations;

    // What a form may write.
    protected $editable_metas = ['seo_title', 'seo_description'];

    // What only your code writes. Wins over $editable_metas.
    protected $protected_metas = ['views'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function metas(): HasMany
    {
        return $this->hasMany(ArticleMeta::class);
    }

    public function buildPayload(): array
    {
        return $this->metas()->pluck('value', 'key')->all();
    }

    public function updatePayload(): bool
    {
        $this->payload = $this->buildPayload();

        return $this->save();
    }
}
```

The metas table needs `key`, `value` (text), the foreign key and a unique index on `(key, <model>_id)`.

| Method | What it does |
|---|---|
| `update_metas($requestOrArray, Meta::class, 'article_id')` | The form path. Writes the keys in `$editable_metas` that are not in `$protected_metas`. An empty value (null, blank string, empty array) deletes the meta; a key that isn't sent is left alone. Arrays are stored as JSON. |
| `setMeta($key, $value)` / `setMetas([...])` | The code path. No whitelist, so this is how protected metas are written. Arrays are stored as JSON; `setMetas([])` does nothing. |
| `meta($key, $default)` | Reads one meta from the table, as stored: a JSON meta comes back as a string. |
| `getPayload('seo.title', $default)` | Reads the `payload` copy with dot notation. |
| `metas_array($requestOrArray)` | What `update_metas` would write. |
| `protectedMetas()` | The keys only the system writes. |

`setMeta` does not refresh `payload`: call `updatePayload()` after writing metas from code.

## Console commands

- `metas:regpayload {modelClass} {--modelId=}` — calls `updatePayload()` on one model or all of them, and reports the ones that fail.
- `meta:cleanup {models*}` — removes duplicate `(key, <model>_id)` rows and adds the unique index. MySQL only.

## Install

```bash
composer require innoboxrr/traits
php artisan vendor:publish --tag=innoboxrrtraits-config
```

---

Part of [Innobox R&R](https://github.com/innoboxrr) — 52 open-source packages extracted from production work. **[innobox.systems](https://innobox.systems)**
