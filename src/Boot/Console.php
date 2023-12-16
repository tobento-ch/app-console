<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);
 
namespace Tobento\App\Console\Boot;

use Tobento\App\Boot;
use Tobento\App\Migration\Boot\Migration;
use Tobento\Service\Console\ConsoleInterface;
use Tobento\Service\Console\ConsoleFactoryInterface;
use Tobento\Service\Console\CommandInterface;
use Tobento\Service\Console\Symfony;

/**
 * Console
 */
class Console extends Boot
{
    public const INFO = [
        'boot' => [
            'installs and loads console config file',
            'implements console interfaces',
        ],
    ];

    public const BOOT = [
        Migration::class,
    ];
    
    /**
     * Indicates if the app is running in the console.
     *
     * @var null|bool
     */
    protected null|bool $runningInConsole = null;
    
    /**
     * Indicates if the boot console is booted.
     *
     * @var bool
     */
    protected bool $isBooted = false;

    /**
     * Boot application services.
     *
     * @param Migration $migration
     * @return void
     */
    public function boot(Migration $migration): void
    {
        // install migration:        
        $migration->install(\Tobento\App\Console\Migration\Console::class);
        
        // interfaces:
        $this->app->set(ConsoleFactoryInterface::class, Symfony\ConsoleFactory::class);
        
        $this->app->set(ConsoleInterface::class, function(): ConsoleInterface {
            return $this->app->get(ConsoleFactoryInterface::class)->createConsole(name: 'app');
        });

        $this->isBooted = true;
    }
    
    /**
     * Returns the boot priority.
     *
     * @return int
     */
    public function priority(): int
    {
        // on boot:
        // set the default priority so to keep boots order.
        
        // on terminate:
        // we set a low priority so that terminate method gets called first
        // and run the console. Otherwise, other boots will get terminated before
        // such as http boot.
        return $this->isBooted ? -100000 : 1000;
    }
    
    /**
     * Terminate application services.
     *
     * @return void
     */
    public function terminate(): void
    {
        if ($this->app->getEnvironment() === 'testing') {
            return;
        }
        
        if (str_contains($_SERVER['argv'] ?? '', 'phpunit')) {
            return;
        }
        
        if ($this->runningInConsole()) {
            $status = $this->app->get(ConsoleInterface::class)->run();
            exit($status);
        }
    }
    
    /**
     * Add a command.
     *
     * @param string|CommandInterface $command
     * @return static $this
     */
    public function addCommand(string|CommandInterface $command): static
    {
        $this->app->get(ConsoleInterface::class)->addCommand($command);
        
        return $this;
    }
    
    /**
     * Determine if the app is running in the console.
     *
     * @return bool
     */
    public function runningInConsole(): bool
    {
        if ($this->runningInConsole === null) {
            $this->runningInConsole = \PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg';
        }

        return $this->runningInConsole;
    }
}