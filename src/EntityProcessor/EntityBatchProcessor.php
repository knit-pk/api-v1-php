<?php

declare(strict_types=1);

namespace App\EntityProcessor;

use App\EntityProcessor\Handler\EntityProcessorHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

final class EntityBatchProcessor
{
    private const DEFAULT_FLUSH_AFTER = 20;

    private $em;
    private $flushAfter;

    public function __construct(EntityManagerInterface $em, int $flushAfter = self::DEFAULT_FLUSH_AFTER)
    {
        $this->em = $em;
        $this->flushAfter = $flushAfter;
    }

    /**
     * @param EntityProcessorHandlerInterface $handler
     * @param iterable|object[]               $entries
     */
    public function process(EntityProcessorHandlerInterface $handler, iterable $entries): void
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
            if (0 === $counter % $this->flushAfter) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->em->clear();
    }
}
