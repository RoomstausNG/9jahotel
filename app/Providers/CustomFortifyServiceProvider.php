<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\FortifyServiceProvider as FortifyProvider;


class CustomFortifyServiceProvider extends FortifyProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->register(CustomFortifyServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        parent::boot();

        $this->customValidationMessages();
    }


    protected function customValidationMessages()
    {
        $customMessages = [
            // Add your custom validation messages here
            'username.required' => 'Kindly put your username or email address',
            'username.exists' => 'There is no user with the username or email address entered',
            'code.required' => 'Hotel Code is required',
            'code.exists' => 'There is no hotel with the given code'
            // Add more custom messages as needed
        ];

        // Merge the custom messages with existing validation messages
        $validationMessages = array_merge(
            trans('fortify::validation'), // Default Fortify messages
            $customMessages
        );

        // Set the custom validation messages
        $this->app['validator']->setCustomMessages($validationMessages);
    }
}