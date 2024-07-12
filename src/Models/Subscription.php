<?php

declare(strict_types=1);

namespace Laravelcm\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravelcm\Subscriptions\Traits\BelongsToPlan;
use Laravelcm\Subscriptions\Services\Period;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Laravelcm\Subscriptions\Models\Subscription.
 *
 * @property int $id
 * @property float $charges
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $expired_at
 * @property \Carbon\Carbon|null $canceled_at
 * @property \Carbon\Carbon|null $suppressed_at
 * @property \Carbon\Carbon|null $trial_ended_at
 * @property bool $was_switched
 * @property int $plan_id
 * @property int $subscriber_id
 * @property string $subscriber_type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Laravelcm\Subscriptions\Models\Plan $plan
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravelcm\Subscriptions\Models\SubscriptionUsage[] $usage
 * @property-read \Illuminate\Database\Eloquent\Model $subscriber
 *
 */
class Subscription extends Model
{
    use BelongsToPlan;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'charges',
        'started_at',
        'expired_at',
        'canceled_at',
        'suppressed_at',
        'trial_ended_at',
        'was_switched',
        'plan_id',
    ];

    protected $casts = [
        'charges' => 'double',
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
        'canceled_at' => 'datetime',
        'suppressed_at' => 'datetime',
        'trial_ended_at' => 'datetime',
        'was_switched' => 'boolean',
        'plan_id' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('laravel-subscriptions.tables.subscriptions');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (!$model->started_at || !$model->expired_at) {
                $model->setNewPeriod();
            }
        });

        static::deleted(function (self $subscription): void {
            $subscription->usage()->delete();
        });
    }

    public function subscriber(): MorphTo
    {
        return $this->morphTo('subscriber');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(config('laravel-subscriptions.models.plan'));
    }

    public function usage(): hasMany
    {
        return $this->hasMany(config('laravel-subscriptions.models.subscription_usage'));
    }

    public function active(): bool
    {
        return !$this->expired() || $this->onTrial();
    }

    public function inactive(): bool
    {
        return ! $this->active();
    }

    public function onTrial(): bool
    {
        return $this->trial_ended_at && Carbon::now()->lt($this->trial_ended_at);
    }

    public function canceled(): bool
    {
        return $this->canceled_at && Carbon::now()->gte($this->canceled_at);
    }

    public function expired(): bool
    {
        return $this->expired_at && Carbon::now()->gte($this->expired_at);
    }

    public function cancel(bool $immediately = false): self
    {
        $this->canceled_at = Carbon::now();

        if ($immediately) {
            $this->expired_at = $this->canceled_at;
        }

        $this->save();

        return $this;
    }

    public function renew(): self
    {
        if ($this->expired() && $this->canceled()) {
            throw new LogicException('Unable to renew canceled ended subscription.');
        }

        $subscription = $this;

        DB::transaction(function () use ($subscription): void {
            $subscription->usage()->delete();
            $subscription->setNewPeriod();
            $subscription->canceled_at = null;
            $subscription->save();
        });

        return $this;
    }

    public function suppress(?Carbon $suppressation = null)
    {
        $suppressationDate = $suppressation ?: now();

        $this->fill(['suppressed_at' => $suppressationDate])->save();

        return $this;
    }

    public function markAsSwitched(): self
    {
        return $this->fill([
            'was_switched' => true,
        ]);
    }

    protected function setNewPeriod(string $invoice_interval = '', int $invoice_period = null, Carbon $start = null): self
    {
        if (empty($invoice_interval)) {
            $invoice_interval = $this->plan->invoice_interval;
        }

        if (empty($invoice_period)) {
            $invoice_period = $this->plan->invoice_period;
        }

        $period = new Period($invoice_interval, $invoice_period, $start ?? Carbon::now());

        $this->started_at = $period->getStartDate();
        $this->expired_at = $period->getEndDate();

        return $this;
    }

    public function scopeOfSubscriber(Builder $builder, Model $subscriber): Builder
    {
        return $builder->where('subscriber_type', $subscriber->getMorphClass())
            ->where('subscriber_id', $subscriber->getKey());
    }

    public function scopeFindEndingTrial(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('trial_ended_at', [$from, $to]);
    }

    public function scopeFindEndedTrial(Builder $builder): Builder
    {
        return $builder->where('trial_ended_at', '<=', Carbon::now());
    }

    public function scopeFindEndingPeriod(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('expired_at', [$from, $to]);
    }

    public function scopeFindEndedPeriod(Builder $builder): Builder
    {
        return $builder->where('expired_at', '<=', Carbon::now());
    }

    public function scopeFindActive(Builder $builder): Builder
    {
        return $builder->where('expired_at', '>', Carbon::now());
    }
}
