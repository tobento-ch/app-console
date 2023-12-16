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

namespace Tobento\App\Console\Test\Boot;

use PHPUnit\Framework\TestCase;
use Tobento\App\Console\Boot\Console;
use Tobento\Service\Console\ConsoleInterface;
use Tobento\Service\Console\ConsoleFactoryInterface;
use Tobento\Service\Console\Command;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\App\Boot;
use Tobento\Service\Filesystem\Dir;
use Tobento\Service\Filesystem\File;

class ConsoleTest extends TestCase
{    
    protected function createApp(): AppInterface
    {
        (new Dir())->create(__DIR__.'/../../app/');
        
        $app = (new AppFactory())->createApp();
        
        $app->dirs()
            ->dir(realpath(__DIR__.'/../../'), 'root')
            ->dir($app->dir('root').'app', 'app')
            ->dir($app->dir('app').'config', 'config', group: 'config')
            ->dir($app->dir('root').'vendor', 'vendor')
            // for testing only we add public within app dir.
            ->dir($app->dir('app').'public', 'public');
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../../app/');
        (new File(__DIR__.'/../../ap'))->delete();
    }
    
    public function testInterfacesAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(Console::class);
        $app->booting();
        
        $this->assertInstanceof(ConsoleInterface::class, $app->get(ConsoleInterface::class));
        $this->assertInstanceof(ConsoleFactoryInterface::class, $app->get(ConsoleFactoryInterface::class));
    }
    
    public function testAddingCommandUsingApp()
    {
        $app = $this->createApp();
        $app->boot(Console::class);
        $app->booting();
        
        $app->on(ConsoleInterface::class, function(ConsoleInterface $console) {
            
            $command = (new Command(name: 'sample'))
                ->handle(function(): int {
                    return 0;
                });
            
            $console->addCommand($command);
        });
        
        $this->assertTrue($app->get(ConsoleInterface::class)->hasCommand('sample'));
    }
    
    public function testAddingCommandUsingBoot()
    {
        $app = $this->createApp();
        
        $serviceBoot = new class($app) extends Boot {
            public const BOOT = [
                // you may ensure the console boot.
                Console::class,
            ];

            public function boot(Console $console)
            {
                if (! $console->runningInConsole()) {
                    return;
                }
                
                $command = (new Command(name: 'sample'))
                    ->handle(function(): int {
                        return 0;
                    });
                
                $console->addCommand($command);
            }
        };
        
        $app->boot($serviceBoot);
        $app->booting();
        
        $this->assertTrue($app->get(ConsoleInterface::class)->hasCommand('sample'));
    }
}