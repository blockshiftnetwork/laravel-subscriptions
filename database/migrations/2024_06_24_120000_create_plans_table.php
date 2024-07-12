<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    
    public function up(): void
    {
        Schema::create(config('laravel-subscriptions.tables.plans'), function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->double('price')->default('0.00');
            $table->string('currency');
            $table->unsignedSmallInteger('trial_period')->default(0);
            $table->string('trial_interval')->nullable();
            $table->unsignedSmallInteger('invoice_period')->default(0);
            $table->string('invoice_interval')->nullable();
            $table->unsignedSmallInteger('grace_period')->default(0);
            $table->string('grace_interval')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists(config('laravel-subscriptions.tables.plans'));
    }
};
