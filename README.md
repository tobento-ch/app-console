# App Console

Console support for the app using the [Console Service](https://github.com/tobento-ch/service-console).

## Table of Contents

- [Getting Started](#getting-started)
    - [Requirements](#requirements)
- [Documentation](#documentation)
    - [App](#app)
    - [Console Boot](#console-boot)
        - [Creating Commands](#creating-commands)
        - [Adding Commands](#adding-commands)
        - [Invoke Commands](#invoke-commands)
- [Credits](#credits)
___

# Getting Started

Add the latest version of the app console project running this command.

```
composer require tobento/app-console
```

## Requirements

- PHP 8.0 or greater

# Documentation

## App

Check out the [**App Skeleton**](https://github.com/tobento-ch/app-skeleton) if you are using the skeleton.

You may also check out the [**App**](https://github.com/tobento-ch/app) to learn more about the app in general.

## Console Boot

The console boot does the following:

* creates app file in the root directory if not exist
* implements console interfaces

```php
use Tobento\App\AppFactory;
use Tobento\Service\Console\ConsoleInterface;
use Tobento\Service\Console\ConsoleFactoryInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots
$app->boot(\Tobento\App\Console\Boot\Console::class);
$app->booting();

// Implemented interfaces:
$consoleFactory = $app->get(ConsoleFactoryInterface::class);
$console = $app->get(ConsoleInterface::class);

// Run the app
$app->run();
```

If you are not using the [App Skeleton](https://github.com/tobento-ch/app-skeleton/) you may adjust the ```ap``` file in the root directory with the path to your app:

```php
// Get and run the application.
// (require __DIR__.'/app/app.php')->run();
(require __DIR__.'/path/to/app.php')->run();
```

### Creating Commands

Check out the [Console Service - Creating Commands](https://github.com/tobento-ch/service-console#creating-commands) section to learn more about creating commands.

### Adding Commands

You can add commands in severval ways:

**Using the app**

You may use the app ```on``` method to register commands only if the console is requested.

```php
use Tobento\App\AppFactory;
use Tobento\Service\Console\ConsoleInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Console\Boot\Console::class);

// Adding commands:
$app->on(ConsoleInterface::class, function(ConsoleInterface $console) {
    $console->addCommand($command);
});

// Run the app
$app->run();
```

**Using the console boot**

```php
use Tobento\App\Boot;
use Tobento\App\Console\Boot\Console;

class AnyServiceBoot extends Boot
{
    public const BOOT = [
        // you may ensure the console boot.
        Console::class,
    ];
    
    public function boot(Console $console)
    {
        // you may add commands only if running in console:
        if ($console->runningInConsole()) {
            $console->addCommand($command);
        }
    }
}
```

### Invoke Commands

To invoke application command just run:

```
php ap command:name
```

To get a list of available commands:

```
php ap list
```

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)