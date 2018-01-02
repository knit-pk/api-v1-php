<?php

declare(strict_types=1);

namespace App\Tests\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Serializer\AdminContextBuilder;
use App\Serializer\Group\Factory\AdminSerializerGroupFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminContextBuilderTest extends TestCase
{
    /**
     * @var SerializerContextBuilderInterface|ObjectProphecy
     */
    private $serializerContextBuilderProphecy;

    /**
     * @var AuthorizationCheckerInterface|ObjectProphecy
     */
    private $authorizationCheckerProphecy;

    /**
     * @var AdminSerializerGroupFactory|ObjectProphecy
     */
    private $adminSerializerGroupFactoryProphecy;

    /**
     * @var AdminContextBuilder
     */
    private $adminContextBuilder;

    protected function setUp()
    {
        $this->serializerContextBuilderProphecy = $this->prophesize(SerializerContextBuilderInterface::class);
        $this->authorizationCheckerProphecy = $this->prophesize(AuthorizationCheckerInterface::class);
        $this->adminSerializerGroupFactoryProphecy = $this->prophesize(AdminSerializerGroupFactory::class);

        $this->adminContextBuilder = new AdminContextBuilder($this->serializerContextBuilderProphecy->reveal(), $this->authorizationCheckerProphecy->reveal(), $this->adminSerializerGroupFactoryProphecy->reveal());
    }

    public function testNonSerializerGroupsSet()
    {
        $request = new Request();
        $normalization = true;
        $extractedAttributes = null;

        $context = [
            'resource_class' => 'Test',
        ];

        $this->serializerContextBuilderProphecy->createFromRequest($request, $normalization, $extractedAttributes)->willReturn($context)->shouldBeCalled();
        $this->authorizationCheckerProphecy->isGranted('ROLE_ADMIN')->shouldNotBeCalled();
        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($context['resource_class'], 'read')->shouldNotBeCalled();

        $this->assertSame($context, $this->adminContextBuilder->createFromRequest($request, $normalization, $extractedAttributes));
    }

    public function testNonAdminGranted()
    {
        $request = new Request();

        $normalization = true;
        $extractedAttributes = null;

        $context = [
            'groups' => ['TestRead'],
            'resource_class' => 'Test',
        ];

        $this->serializerContextBuilderProphecy->createFromRequest($request, $normalization, $extractedAttributes)->willReturn($context)->shouldBeCalled();
        $this->authorizationCheckerProphecy->isGranted('ROLE_ADMIN')->willReturn(false)->shouldBeCalled();
        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($context['resource_class'], 'read')->shouldNotBeCalled();

        $this->assertSame($context, $this->adminContextBuilder->createFromRequest($request, $normalization, $extractedAttributes));
    }

    public function testNormalization()
    {
        $request = new Request();

        $normalization = true;
        $extractedAttributes = null;

        $context = [
            'groups' => ['TestRead'],
            'resource_class' => 'Test',
        ];

        $this->serializerContextBuilderProphecy->createFromRequest($request, $normalization, $extractedAttributes)->willReturn($context)->shouldBeCalled();
        $this->authorizationCheckerProphecy->isGranted('ROLE_ADMIN')->willReturn(true)->shouldBeCalled();

        $group = 'TestAdminRead';
        $context['groups'][] = $group;

        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($context['resource_class'], 'read')->willReturn($group)->shouldBeCalled();
        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($context['resource_class'], 'write')->shouldNotBeCalled();

        $this->assertSame($context, $this->adminContextBuilder->createFromRequest($request, $normalization, $extractedAttributes));
    }

    public function testDenormalizationOnPut()
    {
        $request = new Request();
        $request->setMethod('PUT');

        $normalization = false;
        $extractedAttributes = null;

        $context = [
            'groups' => ['TestWrite'],
            'resource_class' => 'Test',
        ];

        $this->serializerContextBuilderProphecy->createFromRequest($request, $normalization, $extractedAttributes)->willReturn($context)->shouldBeCalled();
        $this->authorizationCheckerProphecy->isGranted('ROLE_ADMIN')->willReturn(true)->shouldBeCalled();

        $group = 'TestAdminUpdate';
        $context['groups'][] = $group;

        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($context['resource_class'], 'update')->willReturn($group)->shouldBeCalled();

        $group = 'TestAdminWrite';
        $context['groups'][] = $group;

        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($context['resource_class'], 'write')->willReturn($group)->shouldBeCalled();
        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($context['resource_class'], 'create')->shouldNotBeCalled();

        $this->assertSame($context, $this->adminContextBuilder->createFromRequest($request, $normalization, $extractedAttributes));
    }

    public function testDenormalizationOnPost()
    {
        $request = new Request();
        $request->setMethod('POST');

        $normalization = false;
        $extractedAttributes = null;

        $context = [
            'groups' => ['TestWrite'],
            'resource_class' => 'Test',
        ];

        $this->serializerContextBuilderProphecy->createFromRequest($request, $normalization, $extractedAttributes)->willReturn($context)->shouldBeCalled();
        $this->authorizationCheckerProphecy->isGranted('ROLE_ADMIN')->willReturn(true)->shouldBeCalled();

        $group = 'TestAdminCreate';
        $context['groups'][] = $group;

        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($context['resource_class'], 'create')->willReturn($group)->shouldBeCalled();

        $group = 'TestAdminWrite';
        $context['groups'][] = $group;

        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($context['resource_class'], 'write')->willReturn($group)->shouldBeCalled();
        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($context['resource_class'], 'update')->shouldNotBeCalled();

        $this->assertSame($context, $this->adminContextBuilder->createFromRequest($request, $normalization, $extractedAttributes));
    }

    public function notValidHttpMethodsForDenormalizationProvider(): array
    {
        return [
            ['GET'],
            ['DELETE'],
            ['HEAD'],
            ['OPTIONS'],
        ];
    }

    /**
     * @dataProvider             notValidHttpMethodsForDenormalizationProvider
     *
     * @expectedException        \DomainException
     * @expectedExceptionMessage Unsupported HTTP method for Admin context denormalization
     *
     * @param string $method
     */
    public function testNotSupportedHttpMethodForDenormalization(string $method)
    {
        $request = new Request();
        $request->setMethod($method);

        $normalization = false;
        $extractedAttributes = null;

        $context = [
            'groups' => ['TestWrite'],
            'resource_class' => 'Test',
        ];

        $this->serializerContextBuilderProphecy->createFromRequest($request, $normalization, $extractedAttributes)->willReturn($context)->shouldBeCalled();
        $this->authorizationCheckerProphecy->isGranted('ROLE_ADMIN')->willReturn(true)->shouldBeCalled();

        $this->adminContextBuilder->createFromRequest($request, $normalization, $extractedAttributes);
    }
}
