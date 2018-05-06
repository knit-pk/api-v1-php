<?php

declare(strict_types=1);

namespace App\EntityProcessor\Handler;

use RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

abstract class AbstractConsoleCommandEntityProcessorHandler implements EntityProcessorHandlerInterface
{
    /**
     * @var OutputInterface|null
     */
    private $output;

    /**
     * @var ProgressBar|null
     */
    private $progressBar;

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function setProgressBar(ProgressBar $progressBar): void
    {
        $this->progressBar = $progressBar;
    }

    public function success(object $entity): void
    {
        if (null !== $this->progressBar) {
            $this->progressBar->advance(1);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function error(object $entity, Throwable $error): void
    {
        if (null === $this->output) {
            throw new RuntimeException('Could not print error to console, because no configured output', 0, $error);
        }

        $this->output->writeln(\sprintf('<error>Error: %s</error>', $error->getMessage()));
    }
}
