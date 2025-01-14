<?php

namespace Techquity\AeroProductLeads;

use Aero\Catalog\Models\Tag;
use Aero\Common\Facades\Settings;
use Aero\Common\Providers\ModuleServiceProvider;
use Illuminate\Routing\Router;

class ServiceProvider extends ModuleServiceProvider
{
    public function setup()
    {
        $this->loadSettings();

        $this->loadMigrations();

        $this->loadViews();
    }

    private function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    private function loadViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'product-leads');
    }

    // private function registerMacros()
    // {
    //     Location::macro('product-leads', function() {
    //         return $this->belongsToMany(Career::class);
    //     });
    // }

    private function loadSettings()
    {
        Settings::group('product-leads', function ($group) {
            $group->boolean('enabled')->default(true);
            $group->eloquent('lead-tags', Tag::class)
                ->hint('Tags that are linked to the products you want to build leads for')
                ->multiple();
            $group->string('google-maps-api-key');
            $group->string('queue')->default('product-leads');
            $group->integer('first-email')
                ->hint('The amount of days to wait before sending the first lead email')
                ->default(7);
            $group->boolean('fallback-email-enabled')->default(true);
            $group->string('fallback-email')
                ->hint('What email do you want the fallback leads to go too?');
        });
    }
}
