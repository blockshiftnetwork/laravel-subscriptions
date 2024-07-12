<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    
    public function up(): void
    {
        Schema::create(config('laravel-subscriptions.tables.subscriptions'), function (Blueprint $table): void {
            $table->id();
            $table->double('charges')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('suppressed_at')->nullable();
            $table->timestamp('trial_ended_at')->nullable();
            $table->boolean('was_switched')->default(false);
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->morphs('subscriber');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('laravel-subscriptions.tables.subscriptions'));
    }
};
