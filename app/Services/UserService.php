<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(protected UserRepositoryInterface $userRepository) {}

    public function list(array $filters): mixed
    {
        return $this->userRepository->getPaginated($filters);
    }

    public function stats(): array
    {
        return $this->userRepository->getStats();
    }

    public function listInstructors(): mixed
    {
        return $this->userRepository->getInstructors();
    }

    public function listPublicInstructors(): mixed
    {
        return $this->userRepository->getPublicInstructors();
    }

    public function toggleActive(int $id): mixed
    {
        $user = $this->userRepository->toggleActive($id);

        if (! $user->is_active) {
            DB::table('sessions')->where('user_id', $id)->delete();
        }

        return $user;
    }
}
