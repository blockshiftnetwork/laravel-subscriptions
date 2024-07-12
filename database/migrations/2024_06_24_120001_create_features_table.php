<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {

    public function up(): void
    {
        Schema::create(config('laravel-subscriptions.tables.features'), function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->boolean('consumable');
            $table->unsignedSmallInteger('resettable_period')->default(0);
            $table->string('resettable_interval')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('laravel-subscriptions.tables.features'));
    }
};
