<?php

declare(strict_types=1);

namespace App\Tests\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Serializer\UuidContextBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;

class UuidContextBuilderTest extends TestCase
{
    /**
     * @var SerializerContextBuilderInterface|ObjectProphecy
     */
    private $decoratedProphecy;

    /**
     * @var UuidContextBuilder
     */
    private $uuidContextBuilder;

    protected function setUp(): void
    {
        $this->decoratedProphecy = $this->prophesize(SerializerContextBuilderInterface::class);

        /** @var SerializerContextBuilderInterface $decoratedMock */
        $decoratedMock = $this->decoratedProphecy->reveal();

        $this->uuidContextBuilder = new UuidContextBuilder($decoratedMock);
    }

    public function testNonDenormalizationPostRequest(): void
    {
        $request = new Request();
        $normalization = true;
        $extractedAttributes = null;
        $context = [];

        $this->decoratedProphecy->createFromRequest($request, $normalization, $extractedAttributes)->willReturn($context)->shouldBeCalled();

        $this->assertSame($context, $this->uuidContextBuilder->createFromRequest($request, $normalization, $extractedAttributes));
    }

    public function testDenormalizationOnPost(): void
    {
        $request = new Request();
        $request->setMethod('POST');
        $normalization = false;
        $extractedAttributes = null;

        $decoratedContext = [
            'resource_class' => 'TestClass',
        ];

        $this->decoratedProphecy->createFromRequest($request, $normalization, $extractedAttributes)->willReturn($decoratedContext)->shouldBeCalled();

        $context = $this->uuidContextBuilder->createFromRequest($request, $normalization, $extractedAttributes);
        $this->assertSame($decoratedContext['resource_class'], $context['resource_class']);
        $this->assertInstanceOf(UuidInterface::class, $context['default_constructor_arguments'][$decoratedContext['resource_class']]['id']);
    }
}
