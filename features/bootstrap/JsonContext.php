<?php

declare(strict_types=1);

namespace App\Tests\Feature;

use Behatch\Context\JsonContext as BaseContext;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class JsonContext extends BaseContext
{
    /**
     * @Then the JSON collection :collectionNode should not be empty
     *
     * @param string $collectionNode
     *
     * @throws \Exception
     */
    public function theJsonCollectionShouldNotBeEmpty(string $collectionNode): void
    {
        $collection = $this->getJsonCollection($collectionNode);

        $this->assertFalse(empty($collection), 'JSON is an empty collection');
    }

    /**
     * @Then the JSON collection should not be empty
     *
     * @throws \Exception
     */
    public function theJsonRootCollectionShouldNotBeEmpty(): void
    {
        $this->theJsonCollectionShouldNotBeEmpty('');
    }

    /**
     * @Then the JSON node :node should be a valid uuid
     *
     * @param string $node
     *
     * @throws \Exception
     */
    public function theJsonNodeShouldBeValidUuid(string $node): void
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, $node);

        if (!Uuid::isValid($actual)) {
            throw new RuntimeException(\sprintf('The json node "%s" value %s is not a valid Uuid4', $node, \json_encode($actual)));
        }
    }

    /**
     * @Then the JSON items in collection :collectionNode should have node :node that is equal to :value
     *
     * @param string       $collectionNode
     * @param string       $node
     * @param string|mixed $value
     *
     * @throws \Exception
     */
    public function theJsonCollectionEveryNodeShouldBeEqualTo(string $collectionNode, string $node, $value): void
    {
        $jsonCollection = $this->getJsonCollection($collectionNode);
        $itemsCount = \count($jsonCollection);

        for ($itemNo = 0; $itemNo < $itemsCount; ++$itemNo) {
            $this->theJsonNodeShouldBeEqualTo(\sprintf('%s[%d].%s', $collectionNode, $itemNo, $node), $value);
        }
    }

    /**
     * @Then the JSON items in collection should have node :node that is equal to :value
     *
     * @param string       $node
     * @param string|mixed $value
     *
     * @throws \Exception
     */
    public function theJsonRootCollectionEveryNodeShouldBeEqualTo(string $node, $value): void
    {
        $this->theJsonCollectionEveryNodeShouldBeEqualTo('', $node, $value);
    }

    /**
     * @param string $collectionNode
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getJsonCollection(string $collectionNode): array
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, $collectionNode);

        if (!\is_array($actual)) {
            throw new RuntimeException('JSON is not a collection');
        }

        return $actual;
    }
}
