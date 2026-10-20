<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Exception;

class UserService
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function deleteUser(User $user, User $currentUser): void
    {
        if( $currentUser->getId() !== $user->getId() ) {
            throw new Exception("Cannot delete a profile who is not your's.");
        }
        $this->userRepository->deleteUser($user);
        return;

    }

}
