<?php

namespace Techquity\AeroProductLeads;

use Illuminate\Console\Scheduling\Schedule;
use Aero\Catalog\Models\Tag;
use Aero\Common\Facades\Settings;
use Aero\Common\Providers\ModuleServiceProvider;
use Aero\Checkout\Http\Responses\CheckoutSuccess;
use Techquity\AeroProductLeads\Console\Commands\UpdateLeadCoordinates;
use Techquity\AeroProductLeads\Jobs\UpdateLeadCoordinatesJob;
use Techquity\AeroProductLeads\Models\ProductLead;
use Techquity\AeroProductLeads\Console\Commands\SendLeadEmails;

class ServiceProvider extends ModuleServiceProvider
{
    public function setup()
    {
        $this->loadSettings();
        $this->loadMigrations();
        $this->loadViews();
        $this->loadSchedule();
        $this->registerCommands();
        $this->extendCheckoutSuccess();
    }

    private function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    private function loadViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'product-leads');
    }

    private function loadSettings()
    {
        Settings::group('product-leads', function ($group) {
            $group->boolean('enabled')->default(true);
            $group->eloquent('lead-tags', Tag::class)
                ->hint('Tags that are linked to the products you want to build leads for.')
                ->multiple();
            $group->string('google-maps-api-key')
                ->hint('Requires Geocoding API enabled.');
            $group->string('queue')->default('product-leads');
            $group->integer('email-wait-time')
                ->hint('The amount of days to wait before sending the first lead email.')
                ->default(7);
            $group->integer('store-radius')
                ->hint('Radius in miles to search for the nearest store location.')
                ->default(20);
            $group->boolean('fallback-email-enabled')
                ->hint('If no store locations can be found for an order, send to a fallback email instead.')
                ->default(true);
            $group->string('fallback-email')
                ->hint('Email you want the fallback leads to go to.');
        });
    }

    private function registerCommands()
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                SendLeadEmails::class,
                UpdateLeadCoordinates::class
            ]);
        }
    }

    private function extendCheckoutSuccess()
    {
        CheckoutSuccess::extend(function ($builder) {
            $builder->afterComplete(function ($checkout, $order) {
                $leadTags = setting('product-leads.lead-tags');

                foreach ($order->items as $item) {
                    if ($item->product && $item->product->tags->pluck('id')->intersect($leadTags)->isNotEmpty()) {
                        ProductLead::create([
                            'order_id' => $order->id,
                            'order_item_id' => $item->id,
                            'postcode' => $order->shippingAddress->postcode,
                        ]);
                    }
                }

                // dispatch(new UpdateLeadCoordinatesJob());
            });
        });
    }

    private function loadSchedule()
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            if (setting('product-leads.enabled')) {
                $schedule->job(new UpdateLeadCoordinatesJob())->everyThirtyMinutes();
                $schedule->command('product-leads:send-emails')->dailyAt('09:00');
            }
        });
    }
}
