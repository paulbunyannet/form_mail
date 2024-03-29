# Depreciated, use Laravel's internal mail class instead.

# Form Mail

A laravel package to capture submitted for fields and send out response to a recipient.

## Setup

+ Add providers to your config/app.php file:
```
    Pbc\FormMail\Providers\FormMailServiceProvider::class,
    Pbc\FormMailTemplate\Providers\FormMailTemplateServiceProvider::class,
```
+ Run `php artisan vendor:publish` to get the config and database migration for this package.
+ Run `php artisan migrate` to install the package table

## Config

+ `branding` string used for branding the email message
+ `rules` extra form validation fields
+ `queue` whether to queue this message or send out right away
+ `confirmation` whether to send a confirmation message

## Conventions

### Recipient

This package will use the current route for generating the email recipient of the message. For example, if there is a route is "contact-us" and the current site's `APP_URL` is "http://www.example.com" then the recipient will be "contact-us@example.com".

### Fields

By default the package requires three fields (in addition to CSRF): 

+ `name`, required
+ `email`, required and valid email
+ `fields`, required and must be an array. The fields list is used for labeling fields in the responces. The array should be formatted `['field_name'=>'Field Label']`. If no lable is found for a particular field, the field name will be used. 

Any other required fields can be added to the `config/form_mail.php` config file.

As with language, you can add rules that are specific to a path by using the route name as the ley were the rules are located.

### Branding

Add a graphic/html/whatever to the branding config item and it will be injected at the top of the email message. If branding is missing the branding will default to a language string `':domain :form Form'`.

### Messages

To add a verbage (success message, what to do next, etc.) to the top of the message add a line to the `resources/lang/vendor/pbc_form_mail/en/body.php` using the route name and the the following keys:

+ `recipient` used for email going to recipient
+ `sender` used in both the return value from `Pbc\FormMail\Http\Controllers\FormMailController@requestHandler` and the confirmation message if option is turned on.

For example, if you had a route to `App\Http\Controllers\FormController@send` you would format your message array like:
```
'form' => [
        'send' => [
            'sender' => 'Thanks for filling out the :form form,  we will get back to you as soon as possible! This is a summary of the form you submitted. A copy of this form will be forwarded to :recipient.',
            'recipient' => 'A new response from the :form was submitted at :time from :domain.',
            'subject' => [
                'sender' => 'Your form message has been received!',
                'recipient' => 'A new form submission created on :url',
            ]
        ]
```

### Custom Subject Line

There is a helper that will auto create a subject for the message. If you want a custom one add a array key `subject` with keys `sender` and `recipient` like the above example.
