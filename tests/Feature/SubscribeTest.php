<?php

declare(strict_types=1);

use Tests\Models\Plan;
use Tests\Models\User;
use Tests\Models\Feature;
use Illuminate\Support\Carbon;
use Laravelcm\Subscriptions\Models\Subscription;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->plan = Plan::factory()->create();

    $scenarios = Feature::factory()->create([
        'name' => 'Escenarios',
        'description' => '',
        'consumable' => true,
        'resettable_period' => 0,
        'resettable_interval' => 'year'
    ]);

    $images = Feature::factory()->create([
        'name' => 'Imagenes',
        'description' => '',
        'consumable' => false,
        'resettable_period' => 0,
        'resettable_interval' => 'year'
    ]); 

    $this->plan->features()->attach($scenarios);
    $this->plan->features()->attach($images);
});

it('User implement subscription methods', function (): void {
    expect($this->user)
        ->toHaveMethods([
            'subscribeTo',
            'isSubscribedTo',
            'subscription',
            'balance',
            'canConsume',
            'consume',
            'switchTo'
        ]);
})->group('subscribe');

it('a user can subscribe to a plan', function (): void {
    $this->user->subscribeTo($this->plan, 200);

    expect($this->user->isSubscribedTo($this->plan->id))->toBeTrue();
})->group('subscribe');

it('user can have an active annual subscription plan', function (): void {
    $this->user->subscribeTo($this->plan, 500);

    expect($this->user->subscription()->active())
        ->toBeTrue()
        ->and($this->user->subscription()->expired_at->toDateString())
        ->toBe(Carbon::now()->addYear()->addDays($this->plan->trial_period)->toDateString());
})->group('subscribe');

it('user can switch to plan', function (): void {
    $plan = Plan::factory()->create([
        'name' => 'Premium',
        'description' => 'Premium plan',
        'is_active' => true,
        'price' => 25.50,
        'currency' => 'USD',
        'invoice_period' => 1,
        'invoice_interval' => 'year',
        'trial_period' => 0,
        'trial_interval' => 'day',
        'grace_period' => 0,
        'trial_interval' => 'day',
    ]);

    $this->user->subscribeTo($this->plan, 1000);

    $this->user->switchTo($plan, 2000);

    expect($this->user->isSubscribedTo($plan->id))->toBeTrue();
})->group('subscribe');

it('user can cancel a subscription', function (): void {
    $this->user->subscribeTo($this->plan, 3000);

    expect($this->user->isSubscribedTo($this->plan->id))->toBeTrue();

    $this->user->subscription()->cancel(true);

    expect($this->user->subscription()->canceled())->toBeTrue();
})->group('subscribe');

it('user can consume 100 credits', function (): void {
    $this->user->subscribeTo($this->plan, 4000);

    expect($this->user->isSubscribedTo($this->plan->id))->toBeTrue();

    expect($this->user->canConsume('escenarios-5', 100))->toBeTrue();
    
    expect($this->user->canConsume('imagenes-5', 100))->toBeTrue();

    $this->user->consume('escenarios-5', 100);

    expect($this->user->balance())->toBe(3900.0);
})->group('subscribe');

it('user can renew the subscription', function (): void {
    $this->user->subscribeTo($this->plan, 5000);

    expect($this->user->isSubscribedTo($this->plan->id))->toBeTrue();

    $this->user->subscription()->expired_at = now();
    $this->user->subscription()->save();

    expect($this->user->subscription()->renew())->toBeInstanceOf(Subscription::class);

})->group('subscribe');