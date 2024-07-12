<?php

declare(strict_types=1);

namespace Tests\Models;

use Tests\Database\Factories\FeatureFactory;
use Laravelcm\Subscriptions\Models\Feature as ModelFeature;

class Feature extends ModelFeature
{
    protected static function newFactory(): FeatureFactory
    {
        return FeatureFactory::new();
    }
}
