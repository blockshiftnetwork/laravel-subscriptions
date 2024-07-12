<?php

declare(strict_types=1);

namespace Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Models\Feature;

class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    public function definition(): array
    {
        return [
            'name' => 'Escenarios',
            'description' => '',
            'consumable' => true,
            'resettable_period' => 0,
            'resettable_interval' => 'year'
        ];
    }
}
