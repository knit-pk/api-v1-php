<?php

declare(strict_types=1);

namespace App\Thought;

use App\Security\User\UserInterface;

interface ThoughtInterface
{
    /**
     * Sets the subject of a thought.
     *
     * @param ThoughtfulInterface $subject
     */
    public function setSubject(ThoughtfulInterface $subject): void;

    /**
     * Sets the author of a thought.
     *
     * @param UserInterface $author
     */
    public function setAuthor(UserInterface $author): void;

    /**
     * Expresses thought provided by its author in readable form.
     *
     * @return string
     */
    public function toString(): string;
}
