<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    
    public function up(): void
    {
        Schema::create(config('laravel-subscriptions.tables.feature_plan'), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('feature_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('laravel-subscriptions.tables.feature_plan'));
    }
};
