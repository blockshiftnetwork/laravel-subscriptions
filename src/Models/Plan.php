<?php

declare(strict_types=1);

namespace Laravelcm\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravelcm\Subscriptions\Traits\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Laravelcm\Subscriptions\Models\Plan.
 *
 * @property int $id
 * @property array $name
 * @property array $description
 * @property bool $is_active
 * @property float $price
 * @property string $currency
 * @property int $trial_period
 * @property string $trial_interval
 * @property int $invoice_period
 * @property string $invoice_interval
 * @property int $grace_period
 * @property string $grace_interval
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravelcm\Subscriptions\Models\Feature[] $features
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravelcm\Subscriptions\Models\Subscription[] $subscriptions
 *
 */
class Plan extends Model
{
    use HasFactory;
    use HasSlug;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'is_active',
        'price',
        'currency',
        'trial_period',
        'trial_interval',
        'invoice_period',
        'invoice_interval',
        'grace_period',
        'grace_interval',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'double',
        'currency' => 'string',
        'trial_period' => 'integer',
        'trial_interval' => 'string',
        'invoice_period' => 'integer',
        'invoice_interval' => 'string',
        'grace_period' => 'integer',
        'grace_interval' => 'string',
        'deleted_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('laravel-subscriptions.tables.plans');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function features(): belongsToMany
    {
        return $this->belongsToMany(config('laravel-subscriptions.models.feature'));
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(config('laravel-subscriptions.models.subscription'));
    }

    public function isFree(): bool
    {
        return $this->price <= 0.00;
    }

    public function hasTrial(): bool
    {
        return $this->trial_period && $this->trial_interval;
    }

    public function hasGrace(): bool
    {
        return $this->grace_period && $this->grace_interval;
    }

    public function activate(): self
    {
        $this->update(['is_active' => true]);

        return $this;
    }

    public function deactivate(): self
    {
        $this->update(['is_active' => false]);

        return $this;
    }
}
