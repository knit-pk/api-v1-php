<?php

declare(strict_types=1);

namespace App\EntityBatchProcessor;

use App\EntityBatchProcessor\Handler\EntityBatchProcessorHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

final class EntityBatchProcessor
{
    private const FLUSH_AFTER = 20;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param EntityBatchProcessorHandlerInterface $handler
     * @param iterable|object[]                    $entries
     */
    public function process(EntityBatchProcessorHandlerInterface $handler, iterable $entries): void
    {
        $counter = 0;
        foreach ($entries as $entry) {
            try {
                $handler->handle($entry);
            } catch (Throwable $err) {
                $handler->error($entry, $err);
            }

            $handler->success($entry);

            ++$counter;
            if (0 === $counter % self::FLUSH_AFTER) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->em->clear();
    }
}
