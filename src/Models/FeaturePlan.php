<?php

declare(strict_types=1);

namespace Laravelcm\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Laravelcm\Subscriptions\Models\FeaturePlan.
 *
 * @property int $id
 * @property int $feature_id
 * @property int $plan_id
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Laravelcm\Subscriptions\Models\Plan $plan
 * @property-read \Laravelcm\Subscriptions\Models\Feature $feature
 *
 */
class FeaturePlan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'feature_id',
        'plan_id',
    ];

    public function getTable(): string
    {
        return config('laravel-subscriptions.tables.feature_plan');
    }

    public function feature()
    {
        return $this->belongsTo(config('laravel-subscriptions.models.feature'));
    }

    public function plan()
    {
        return $this->belongsTo(config('laravel-subscriptions.models.plan'));
    }
}
