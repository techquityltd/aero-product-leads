<?php

namespace Techquity\AeroProductLeads;

use Aero\Admin\BulkAction;
use Aero\Store\Models\Location;
use Closure;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Router;
use Aero\Catalog\Models\Tag;
use Aero\Common\Facades\Settings;
use Aero\Admin\AdminModule;
use Aero\Common\Providers\ModuleServiceProvider;
use Aero\Checkout\Http\Responses\CheckoutSuccess;
use Techquity\AeroProductLeads\BulkActions\ExportLeadsBulkAction;
use Techquity\AeroProductLeads\Console\Commands\UpdateLeadCoordinates;
use Techquity\AeroProductLeads\Jobs\UpdateLeadCoordinatesJob;
use Techquity\AeroProductLeads\Models\ProductLead;
use Techquity\AeroProductLeads\Console\Commands\SendLeadEmails;
use Techquity\AeroProductLeads\ResourceLists\ProductLeadResourceList;

class ServiceProvider extends ModuleServiceProvider
{
    public function setup()
    {
        $this->loadRoutes();
        $this->loadModule();
        $this->loadSettings();
        $this->loadMigrations();
        $this->loadViews();
        $this->loadSchedule();
        $this->registerCommands();
        $this->extendCheckoutSuccess();
        $this->createBulkActions();
    }

    private function loadRoutes()
    {
        Router::addAdminRoutes(__DIR__.'/../routes/admin.php');
    }

    private function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    private function loadViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'product-leads');
    }

    private function loadModule()
    {
        AdminModule::create('product_leads')
            ->title('Product Leads')
            ->summary('View your stores product leads')
            ->route('admin.productleads');
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
            $group->eloquent('ignore-locations', Location::class)
                ->hint('Locations that you do not want to use.')
                ->multiple();
            $group->boolean('fallback-email-enabled')
                ->hint('If no store locations can be found for an order, send to a fallback email instead.')
                ->default(true);
            $group->string('fallback-email')
                ->hint('Email you want the fallback leads to go to.');
            $group->string('send-emails-cron')->default('0 9 * * *');
            $group->string('update-coordinates-cron')->default('*/30 * * * *');
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
        CheckoutSuccess::extend(static function ($content, Closure $next) {

            $leadTags = setting('product-leads.lead-tags');
    
            if (empty($leadTags) || !$leadTags->count()) {
                return $next($content);
            }
    
            $leadTagIds = $leadTags->pluck('id');
            $order = $content->cart->order();
    
            foreach ($order->items as $item) {

                if (!isset($item->buyable, $item->buyable->product)) {
                    continue;
                }
    
                $product = $item->buyable->product;

                if (!isset($order->shippingAddress) || !$order->shippingAddress->postcode) {
                    continue;
                }
    
                // Check if the product's tags intersect with the lead-tags
                if ($product->tags->pluck('id')->intersect($leadTagIds)->isNotEmpty()) {
                    try {
                        ProductLead::create([
                            'order_id' => $order->id,
                            'order_item_id' => $item->id,
                            'postcode' => $order->shippingAddress->postcode,
                        ]);
                    } catch (\Exception $e) {
                        // Log::error('Failed to create product lead', ['error' => $e->getMessage()]);
                    }
                }
            }
    
            // Optionally, dispatch the coordinates update job after creating leads
            // try {
            //     dispatch(new UpdateLeadCoordinatesJob());
            // } catch (\Exception $e) {
            //     // Log the error but do not interrupt the checkout
            //     // Log::error('Failed to dispatch UpdateLeadCoordinatesJob', ['error' => $e->getMessage()]);
            // }
    
            return $next($content);
        });
    }

    private function loadSchedule()
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            if (setting('product-leads.enabled')) {
                $schedule->command('product-leads:update-lead')->cron(setting('product-leads.update-coordinates-cron'));
                $schedule->command('product-leads:send-emails')->cron(setting('product-leads.send-emails-cron'));
            }
        });
    }

    private function createBulkActions()
    {
        BulkAction::create(ExportLeadsBulkAction::class, ProductLeadResourceList::class)
            ->title('Export Leads');
    }
}
