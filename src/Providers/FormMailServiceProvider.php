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
        $this->loadRoutesFrom(dirname(__DIR__).'/Http/routes.php');
        // load translations
        $this->loadTranslationsFrom(dirname(__DIR__) . '/Resources/Lang', 'pbc_form_mail');

        // load views
        $this->loadViewsFrom(dirname(__DIR__) . '/Resources/Views', 'pbc_form_mail');

        // publish the config
        $this->publishes([dirname(__DIR__,2) . '/config/form_mail.php' => config_path('form_mail.php')], 'config');

        // publish the migrations
        $this->publishes([realpath(dirname(dirname(__DIR__))) . '/database/migrations' => $this->app->databasePath() . '/migrations'], 'migrations');

        // publish the language file
        $this->publishes([realpath(dirname(dirname(__DIR__))) . '/src/Resources/Lang' => $this->app->basePath() . '/resources/lang/vendor/pbc_form_mail/'], 'language');
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register()
    {
        $this->app->bind('FormMailHelper', 'Pbc\FormMail\Helpers\FormMailHelper');
        $this->app->make('Pbc\FormMail\Http\Controllers\FormMailController');
    }
}
