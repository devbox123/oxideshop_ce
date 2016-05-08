<?php

namespace OxidEsales\Eshop\Core;

use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\HandleClassNameInflector;
use OxidEsales\Eshop\Application\Command\AddToBasketCommand;
use OxidEsales\Eshop\Application\Command\AddToBasketHandler;

class CommandBus
{
    private $bus;

    public function __construct()
    {
        $locator = new InMemoryLocator();
        $locator->addHandler(
            new AddToBasketHandler(),
            AddToBasketCommand::class
        );

        $handlerMiddleware = new \League\Tactician\Handler\CommandHandlerMiddleware(
            new ClassNameExtractor(),
            $locator,
            new HandleClassNameInflector()
        );

        $this->bus = new \League\Tactician\CommandBus([$handlerMiddleware]);
    }

    public function handle($command)
    {
        return $this->bus->handle($command);
    }
}


