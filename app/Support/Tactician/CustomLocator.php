<?php

namespace App\Support\Tactician;

use League\Tactician\Exception\MissingHandlerException;
use Joselfonseca\LaravelTactician\Locator\LocatorInterface;

/**
 * Custom Handler Locator for Laravel-Tactician that allows
 * queries and commands to be in /Query o /Command and handlers
 * in /Handler, keeping a clean DDD separation.
 */
class CustomLocator implements LocatorInterface
{
    public function getHandlerForCommand($commandName)
    {
        $parts = explode('\\', $commandName);
        $command_name = array_pop($parts);
        $file = array_pop($parts); // GetUserByIdQuery
        $contextPath = implode('\\', $parts);
        $handlerClass = $contextPath . '\\Handler\\' . $command_name . 'Handler';

        if (class_exists($handlerClass)) {
            return app($handlerClass);
        }

        throw MissingHandlerException::forCommand($commandName);
    }

    public function getHandlers()
    {
        return [];
    }
    public function addHandler($commandName, $handler){}

    public function addHandlers(array $handlers){}
}


