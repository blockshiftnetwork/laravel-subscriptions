<?php

declare(strict_types=1);

namespace Laravelcm\Subscriptions\Traits;

use Carbon\Carbon;
use Laravelcm\Subscriptions\Models\Plan;
use Laravelcm\Subscriptions\Models\Feature;
use Laravelcm\Subscriptions\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravelcm\Subscriptions\Services\Period;

trait HasSubscriptions
{
    protected static function bootHasSubscriptions(): void
    {
        static::deleted(function ($plan): void {
            $plan->subscriptions()->delete();
        });
    }

    public function subscriptions(): MorphMany
    {
        return $this->morphMany(config('laravel-subscriptions.models.subscription'), 'subscriber');
    }

    public function subscription(): Subscription
    {
        return app(config('laravel-subscriptions.models.subscription'))
            ->whereMorphedTo('subscriber', $this)
            ->orderBy('started_at', 'DESC')
            ->first();
    }

    public function isSubscribedTo(int $planId): bool
    {
        $subscription = $this->subscription()->where('plan_id', $planId)->first();
        return $subscription && $subscription->active();
    }

    public function subscribeTo(Plan $plan, float $charges, ?Carbon $startDate = null): Subscription
    {
        $trial = new Period($plan->trial_interval, $plan->trial_period, $startDate ?? Carbon::now());

        $period = new Period($plan->invoice_interval, $plan->invoice_period, $trial->getEndDate());

        $subscription = $this->subscriptions()->create([
            'charges' => $charges,
            'plan_id' => $plan->id,
            'started_at' => $period->getStartDate(),
            'expired_at' => $period->getEndDate(),
            'trial_ended_at' => $trial->getEndDate(),
        ]);

        return $subscription;
    }

    public function balance(): float
    {
        $totalCharges = $this->subscription()->charges;
        $totalConsume = $this->subscription()->usage()->pluck('used')->sum() ?? 0;
        return  $totalCharges - $totalConsume;
    }

    public function canConsume(string $feature, float $credits): bool
    {
        if (empty($feature = $this->getFeature($feature))) {
            return false;
        }

        if (!$feature->consumable) {
            return true;
        }

        $balance = $this->balance();
        return $balance >= $credits;
    }

    public function consume(string $feature, float $credits): void
    {
        if ($this->canConsume($feature, $credits)) {

            $feature = $this->getFeature($feature);

            if ($feature->isCosumable()) {

                $feature->usage()->create([
                    'used' => $credits,
                    'expired_at' => $feature->getResetDate($this->subscription()->started_at),
                    'subscription_id' => $this->subscription()->id,
                    'feature_id' => $feature->id,
                ]);
            }
        }
    }

    public function switchTo(Plan $plan, float $charges, ?Carbon $startDate = null, $immediately = true): Subscription
    {
        if ($immediately) {
            $this->subscription()
                ->markAsSwitched()
                ->suppress()
                ->save();

            return $this->subscribeTo($plan, $charges);
        }

        $this->subscription()
            ->markAsSwitched()
            ->save();

        $startDate = $this->subscription()->expired_at;
        $newSubscription = $this->subscribeTo($plan, $charges, $startDate);

        return $newSubscription;
    }  

    public function features()
    {
        return $this->subscription()->plan->features();
    }

    public function getFeature(string $feature): ?Feature
    {
        $feature = $this->features()->firstWhere('slug', $feature);
        return $feature;
    }
}
