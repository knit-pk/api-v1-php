<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Entity\Article\SynchronizeCategories;
use App\Command\Entity\Article\UpdateCommentsCount;
use App\Entity\Article;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class EntitiesSynchronizeCommand extends Command
{
    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName('app:entities:synchronize')
            ->setDescription('Synchronizes entities and their cached fields');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Running commands to synchronize all entities');

        $batchProcessing = $this->getApplication()->find('app:entities:process');

        $batchProcessing->run(new ArrayInput([
            'entity' => Article::class,
            'action' => UpdateCommentsCount::class,
        ]), $output);

        $batchProcessing->run(new ArrayInput([
            'entity' => Article::class,
            'action' => SynchronizeCategories::class,
        ]), $output);
    }
}
