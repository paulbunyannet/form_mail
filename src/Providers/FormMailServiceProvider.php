<?php

namespace Pbc\FormMail\Providers;

use Illuminate\Support\ServiceProvider;

class FormMailServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(dirname(__DIR__) . '/Resources/Lang', 'pbc_form_mail');
        $this->loadViewsFrom(dirname(__DIR__) . '/Resources/Views', 'pbc_form_mail');
        $this->mergeConfigFrom(dirname(__DIR__) .'/Config/FormMail.php', 'form_mail');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include dirname(__DIR__).'/Http/routes.php';
        $this->app->make('Pbc\FormMail\Http\Controllers\FormMailController');
    }
}
