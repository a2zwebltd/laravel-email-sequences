<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_sequences') || Schema::hasColumn('email_sequences', 'deleted_at')) {
            return;
        }

        Schema::table('email_sequences', function (Blueprint $table): void {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('email_sequences', 'deleted_at')) {
            return;
        }

        Schema::table('email_sequences', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};
