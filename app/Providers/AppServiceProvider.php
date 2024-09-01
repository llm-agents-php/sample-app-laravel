<?php

namespace App\Providers;

use App\Event\ChatEventsListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Psr\EventDispatcher\EventDispatcherInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EventDispatcherInterface::class, function () {
            return new class implements EventDispatcherInterface {
                public function dispatch(object $event): object
                {
                    Event::dispatch($event);
                    return $event;
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::subscribe(ChatEventsListener::class);
    }
}
