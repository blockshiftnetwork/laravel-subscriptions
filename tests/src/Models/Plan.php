<?php

declare(strict_types=1);

namespace Tests\Models;

use Tests\Database\Factories\PlanFactory;
use Laravelcm\Subscriptions\Models\Plan as ModelPlan;

class Plan extends ModelPlan
{
    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }
}
