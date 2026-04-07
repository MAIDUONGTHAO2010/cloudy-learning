<?php

namespace App\Services;

use App\Repositories\UserRepositoryEloquent;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepositoryEloquent $userRepository)
    {
        $this->userRepository = $userRepository;
    }

}
