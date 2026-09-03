<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Laravel مفيهوش نظام تفضيلات جاهز — بنبنيه. (docs/09 بند ٣)
        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('notification_key');       // user_invited
            $table->json('channels');                 // ["database", "mail"]
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'tenant_id', 'notification_key'], 'notification_preferences_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
