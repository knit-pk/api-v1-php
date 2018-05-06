<?php

declare(strict_types=1);

namespace App\Command;

use App\EntityProcessor\EntityBatchProcessor;
use App\EntityProcessor\Handler\Factory\EntityProcessorHandlerFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class EntitiesProcessCommand extends Command
{
    private const APP_ENTITY_NAMESPACE = 'App\\Entity';
    private const APP_COMMAND_ENTITY_NAMESPACE = 'App\\Command\\Entity';

    private $em;
    private $processor;
    private $handlerFactory;

    public function __construct(EntityManagerInterface $em, EntityBatchProcessor $processor, EntityProcessorHandlerFactoryInterface $handlerFactory)
    {
        parent::__construct();

        $this->em = $em;
        $this->processor = $processor;
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName('app:entities:process')
            ->addArgument('entity', InputArgument::REQUIRED, 'class of updated entity')
            ->addArgument('action', InputArgument::REQUIRED, 'action handler for selected entity');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $entityClassName = $input->getArgument('entity');
        $handlerClassName = $input->getArgument('action');

        if (!\class_exists($entityClassName)) {
            $entityClassName = $this->addToNamespace(
                $this->parseClassShortName($entityClassName),
                self::APP_ENTITY_NAMESPACE
            );
        }

        if (!\class_exists($handlerClassName)) {
            $handlerClassName = $this->addToNamespace(
                $this->parseClassShortName($handlerClassName),
                $this->addToNamespace(
                    $this->parseClassShortName($entityClassName),
                    self::APP_COMMAND_ENTITY_NAMESPACE
                )
            );
        }

        if (!\class_exists($entityClassName)) {
            throw new InvalidArgumentException(\sprintf('Entity %s does not exist', $entityClassName));
        }

        $output->writeln(\sprintf('<info>Running action %s on entity %s</info>', $handlerClassName, $entityClassName));

        $countQuery = $this->em->createQuery(\sprintf('SELECT COUNT(e) as count FROM %s e', $entityClassName));
        $entityCollectionQuery = $this->em->createQuery(\sprintf('SELECT e FROM %s e', $entityClassName));

        $count = $countQuery->getResult()[0]['count'];
        $entries = $this->getEntries($entityCollectionQuery->iterate());
        $progressBar = $io->createProgressBar($count);

        $handler = $this->handlerFactory->make($handlerClassName, [
            'progressBar' => $progressBar,
            'output' => $output,
        ]);

        $this->processor->process($handler, $entries);

        $progressBar->finish();
        $io->newLine(2);
    }

    private function addToNamespace(string $addition, string $namespace): string
    {
        return \sprintf('%s\\%s', \trim($namespace, ' \\'), \trim($addition, ' \\'));
    }

    private function parseClassShortName(string $className): string
    {
        $path = \explode('\\', \trim($className, ' \\'));

        return \ucfirst(\array_pop($path));
    }

    private function getEntries(IterableResult $result): Generator
    {
        foreach ($result as [0 => $entry]) {
            yield $entry;
        }
    }
}
