<?php

declare(strict_types=1);

use A2ZWeb\EmailSequences\Models\EmailSequence;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            });
        }

        // Seed / reconcile the configured sequences (idempotent).
        foreach ((array) config('email-sequences.sequences', []) as $code => $definition) {
            EmailSequence::query()->firstOrCreate(
                ['sequence_code' => $code],
                [
                    'name' => $definition['name'] ?? $code,
                    'days_delay' => (int) ($definition['days_delay'] ?? 0),
                ],
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_sequences');
    }
};
