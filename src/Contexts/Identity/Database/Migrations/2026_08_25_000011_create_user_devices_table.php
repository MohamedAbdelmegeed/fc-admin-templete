<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('fingerprint', 64)->index();       // hash(user agent + platform)
            $table->string('device_name')->nullable();        // "Chrome على Windows"
            $table->string('platform')->nullable();
            $table->string('browser')->nullable();
            $table->string('ip_address', 45);
            $table->string('country', 2)->nullable();
            $table->boolean('is_trusted')->default(false);
            $table->timestamp('last_active_at');
            $table->timestamps();

            $table->index(['user_id', 'last_active_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
