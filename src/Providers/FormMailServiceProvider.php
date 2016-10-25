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
        // load translations
        $this->loadTranslationsFrom(dirname(__DIR__) . '/Resources/Lang', 'pbc_form_mail');
        
        // load views
        $this->loadViewsFrom(dirname(__DIR__) . '/Resources/Views', 'pbc_form_mail');
        
        // publish the config
        $this->publishes([dirname(__DIR__) .'/Config/FormMail.php' => config_path('form_mail.php')], 'config');
        
        // publish the migrations
        $this->publishes([realpath(dirname(dirname(__DIR__))) . '/database/migrations' => $this->app->databasePath() . '/migrations'], 'migrations');

        // publish the language file
        $this->publishes([realpath(dirname(dirname(__DIR__))) . '/src/Resources/Lang' => $this->app->basePath() . '/resources/lang/vendor/pbc_form_mail/'], 'language');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include dirname(__DIR__).'/Http/routes.php';
        $this->app->bind('formMailHelper', 'Pbc\FormMail\Helpers\FormMailHelper');
        $this->app->make('Pbc\FormMail\Http\Controllers\FormMailController');
    }
}
