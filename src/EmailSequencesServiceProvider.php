<?php

declare(strict_types=1);

namespace A2ZWeb\EmailSequences;

use A2ZWeb\EmailSequences\Console\Commands\BackfillEnrollments;
use A2ZWeb\EmailSequences\Console\Commands\DeliverEmailSequences;
use A2ZWeb\EmailSequences\Nova\EmailSequence;
use A2ZWeb\EmailSequences\Nova\EmailSequenceDelivery;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;

class EmailSequencesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/email-sequences.php', 'email-sequences');
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'email-sequences');
        $this->registerAutoEnrollment();
        $this->registerCommands();
        $this->registerSchedule();
        $this->registerNova();
    }

    private function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/email-sequences.php' => config_path('email-sequences.php'),
        ], 'email-sequences-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/email-sequences'),
        ], 'email-sequences-views');
    }

    private function registerAutoEnrollment(): void
    {
        $userModel = (string) config('email-sequences.user_model');

        Event::listen("eloquent.created: {$userModel}", function ($user): void {
            // Re-checked at runtime so the flag can be toggled per request/test.
            if (! config('email-sequences.auto_enroll', true)) {
                return;
            }

            if (method_exists($user, 'enrollInEmailSequences')) {
                $user->enrollInEmailSequences();
            }
        });
    }

    private function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            DeliverEmailSequences::class,
            BackfillEnrollments::class,
        ]);
    }

    private function registerSchedule(): void
    {
        if (! config('email-sequences.schedule.deliver', true)) {
            return;
        }

        $this->app->booted(function (): void {
            $schedule = $this->app->make(Schedule::class);
            $event = $schedule->command('emails:deliver-sequences');

            match ((string) config('email-sequences.schedule.cron', 'hourly')) {
                'everyMinute' => $event->everyMinute(),
                'daily' => $event->daily(),
                'hourly' => $event->hourly(),
                default => $event->cron((string) config('email-sequences.schedule.cron')),
            };
        });
    }

    private function registerNova(): void
    {
        if (! config('email-sequences.nova.register_resources', true) || ! class_exists(Nova::class)) {
            return;
        }

        Nova::resources([
            EmailSequence::class,
            EmailSequenceDelivery::class,
        ]);
    }
}
