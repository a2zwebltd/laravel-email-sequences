<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_sequences')) {
            Schema::create('email_sequences', function (Blueprint $table): void {
                $table->id();
                $table->string('sequence_code');
                $table->string('name');
                $table->integer('days_delay');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Seed / reconcile the configured sequences (idempotent). Plain query
        // builder on purpose: the Eloquent model's SoftDeletes scope references
        // the deleted_at column, which older installs only gain in a later
        // migration that has not run yet at this point.
        foreach ((array) config('email-sequences.sequences', []) as $code => $definition) {
            $exists = DB::table('email_sequences')
                ->where('sequence_code', $code)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('email_sequences')->insert([
                'sequence_code' => $code,
                'name' => $definition['name'] ?? $code,
                'days_delay' => (int) ($definition['days_delay'] ?? 0),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_sequences');
    }
};
