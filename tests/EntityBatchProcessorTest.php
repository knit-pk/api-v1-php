<?php

declare(strict_types=1);

namespace App\Tests;

use App\EntityProcessor\EntityBatchProcessor;
use App\EntityProcessor\Handler\EntityProcessorHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class EntityBatchProcessorTest extends TestCase
{
    private const FLUSH_AFTER = 5;

    /**
     * @var EntityManagerInterface|ObjectProphecy
     */
    private $emProphecy;

    /**
     * @var EntityBatchProcessor
     */
    private $processor;

    /**
     * @var EntityProcessorHandlerInterface|ObjectProphecy
     */
    private $handlerProphecy;

    protected function setUp(): void
    {
        $this->emProphecy = $this->prophesize(EntityManagerInterface::class);
        $this->handlerProphecy = $this->prophesize(EntityProcessorHandlerInterface::class);

        /** @var EntityManagerInterface $emMock */
        $emMock = $this->emProphecy->reveal();
        $this->processor = new EntityBatchProcessor($emMock, self::FLUSH_AFTER);
    }

    public function emCalledTimesProvider(): array
    {
        return [
            [0, 0],
            [1, 1],
            [2, 1],
            [3, 1],
            [4, 1],
            [5, 1],
            [6, 2],
            [15, 3],
            [16, 4],
        ];
    }

    /**
     * @dataProvider emCalledTimesProvider
     *
     * @param int $entriesCount
     * @param int $expectedEmCalls
     */
    public function testProcessAllEntries(int $entriesCount, int $expectedEmCalls): void
    {
        $entryMock = $this->prophesize('object')->reveal();
        $entryCollectionMock = \array_fill(0, $entriesCount, $entryMock);

        $this->handlerProphecy->handle($entryMock)->shouldBeCalledTimes($entriesCount);
        $this->handlerProphecy->success($entryMock)->shouldBeCalledTimes($entriesCount);

        $this->emProphecy->flush()->shouldBeCalledTimes($expectedEmCalls);
        $this->emProphecy->clear()->shouldBeCalledTimes($expectedEmCalls);

        $this->processor->process($this->handlerProphecy->reveal(), $entryCollectionMock);
    }

    public function testProcessWithError(): void
    {
        $entriesCount = 1;
        $entryMock = $this->prophesize('object')->reveal();
        $entryCollectionMock = \array_fill(0, $entriesCount, $entryMock);

        $exception = new Exception();
        $this->handlerProphecy->handle($entryMock)->willThrow($exception)->shouldBeCalled();
        $this->handlerProphecy->error($entryMock, $exception)->shouldBeCalled();
        $this->handlerProphecy->success($entryMock)->shouldBeCalled();

        $this->processor->process($this->handlerProphecy->reveal(), $entryCollectionMock);
    }
}
