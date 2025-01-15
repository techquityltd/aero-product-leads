# Aero Product Leads

This package allows you to assign tagged products to leads, the leads are then emailed to the nearest location/store.

### Installation

Install the package via the Composer package manager:

```shell
composer require techquity/aero-product-leads
```

#### Migrations

Once the package has been installed, you will also need to run the migrations that are included. We recommend running this command after any update to the package in order to stay current with changes to table structures etc.:

```shell
php artisan migrate --force
```

### Settings

You can access the settings screen for this package by going to *Configuration* > *Manage Settings* > *Product Leads* in the Aero admin.
