<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * This class exists to have an resource 'Token' in api platform documentation,
 * to be able to expose corresponding routes
 *
 * @ApiResource(collectionOperations={},
 * itemOperations={
 *     "token"={
 *          "route_name"="api_login_check",
 *          "method"="POST",
 *          "swagger_context"={
 *              "summary"="Authenticate using credentials",
 *              "description"="Authenticate using credentials",
 *              "parameters"={
 *                  {
 *                      "name"="credentials",
 *                      "in"="body",
 *                      "description"="User authentication credentials",
 *                      "schema"={
 *                          "type"="object",
 *                          "properties"={
 *                              "username"={
 *                                  "type"="string",
 *                              },
 *                              "password"={
 *                                  "type"="string",
 *                              },
 *                          },
 *                      },
 *                  },
 *              },
 *              "responses"={
 *                  "200"={
 *                      "description"="Successfully generated token",
 *                      "schema"={
 *                          "type"="object",
 *                          "properties"={
 *                              "token"={
 *                                  "type"="string",
 *                                  "description"="JWT Access Token",
 *                              },
 *                              "refresh_token"={
 *                                  "type"="string",
 *                                  "description"="Refresh Token",
 *                              },
 *                          },
 *                      },
 *                  },
 *                  "401"={
 *                      "description"="Invalid credentials",
 *                      "schema"={
 *                          "type"="object",
 *                          "properties"={
 *                              "code"={
 *                                  "type"="string",
 *                                  "description"="Error code",
 *                              },
 *                              "message"={
 *                                  "type"="string",
 *                                  "description"="Error message",
 *                              },
 *                          },
 *                      },
 *                  },
 *              },
 *          },
 *     },
 *     "refresh_token"={
 *          "route_name"="gesdinet_jwt_refresh_token",
 *          "method"="POST",
 *          "swagger_context"={
 *              "summary"="Authenticate using refresh token",
 *              "description"="Authenticate using credentials",
 *              "parameters"={
 *                  {
 *                      "name"="refresh_token",
 *                      "in"="body",
 *                      "description"="Valid refresh token",
 *                      "schema"={
 *                          "type"="object",
 *                          "properties"={
 *                              "refresh_token"={
 *                                  "type"="string",
 *                                  "description"="Refresh Token",
 *                              },
 *                          },
 *                      },
 *                  },
 *              },
 *              "responses"={
 *                  "200"={
 *                      "description"="Successfully generated token",
 *                      "schema"={
 *                          "type"="object",
 *                          "properties"={
 *                              "token"={
 *                                  "type"="string",
 *                                  "description"="JWT Access Token",
 *                              },
 *                              "refresh_token"={
 *                                  "type"="string",
 *                                  "description"="Refresh Token",
 *                              },
 *                          },
 *                      },
 *                  },
 *                  "401"={
 *                      "description"="Invalid Refresh Token",
 *                      "schema"={
 *                          "type"="object",
 *                          "properties"={
 *                              "code"={
 *                                  "type"="string",
 *                                  "description"="Error code",
 *                              },
 *                              "message"={
 *                                  "type"="string",
 *                                  "description"="Error message",
 *                              },
 *                          },
 *                      },
 *                  },
 *              },
 *          },
 *     },
 * })
 */
class Token
{

}