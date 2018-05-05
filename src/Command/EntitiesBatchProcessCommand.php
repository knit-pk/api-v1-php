<?php

declare(strict_types=1);

namespace App\Command;

use App\EntityBatchProcessor\EntityBatchProcessor;
use App\EntityBatchProcessor\Handler\AbstractConsoleCommandBatchProcessorHandler;
use App\EntityBatchProcessor\Handler\EntityBatchProcessorHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EntitiesBatchProcessCommand extends Command
{
    private const APP_ENTITY_NAMESPACE = 'App\\Entity';
    private const APP_COMMAND_ENTITY_NAMESPACE = 'App\\Command\\Entity';
    private const DOT_PHP = '.php';

    private $em;
    private $batchProcessor;

    public function __construct(EntityManagerInterface $em, EntityBatchProcessor $batchProcessor)
    {
        parent::__construct();

        $this->em = $em;
        $this->batchProcessor = $batchProcessor;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName('app:entities:batch:process')
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
        $entityClass = \str_ireplace([self::APP_ENTITY_NAMESPACE, self::DOT_PHP], '', $input->getArgument('entity'));
        $handlerClass = \str_ireplace([self::APP_COMMAND_ENTITY_NAMESPACE, self::DOT_PHP], '', $input->getArgument('action'));

        $parsedEntityClass = $this->parseClass($entityClass, self::APP_ENTITY_NAMESPACE);
        $handlerNamespace = \str_replace(self::APP_ENTITY_NAMESPACE, self::APP_COMMAND_ENTITY_NAMESPACE, $parsedEntityClass);
        $parsedHandlerClass = $this->parseClass($handlerClass, $handlerNamespace);

        if (!\class_exists($parsedEntityClass)) {
            throw new InvalidArgumentException(\sprintf('Entity %s does not exist', $parsedEntityClass));
        }

        if (!\class_exists($parsedHandlerClass)) {
            throw new InvalidArgumentException(\sprintf('Handler %s does not exist', $parsedHandlerClass));
        }

        $output->writeln(\sprintf('<info>Running action %s on entity %s</info>', $parsedHandlerClass, $parsedEntityClass));

        $countQuery = $this->em->createQuery(\sprintf('SELECT COUNT(e) as count FROM %s e', $parsedEntityClass));
        $entityCollectionQuery = $this->em->createQuery(\sprintf('SELECT e FROM %s e', $parsedEntityClass));

        $count = $countQuery->getResult()[0]['count'];
        $entries = $this->getEntries($entityCollectionQuery->iterate());
        $progressBar = $io->createProgressBar($count);

        /** @var EntityBatchProcessorHandlerInterface $handler */
        $handler = new $parsedHandlerClass();

        if ($handler instanceof AbstractConsoleCommandBatchProcessorHandler) {
            $handler->setOutput($output);
            $handler->setProgressBar($progressBar);
        }

        $this->batchProcessor->process($handler, $entries);

        $progressBar->finish();
    }

    private function getEntries(IterableResult $result): Generator
    {
        foreach ($result as [0 => $entry]) {
            yield $entry;
        }
    }

    private function parseClass(string $class, string $namespace): string
    {
        return \sprintf('%s\\%s', $namespace, \ucfirst(\trim($class, ' \\')));
    }
}
