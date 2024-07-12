<?php

declare(strict_types=1);

namespace Laravelcm\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Laravelcm\Subscriptions\Models\SubscriptionUsage.
 *
 * @property int $id
 * @property int $used
 * @property \Carbon\Carbon|null $expired_at
 * @property int $subscription_id
 * @property int $feature_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Laravelcm\Subscriptions\Models\Feature $feature
 * @property-read \Laravelcm\Subscriptions\Models\Subscription $subscription
 *
 *
 */
class SubscriptionUsage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'used',
        'expired_at',
        'subscription_id',
        'feature_id',
    ];

    protected $casts = [
        'used' => 'integer',
        'expired_at' => 'datetime',
        'subscription_id' => 'integer',
        'feature_id' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('laravel-subscriptions.tables.subscription_usage');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(config('laravel-subscriptions.models.feature'), 'feature_id', 'id', 'feature');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(config('laravel-subscriptions.models.subscription'), 'subscription_id', 'id', 'subscription');
    }

    public function expired(): bool
    {
        if (!$this->expired_at) {
            return false;
        }

        return Carbon::now()->gte($this->expired_at);
    }

    public function scopeByFeatureSlug(Builder $builder, string $feature): Builder
    {
        $model = config('laravel-subscriptions.models.feature', Feature::class);
        $feature = tap(new $model())->where('slug', $feature)->first();

        return $builder->where('feature_id', $feature ? $feature->getKey() : null);
    }
}
