# AZ Databases Laravel Plugin

This plugin allows you to connect your library website to Alma and pull down database resources and display them using HTML and Javascript.

## Installation

TODO: Clean Up the Installation Instructions

### Composer
Install with composer. Use the following command in your terminal window.
```
composer require seumunday/azdatabases
```
### Add Service Provider

?clear caches here?

Add the following to your service providers in config/app.php

```
Seumunday\Azdatabases\AzdatabasesServiceProvider::class,
```

### Artisan

Use Artisan to publish the vendor files into your site. This allows you to fully customize the views.
```
php artisan vendor:publish --provider="Seumunday\Azdatabases\AzdatabasesServiceProvider"
```

**List of Transfered files:**
* config/azdatabases.php
* app/Console/commands/AzDatabaseImport.php
* resources/views/vendor/azdatabases
    - aznav.blade.php
    - index.blade.php
* resources/assets/js/vendor/azdatabases.js

#### Edit Config File

Insert your OAI url from alma into the newly transfered config file.
```
'url' => 'https://YOURURLHERE'
```
> **NOTE**
> Learn how to get this url by reading these helpful articles from exlibris.
> [Alma OAI Integration API](https://developers.exlibrisgroup.com/alma/integrations/oai)
> [Exlibris OAI Article](https://knowledge.exlibrisgroup.com/Alma/Product_Documentation/Alma_Online_Help_(English)/Integrations_with_External_Systems/030Resource_Management/060Setting_Up_OAI_Integration)

#### Set Up Command
In app/Console/Kernel.php, add the following to protected $commands:
```
\App\Console\Commands\AzDatabaseImport::class,
```

And the following under function schedule
```
$schedule->command('importAZDB')
        ->daily();
```

To populate it instantly, change daily() to everyMinute(), then run the following artisan command.

To run the command:
```
artisan schedule:run
```

> NOTE: The data is populated by accessing the ALMA api, and downloading it ever day. To set this up, you will need to make sure you have set up scheduling. https://laravel.com/docs/5.4/scheduling

*__At this point, you should be able to see html loading at YOURDOMAIN/database__*

### Set Up Javascript
Add the following to Elixer in your gulp file.
```
mix.webpack('vendor/azdatabases.js', 'public/assets/js');
```

If you have Laravel 5.4, it will be in your webpack.js file, and will instead look like this:
```
.js('resources/assets/js/vendor/azdatabases.js', 'public/assets/js')
```

> If you store your JS files else where, make sure to also change the script url in the view.

#### Install vue-router

This allows us to have permalinks to searches and selections.
Run the following in your terminal window:
```
npm install vue-router --save
```

Depending on what version of Laravel you use, here are the commands to compile the javascript.

Laravel 5.3
```
gulp
```

Laravel 5.4
```
npm run watch
```

## Usage

TODO: Write usage instructions

## History

TODO: Write more history

This project was based on [Justin Kells AZ Database project](https://github.com/justinkelly/az_databases). 
