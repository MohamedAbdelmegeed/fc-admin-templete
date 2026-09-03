<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 32)->nullable()->after('email');
            $table->string('locale', 5)->nullable()->after('phone');
            $table->string('timezone', 64)->nullable()->after('locale');
            $table->string('status')->default('invited')->after('timezone');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->timestamp('password_changed_at')->nullable()->after('last_login_ip');
            $table->softDeletes();

            // أي عمود بيتفلتر أو بيترتب عليه لازم فهرس. (docs/08 بند ٥)
            $table->index('status');
            $table->index('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropIndex(['last_login_at']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'phone', 'locale', 'timezone', 'status',
                'last_login_at', 'last_login_ip', 'password_changed_at',
            ]);
        });
    }
};
