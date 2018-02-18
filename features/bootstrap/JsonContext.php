<?php

declare(strict_types=1);

namespace App\Tests\Feature;

use Behatch\Context\JsonContext as BaseContext;
use RuntimeException;

class JsonContext extends BaseContext
{
    /**
     * @Then the JSON collection should not be empty
     *
     * @throws \Exception
     */
    public function theJsonCollectionShouldNotBeEmpty(): void
    {
        $collection = $this->getJsonCollection();

        $this->assertFalse(empty($collection), 'JSON is an empty collection');
    }

    /**
     * @Then the JSON collection every node :node should be equal to :text
     *
     * @param string       $node
     * @param string|mixed $text
     *
     * @throws \Exception
     */
    public function theJsonCollectionEveryNodeShouldBeEqualTo(string $node, $text): void
    {
        $collection = $this->getJsonCollection();

        $items = \count($collection);
        for ($itemNo = 0; $itemNo < $items; ++$itemNo) {
            $this->theJsonNodeShouldBeEqualTo(\sprintf('[%d].%s', $itemNo, $node), $text);
        }
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    private function getJsonCollection(): array
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, '');

        if (!\is_array($actual)) {
            throw new RuntimeException('JSON is not a collection');
        }

        return $actual;
    }
}
