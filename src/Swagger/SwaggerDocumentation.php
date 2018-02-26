<?php

declare(strict_types=1);

namespace App\Swagger;

use ArrayObject;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Yaml\Yaml;

final class SwaggerDocumentation implements NormalizerInterface
{
    private const SWAGGER_RESOURCES = __DIR__.'/Resources/';

    private const SECURITY_METHODS = [
        'header' => 'BearerAuthHeader',
        'query' => 'QueryParamToken',
    ];

    /**
     * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface
     */
    private $decorated;

    /**
     * @var array
     */
    private $swagger;

    /**
     * SwaggerDecorator constructor.
     *
     * @param \Symfony\Component\Serializer\Normalizer\NormalizerInterface $decorated
     *
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
        $this->swagger = Yaml::parseFile(self::SWAGGER_RESOURCES.'swagger.yaml');
    }

    private function makeSecurity(array $securityMethods): array
    {
        $security = [];

        foreach (\array_values($securityMethods) as $arrayValue) {
            $security[] = new ArrayObject([$arrayValue => []]);
        }

        return $security;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        $openPaths = $this->swagger['security']['open_paths'];
        $securityMethods = $this->swagger['security']['methods'];
        $additionalPaths = $this->swagger['paths'];

        $docs['securityDefinitions'] = $this->filterSecurityDefinitions($docs['securityDefinitions'], $securityMethods);
        $docs['security'] = []; // not all routes need authorization

        $paths = $docs['paths'];
        if (!$paths instanceof ArrayObject) {
            throw new RuntimeException('[Swagger Documentation] Item `swagger.paths` expected to be represented by an ArrayObject.');
        }

        $this->appendAdditionalPaths($paths, $additionalPaths);
        $this->appendSecurity($paths, $openPaths, $this->makeSecurity($securityMethods));

        return $docs;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    /**
     * Add additional paths to swagger documentation.
     *
     * @param \ArrayObject $paths
     * @param array        $additionalPaths
     */
    private function appendAdditionalPaths(ArrayObject $paths, array $additionalPaths): void
    {
        /** @var ArrayObject[]|array[] $methods */
        foreach ($additionalPaths as $path => $methods) {
            if ($paths->offsetExists($path)) {
                /** @var ArrayObject[] $existingMethods */
                $existingMethods = $paths->offsetGet($path);
                foreach ($existingMethods as $existingMethodName => $existingMethod) {
                    if (!isset($methods[$existingMethodName])) {
                        $methods[$existingMethodName] = $existingMethod;
                    }
                }
            }

            foreach ($methods as $name => $method) {
                if (!$method instanceof ArrayObject) {
                    $methods[$name] = new ArrayObject($method);
                }
            }
            $paths->offsetSet($path, $methods);
        }
    }

    /**
     * Filters security definitions.
     *
     * @param ArrayObject[] $definitions
     * @param array         $securityMethods
     *
     * @return ArrayObject[]
     */
    private function filterSecurityDefinitions(array $definitions, array $securityMethods): array
    {
        $filteredDefinitions = [];

        foreach ($definitions as $definition) {
            if ('header' === $definition['in']) {
                $definition['description'] .= '. Below write: Bearer MY_JWT_TOKEN';
            }

            $filteredDefinitions[$securityMethods[$definition['in']]] = $definition;
        }

        return $filteredDefinitions;
    }

    /**
     * @param \ArrayObject $paths
     * @param array        $openPaths
     * @param array        $security
     *
     * @throws \RuntimeException
     */
    private function appendSecurity(ArrayObject $paths, array $openPaths, array $security): void
    {
        /** @var ArrayObject[] $methods */
        foreach ($paths as $path => $methods) {
            foreach ($methods as $methodName => $method) {
                if (
                    !\array_key_exists($path, $openPaths) ||
                    !\in_array($methodName, $openPaths[$path], true)
                ) {
                    if (!$method instanceof ArrayObject) {
                        throw new RuntimeException(\sprintf('[Swagger Documentation] Item `swagger.paths[%s][%s]` expected to be represented by an ArrayObject.', $path, $methodName));
                    }

                    $method->offsetSet('security', $security);
                }
            }
        }
    }
}
