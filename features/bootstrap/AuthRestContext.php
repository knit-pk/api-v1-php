<?php

namespace App\Tests\Feature;

use App\Security\User\JWTUser;
use App\Security\User\UserInterface;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behatch\Context\RestContext;
use Behatch\HttpCall\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\KernelInterface;

final class AuthRestContext extends RestContext implements KernelAwareContext
{
    /**
     * @var JWTTokenManagerInterface
     */
    private $tokenManager;

    /**
     * @var array
     */
    private $userInfo;

    /**
     * @var UserInterface
     */
    private $user;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userInfo = [
            'id' => Uuid::uuid4()->toString(),
            'username' => 'user',
            'roles' => [UserInterface::ROLE_USER],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $container = $kernel->getContainer();
        /** @var JWTManager $manager */
        $manager = $container->get('lexik_jwt_authentication.jwt_manager');
        $this->tokenManager = $manager;
    }

    /**
     * @Given I am user :username
     *
     * @param string $username
     */
    public function iAmUser(string $username): void
    {
        $this->userInfo['username'] = $username;
    }

    /**
     * @Given I have role :role
     *
     * @param string $role
     */
    public function iHaveRole(string $role): void
    {
        $this->userInfo['roles'][] = \mb_strtoupper(\trim($role));
    }

    /**
     * @Given I have roles :roles
     *
     * @param string $roles
     */
    public function iHaveRoles(string $roles): void
    {
        foreach (\explode(',', $roles) as $role) {
            $this->iHaveRole($role);
        }
    }

    /**
     * @Given I am authenticated
     */
    public function iAmAuthenticated(): void
    {
        $this->user = JWTUser::createFromPayload($this->userInfo['username'], [
            'id' => $this->userInfo['id'],
            'roles' => \array_unique($this->userInfo['roles']),
        ]);

        $this->iAddHeaderEqualTo('Authorization', \sprintf(
            'Bearer %s',
            $this->tokenManager->create($this->user)
        ));
    }
}
