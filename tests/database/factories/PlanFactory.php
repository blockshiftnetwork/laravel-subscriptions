<?php

declare(strict_types=1);

namespace Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Models\Plan;

final class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => 'Pro',
            'description' => 'Pro plan',
            'is_active' => true,
            'price' => 9.99,
            'currency' => 'USD',
            'invoice_period' => 1,
            'invoice_interval' => 'year',
            'trial_period' => 0,
            'trial_interval' => 'day',
            'grace_period' => 0,
            'trial_interval' => 'day',
        ];
    }
}
