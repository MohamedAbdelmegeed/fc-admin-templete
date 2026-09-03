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
            // اسم مستخدم اختياري — الدخول بيقبل الإيميل أو الاسم ده.
            // nullable عشان الحسابات القديمة ما تتكسرش، و unique عشان
            // مايبقاش فيه اتنين بنفس الاسم.
            $table->string('username', 60)->nullable()->unique()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
