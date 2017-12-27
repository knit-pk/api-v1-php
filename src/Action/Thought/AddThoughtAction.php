<?php

declare(strict_types=1);

namespace App\Action\Thought;

use App\Entity\User;
use App\Security\User\UserInterface;
use App\Thought\Exception\NotSupportedThoughtException;
use App\Thought\ThoughtfulInterface;
use App\Thought\ThoughtInterface;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

final class AddThoughtAction
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param ThoughtInterface $data
     * @param ThoughtfulInterface $parent
     * @param SymfonyUserInterface $user
     *
     * @return ThoughtInterface
     *
     * @throws \RuntimeException
     * @throws \App\Thought\Exception\NotSupportedThoughtException
     * @throws \Doctrine\ORM\ORMException
     */
    public function __invoke(ThoughtInterface $data, ThoughtfulInterface $parent, SymfonyUserInterface $user)
    {
        if (!$parent->isThoughtSupported($data)) {
            throw new NotSupportedThoughtException($parent, $data);
        }

        $data->setAuthor($this->getUserEntity($user));
        $data->setSubject($parent);

        return $data;
    }

    /**
     * @param SymfonyUserInterface $user
     *
     * @return User
     *
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    private function getUserEntity(SymfonyUserInterface $user): User
    {
        if ($user instanceof User) {
            return $user;
        }

        if (!$user instanceof UserInterface) {
            throw new RuntimeException('User object must implement local UserInterface.');
        }

        $entity = $this->em->getReference(User::class, $user->getId());

        // Can never happen
        if (!$entity instanceof User) {
            throw new RuntimeException('Authenticated user does not exists in database.', 500);
        }

        return $entity;
    }

}