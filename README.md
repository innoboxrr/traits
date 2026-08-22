# Traits

**Model and value-object helpers, extracted from production Laravel work.**

A small set of focused traits. Each one is independent — take only what you need.

| Trait | Purpose |
|---|---|
| `ArrayOperations` | Array manipulation helpers for model payloads |
| `DtoTrait` | Turn models into data-transfer objects with a consistent shape |
| `EnumTrait` | Ergonomics for PHP enums — labels, options, validation |
| `MetaOperations` | Arbitrary key-value metadata attached to any model |
| `ModelAppendsTrait` | Manage appended attributes without bloating the model |
| `DumpsGlobalScopes` | Inspect which global scopes are active on a query — a debugging aid |
| `SemVerOperations` | Parse, compare and bump semantic versions |

```php
use Innoboxrr\Traits\MetaOperations;
use Innoboxrr\Traits\DtoTrait;

class Article extends Model
{
    use MetaOperations, DtoTrait;
}
```

## Console commands

The package ships two maintenance commands:

- **Meta cleanup** — prunes orphaned metadata rows left behind by deleted models.
- **Payload regeneration** — rebuilds cached model payloads after a schema or accessor change.

## Install

```bash
composer require innoboxrr/traits
php artisan vendor:publish --tag=innoboxrrtraits-config
```

---

Part of [Innobox R&R](https://github.com/innoboxrr) — 52 open-source packages extracted from production work. **[innobox.systems](https://innobox.systems)**
