<?php

declare(strict_types=1);

namespace App\Security\UserProvider;

use App\Entity\User;
use App\Security\Exception\SecurityException;
use App\Security\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class UserEntityProvider
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @throws \App\Security\Exception\SecurityException
     *
     * @return \App\Entity\User
     */
    public function getReference(SymfonyUserInterface $user): User
    {
        if ($user instanceof User) {
            return $user;
        }

        if (!$user instanceof UserInterface) {
            throw new SecurityException(\sprintf('Provided user instance must implement %s', UserInterface::class), 500);
        }

        try {
            /** @var User $entity */
            $entity = $this->em->getReference(User::class, $user->getId());
        } catch (ORMException $e) {
            throw new SecurityException('Provided user does not exists in database', 500);
        }

        return $entity;
    }
}
