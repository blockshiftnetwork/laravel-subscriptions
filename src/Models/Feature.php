<?php

declare(strict_types=1);

namespace Laravelcm\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravelcm\Subscriptions\Traits\BelongsToPlan;
use Laravelcm\Subscriptions\Traits\HasSlug;
use Laravelcm\Subscriptions\Services\Period;
use Spatie\Sluggable\SlugOptions;

/**
 * Laravelcm\Subscriptions\Models\Feature.
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $slug
 * @property bool $consumable
 * @property int $resettable_period
 * @property string $resettable_interval
 * @property int $plan_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Laravelcm\Subscriptions\Models\Plan $plan
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravelcm\Subscriptions\Models\SubscriptionUsage[] $usage
 *
 */
class Feature extends Model
{
    use BelongsToPlan;
    use HasFactory;
    use HasSlug;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'consumable',
        'resettable_period',
        'resettable_interval',
    ];

    protected $casts = [
        'slug' => 'string',
        'consumable' => 'boolean',
        'resettable_period' => 'integer',
        'resettable_interval' => 'string',
        'deleted_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('laravel-subscriptions.tables.features');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function plans(): belongsToMany
    {
        return $this->belongsToMany(config('laravel-subscriptions.models.plan'))->using(config('laravel-subscriptions.models.feature_plan'));
    }

    public function usage(): HasMany
    {
        return $this->hasMany(config('laravel-subscriptions.models.subscription_usage'));
    }

    public function isCosumable(): bool
    {
        return $this->consumable;
    }

    public function getResetDate(?Carbon $dateFrom = null): Carbon
    {
        $period = new Period($this->resettable_interval, $this->resettable_period, $dateFrom ?? Carbon::now());

        return $period->getEndDate();
    }
}
