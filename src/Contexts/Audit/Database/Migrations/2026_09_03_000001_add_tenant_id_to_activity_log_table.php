<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * سجل النشاط بتاع spatie مابيجيش بعمود مؤسسة — وعرضه على لوحة مؤسسة
 * من غيره معناه إن كل مؤسسة بتشوف نشاط الباقيين. (CLAUDE.md خطر رقم ١)
 *
 * العمود nullable عن قصد: النشاط اللي بيحصل بره سياق مؤسسة (أوامر
 * كونسول، سيدرز، طوابير) لازم يتسجّل برضه بدل ما يرمي.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained('tenants')
                ->nullOnDelete();

            $table->index(['tenant_id', 'created_at'], 'activity_log_tenant_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table): void {
            $table->dropIndex('activity_log_tenant_created_index');
            $table->dropConstrainedForeignId('tenant_id');
        });
    }
};
