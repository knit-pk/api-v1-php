<?php

declare(strict_types=1);

namespace App\Action\Thought;

use App\Security\UserProvider\UserEntityProvider;
use App\Thought\Exception\NotSupportedThoughtException;
use App\Thought\ThoughtfulInterface;
use App\Thought\ThoughtInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class AddThoughtAction
{

    private $userEntityProvider;

    public function __construct(UserEntityProvider $userEntityProvider)
    {
        $this->userEntityProvider = $userEntityProvider;
    }

    /**
     * @param ThoughtInterface $data
     * @param ThoughtfulInterface $parent
     * @param UserInterface $user
     *
     * @return ThoughtInterface
     *
     * @throws \App\Security\Exception\SecurityException
     * @throws \App\Thought\Exception\NotSupportedThoughtException
     */
    public function __invoke(ThoughtInterface $data, ThoughtfulInterface $parent, UserInterface $user)
    {
        if (!$parent->isThoughtSupported($data)) {
            throw new NotSupportedThoughtException($parent, $data);
        }

        $data->setAuthor($this->userEntityProvider->getUser($user));
        $data->setSubject($parent);

        return $data;
    }

}