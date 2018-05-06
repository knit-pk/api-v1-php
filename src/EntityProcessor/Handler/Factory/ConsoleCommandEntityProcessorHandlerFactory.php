<?php

declare(strict_types=1);

namespace App\EntityProcessor\Handler\Factory;

use App\EntityProcessor\Handler\AbstractConsoleCommandEntityProcessorHandler;
use App\EntityProcessor\Handler\EntityProcessorHandlerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleCommandEntityProcessorHandlerFactory implements EntityProcessorHandlerFactoryInterface
{
    private $decorated;

    public function __construct(EntityProcessorHandlerFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function make(string $handler, array $options = []): EntityProcessorHandlerInterface
    {
        $handlerInstance = $this->decorated->make($handler, $options);

        if ($handlerInstance instanceof AbstractConsoleCommandEntityProcessorHandler) {
            $this->configure($handlerInstance, $options['output'] ?? null, $options['progressBar'] ?? null);
        }

        return $handlerInstance;
    }

    private function configure(AbstractConsoleCommandEntityProcessorHandler $handler, ?OutputInterface $output, ?ProgressBar $progressBar): void
    {
        if (null !== $output) {
            $handler->setOutput($output);
        }

        if (null !== $progressBar) {
            $handler->setProgressBar($progressBar);
        }
    }
}
