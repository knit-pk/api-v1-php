<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Core\Api\OperationType;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\Exception\PropertyNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ParentReadListener
{
    private $itemDataProvider;

    public function __construct(ItemDataProviderInterface $itemDataProvider)
    {
        $this->itemDataProvider = $itemDataProvider;
    }

    /**
     * Calls the data provider and sets the data attribute.
     *
     * @param GetResponseEvent $event
     *
     * @throws NotFoundHttpException
     * @throws \ApiPlatform\Core\Exception\ResourceClassNotSupportedException
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();
        if (!($attributes = self::extractAttributes($request))) {
            return;
        }

        $request->attributes->set('parent', $this->getItemData($request, $attributes));
    }

    /**
     * Gets data for an item operation.
     *
     * @param Request $request
     * @param array   $attributes
     *
     * @throws NotFoundHttpException
     * @throws \ApiPlatform\Core\Exception\ResourceClassNotSupportedException
     *
     * @return object
     */
    private function getItemData(Request $request, array $attributes)
    {
        $id = $request->attributes->get('id');

        try {
            $data = $this->itemDataProvider->getItem($attributes['resource_class'], $id, $attributes['item_operation_name']);
        } catch (PropertyNotFoundException $e) {
            $data = null;
        }

        if (null === $data) {
            throw new NotFoundHttpException('Not Found');
        }

        return $data;
    }

    /**
     * Extracts resource class, operation name and format request attributes. Returns an empty array if the request does
     * not contain required attributes.
     *
     * @param Request $request
     *
     * @return array
     */
    public static function extractAttributes(Request $request): array
    {
        $result = $request->attributes->get('_api_parent_context');

        $attribute = OperationType::ITEM.'_operation_name';
        if (null === $result || !isset($result['resource_class'], $result[$attribute])) {
            return [];
        }

        return $result;
    }
}
