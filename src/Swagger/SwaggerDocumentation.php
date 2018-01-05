<?php

declare(strict_types=1);

namespace App\Swagger;

use ArrayObject;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SwaggerDocumentation implements NormalizerInterface
{
    /**
     * Unsecured api docs paths.
     */
    private const NOT_SECURED_PATHS = [
        '/token' => ['post'],
        '/token/refresh' => ['post'],
        '/articles' => ['get'],
        '/articles/{id}' => ['get'],
        '/comments' => ['get'],
        '/comments/{id}' => ['get'],
        '/comment_replies' => ['get'],
        '/comment_replies/{id}' => ['get'],
        '/articles/{id}/comments' => ['get'],
        '/articles/{id}/comments/{id}' => ['get'],
        '/articles/{id}/ratings' => ['get'],
        '/articles/{id}/ratings/{id}' => ['get'],
        '/projects' => ['get'],
        '/projects/{id}' => ['get'],
        '/teams' => ['get'],
        '/teams/{id}' => ['get'],
        '/ratings' => ['get'],
        '/ratings/{id}' => ['get'],
        '/tags' => ['get'],
        '/tags/{id}' => ['get'],
        '/categories' => ['get'],
        '/category/{id}' => ['get'],
        '/security_roles' => ['get'],
        '/security_roles/{id}' => ['get'],
        '/images' => ['get'],
        '/images/{id}' => ['get'],
    ];

    private const SECURITY_METHODS = [
        'header' => 'BearerAuthHeader',
        'query' => 'QueryParamToken',
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

    private function makeSecurity(): array
    {
        $security = [];

        foreach (array_values(self::SECURITY_METHODS) as $arrayValue) {
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

        $docs['securityDefinitions'] = $this->filterSecurityDefinitions($docs['securityDefinitions']);
        $docs['security'] = []; // not all routes need authorization

        $paths = $docs['paths'];
        if (!$paths instanceof ArrayObject) {
            throw new RuntimeException('[Swagger Documentation] Item `swagger.paths` expected to be represented by an ArrayObject.');
        }

        // Appends additional paths to documentation
        $this->appendAdditionalPaths($paths);

        // Add security parameter to paths
        $security = $this->makeSecurity();
        foreach ($paths as $path => $methods) {
            /** @var array $methods */
            /** @var ArrayObject $swagger */
            foreach ($methods as $method => $swagger) {
                if (
                    !array_key_exists($path, self::NOT_SECURED_PATHS) ||
                    !\in_array($method, self::NOT_SECURED_PATHS[$path], true)
                ) {
                    if (!$swagger instanceof ArrayObject) {
                        throw new RuntimeException(sprintf('[Swagger Documentation] Item `swagger.paths[%s][%s]` expected to be represented by an ArrayObject.', $path, $method));
                    }

                    $swagger->offsetSet('security', $security);
                }
            }
        }

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
     */
    private function appendAdditionalPaths(ArrayObject $paths)
    {
        $additionalPaths = [
            '/token' => [
                'post' => new ArrayObject([
                    'tags' => ['Token'],
                    'consumes' => 'application/json',
                    'produces' => 'application/json',
                    'summary' => 'Authenticate using credentials',
                    'description' => 'Authenticate using credentials',
                    'parameters' => [
                        [
                            'name' => 'credentials',
                            'in' => 'body',
                            'description' => 'Valid User credentials',
                            'schema' => [
                                'type' => 'object',
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
                    'responses' => [
                        '200' => [
                            'description' => 'Successfully generated token',
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'token' => [
                                        'type' => 'string',
                                        'description' => 'JWT Access Token',
                                    ],
                                    'refresh_token' => [
                                        'type' => 'string',
                                        'description' => 'Refresh Token',
                                    ],
                                ],
                            ],
                        ],
                        '401' => [
                            'description' => 'Invalid Refresh Token',
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'code' => [
                                        'type' => 'string',
                                        'description' => 'Error code',
                                    ],
                                    'message' => [
                                        'type' => 'string',
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
                    'tags' => ['Token'],
                    'consumes' => 'application/json',
                    'produces' => 'application/json',
                    'summary' => 'Authenticate using refresh token',
                    'description' => 'Authenticate using refresh token',
                    'parameters' => [
                        [
                            'name' => 'refresh_token',
                            'in' => 'body',
                            'description' => 'Valid refresh token',
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'refresh_token' => [
                                        'type' => 'string',
                                        'description' => 'Refresh Token',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Successfully generated token',
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'token' => [
                                        'type' => 'string',
                                        'description' => 'JWT Access Token',
                                    ],
                                    'refresh_token' => [
                                        'type' => 'string',
                                        'description' => 'Refresh Token',
                                    ],
                                ],
                            ],
                        ],
                        '401' => [
                            'description' => 'Invalid Refresh Token',
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'code' => [
                                        'type' => 'string',
                                        'description' => 'Error code',
                                    ],
                                    'message' => [
                                        'type' => 'string',
                                        'description' => 'Error message',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            '/images/upload' => [
                'post' => new ArrayObject([
                    'tags' => ['Image'],
                    'consumes' => 'application/x-www-form-urlencoded',
                    'produces' => 'application/json',
                    'summary' => 'Create an Image resource from file.',
                    'description' => 'Create an Image resource from uploaded file.',
                    'parameters' => [
                        [
                            'name' => 'image',
                            'in' => 'formData',
                            'type' => 'file',
                            'description' => 'Image file to create resource from.',
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Successfully generated token',
                            'schema' => [
                                '$ref' => '#/definitions/Image-ImageRead',
                            ],
                        ],
                        '400' => [
                            'description' => 'Invalid input',
                        ],
                    ],
                ]),
            ],
            '/articles/{id}/comments' => [
                'post' => new ArrayObject([
                    'tags' => ['Article', 'Comment'],
                    'consumes' => 'application/json',
                    'produces' => 'application/json',
                    'summary' => 'Adds a Comment to an Article.',
                    'description' => 'Adds a Comment to an Article resource as current user.',
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'type' => 'string',
                            'format' => 'uuid',
                            'required' => true,
                        ],
                        [
                            'name' => 'comment',
                            'in' => 'body',
                            'schema' => [
                                '$ref' => '#/definitions/Comment-CommentWriteLess',
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Successfully created resource',
                            'schema' => [
                                '$ref' => '#/definitions/Comment-CommentRead',
                            ],
                        ],
                        '400' => [
                            'description' => 'Invalid input',
                        ],
                    ],
                ]),
            ],
            '/articles/{id}/ratings' => [
                'post' => new ArrayObject([
                    'tags' => ['Article', 'Rating'],
                    'consumes' => 'application/json',
                    'produces' => 'application/json',
                    'summary' => 'Adds a Rating to an Article.',
                    'description' => 'Adds a Rating to an Article resource as current user.',
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'type' => 'string',
                            'format' => 'uuid',
                            'required' => true,
                        ],
                        [
                            'name' => 'comment',
                            'in' => 'body',
                            'schema' => [
                                '$ref' => '#/definitions/Rating-RatingWriteLess',
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Successfully created resource',
                            'schema' => [
                                '$ref' => '#/definitions/Rating-RatingRead',
                            ],
                        ],
                        '400' => [
                            'description' => 'Invalid input',
                        ],
                    ],
                ]),
            ],
            '/comments/{id}/comment_replies' => [
                'post' => new ArrayObject([
                    'tags' => ['Comment', 'CommentReply'],
                    'consumes' => 'application/json',
                    'produces' => 'application/json',
                    'summary' => 'Adds a Comment Reply to an Article.',
                    'description' => 'Adds a Comment Reply to an Article resource as current user.',
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'type' => 'string',
                            'format' => 'uuid',
                            'required' => true,
                        ],
                        [
                            'name' => 'reply',
                            'in' => 'body',
                            'schema' => [
                                '$ref' => '#/definitions/CommentReply-ReplyWriteLess',
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Successfully created resource',
                            'schema' => [
                                '$ref' => '#/definitions/CommentReply-ReplyRead',
                            ],
                        ],
                        '400' => [
                            'description' => 'Invalid input',
                        ],
                    ],
                ]),
            ],
        ];

        foreach ($additionalPaths as $additionalPath => $methods) {
            if ($paths->offsetExists($additionalPath)) {
                /** @var array $existingMethods */
                $existingMethods = $paths->offsetGet($additionalPath);
                foreach ($existingMethods as $existingMethod => $methodData) {
                    if (!isset($methods[$existingMethod])) {
                        $methods[$existingMethod] = $methodData;
                    }
                }
            }

            $paths->offsetSet($additionalPath, $methods);
        }
    }

    /**
     * Filters security definitions.
     *
     * @param ArrayObject[] $definitions
     *
     * @return ArrayObject[]
     */
    private function filterSecurityDefinitions(array $definitions): array
    {
        $filteredDefinitions = [];

        foreach ($definitions as $definition) {
            if ('header' === $definition['in']) {
                $definition['description'] .= '. Below write: Bearer MY_JWT_TOKEN';
            }

            $filteredDefinitions[self::SECURITY_METHODS[$definition['in']]] = $definition;
        }

        return $filteredDefinitions;
    }
}
