<?php
declare(strict_types=1);

namespace App\Swagger;

use ArrayObject;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SwaggerDocumentation implements NormalizerInterface
{
    /**
     * Unsecured api docs paths
     */
    private const NOT_SECURED_PATHS = [
        '/token'               => ['post'],
        '/token/refresh'       => ['post'],
        '/articles'            => ['get'],
        '/articles/{id}'       => ['get'],
        '/projects'            => ['get'],
        '/projects/{id}'       => ['get'],
        '/tags'                => ['get'],
        '/tags/{id}'           => ['get'],
        '/categories'          => ['get'],
        '/category/{id}'       => ['get'],
        '/security_roles'      => ['get'],
        '/security_roles/{id}' => ['get'],
    ];

    /**
     * Authorization parameter to secured routes
     * (Shown in swagger documentation)
     */
    private const AUTHORIZATION_PARAMETER = [
        'name'        => 'Authorization',
        'description' => 'JWT Access Token',
        'in'          => 'header',
        'default'     => 'Bearer <%JWTAccessToken%>',
        'required'    => true,
        'type'        => 'string',
    ];

    /**
     * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface
     */
    private $decorated;


    /**
     * SwaggerDecorator constructor.
     *
     * @param \Symfony\Component\Serializer\Normalizer\NormalizerInterface $decorated
     */
    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
    }


    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        $paths = $docs['paths'];
        if (!$paths instanceof ArrayObject) {
            throw new RuntimeException('[Swagger Documentation] Item `swagger.paths` expected to be represented by an ArrayObject.');
        }

        // Appends additional paths to documentation
        $this->appendAdditionalPaths($paths);

        // Prepend authorization parameter to paths
        foreach ($paths as $path => $methods) {
            /** @var array $methods */
            /** @var ArrayObject $swagger */
            foreach ($methods as $method => $swagger) {
                if (
                    !array_key_exists($path, self::NOT_SECURED_PATHS) ||
                    !in_array($method, self::NOT_SECURED_PATHS[$path], true)
                ) {
                    if (!$swagger instanceof ArrayObject) {
                        throw new RuntimeException(sprintf('[Swagger Documentation] Item `swagger.paths[%s][%s]` expected to be represented by an ArrayObject.', $path, $method));
                    }
                    $this->addAuthorizationParameter($swagger);
                }
            }
        }

        return $docs;
    }


    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }


    /**
     * Add additional paths to swagger documentation
     *
     * @param \ArrayObject $paths
     */
    private function appendAdditionalPaths(ArrayObject $paths)
    {
        $additionalPaths = [
            '/token'         => [
                'post' => new ArrayObject([
                    'tags'        => ['Token'],
                    'consumes'    => 'application/json',
                    'produces'    => 'application/json',
                    'summary'     => 'Authenticate using credentials',
                    'description' => 'Authenticate using credentials',
                    'parameters'  => [
                        [
                            'name'        => 'credentials',
                            'in'          => 'body',
                            'description' => 'Valid User credentials',
                            'schema'      => [
                                'type'       => 'object',
                                'properties' => [
                                    'username' => [
                                        'type' => 'string',
                                    ],
                                    'password' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses'   => [
                        '200' => [
                            'description' => 'Successfully generated token',
                            'schema'      => [
                                'type'       => 'object',
                                'properties' => [
                                    'token'         => [
                                        'type'        => 'string',
                                        'description' => 'JWT Access Token',
                                    ],
                                    'refresh_token' => [
                                        'type'        => 'string',
                                        'description' => 'Refresh Token',
                                    ],
                                ],
                            ],
                        ],
                        '401' => [
                            'description' => 'Invalid Refresh Token',
                            'schema'      => [
                                'type'       => 'object',
                                'properties' => [
                                    'code'    => [
                                        'type'        => 'string',
                                        'description' => 'Error code',
                                    ],
                                    'message' => [
                                        'type'        => 'string',
                                        'description' => 'Error message',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            '/token/refresh' => [
                'post' => new ArrayObject([
                    'tags'        => ['Token'],
                    'consumes'    => 'application/json',
                    'produces'    => 'application/json',
                    'summary'     => 'Authenticate using refresh token',
                    'description' => 'Authenticate using refresh token',
                    'parameters'  => [
                        [
                            'name'        => 'refresh_token',
                            'in'          => 'body',
                            'description' => 'Valid refresh token',
                            'schema'      => [
                                'type'       => 'object',
                                'properties' => [
                                    'refresh_token' => [
                                        'type'        => 'string',
                                        'description' => 'Refresh Token',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses'   => [
                        '200' => [
                            'description' => 'Successfully generated token',
                            'schema'      => [
                                'type'       => 'object',
                                'properties' => [
                                    'token'         => [
                                        'type'        => 'string',
                                        'description' => 'JWT Access Token',
                                    ],
                                    'refresh_token' => [
                                        'type'        => 'string',
                                        'description' => 'Refresh Token',
                                    ],
                                ],
                            ],
                        ],
                        '401' => [
                            'description' => 'Invalid Refresh Token',
                            'schema'      => [
                                'type'       => 'object',
                                'properties' => [
                                    'code'    => [
                                        'type'        => 'string',
                                        'description' => 'Error code',
                                    ],
                                    'message' => [
                                        'type'        => 'string',
                                        'description' => 'Error message',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
        ];

        foreach ($additionalPaths as $additionalPath => $methods) {
            $paths->offsetSet($additionalPath, $methods);
        }
    }


    /**
     * Adds an authorization parameter to swagger schema
     *
     * @param ArrayObject $swagger  Object representing swagger.path[$path].method[$method]
     *                              in swagger schema
     */
    private function addAuthorizationParameter(ArrayObject $swagger)
    {
        // Prepend parameters
        if ($swagger->offsetExists('parameters')) {
            array_unshift($swagger['parameters'], self::AUTHORIZATION_PARAMETER);
        } else {
            // ..or create with first parameter
            $swagger['parameters'][] = self::AUTHORIZATION_PARAMETER;
        }
    }
}