<?php

declare(strict_types=1);

use Tests\Models\Feature;
use Tests\Models\Plan;

beforeEach(function (): void {
    $this->plan = Plan::factory()->create();
});

it('can create a plan', function (): void {
    expect(Plan::count())->toBe(1);
})->group('plan');

it('a plan can have many features', function (): void {
    expect($this->plan->features()->count())->toBe(0);

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

    expect($this->plan->features()->count())->toBe(2);
})->group('plan');

it('a plan can be free with trial period', function (): void {
    $this->plan->update([
        'price' => 0,
        'trial_period' => 7,
        'trial_interval' => 'day',
    ]);

    expect($this->plan->isFree())->toBeTrue()->and($this->plan->hasTrial())->toBeTrue();
})->group('plan');

it('a plan can have a grace period', function (): void {
    $this->plan->update([
        'grace_period' => 7,
        'grace_interval' => 'day',
    ]);

    expect($this->plan->hasGrace())->toBeTrue();
})->group('plan');
